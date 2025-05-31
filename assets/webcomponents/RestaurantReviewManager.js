export class RestaurantReviewManager extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
    this.restaurantId = this.getAttribute('restaurant-id');
    this.csrf = this.getAttribute('csrf');
    this.editingResponseId = null;
  }

  connectedCallback() {
    this.fetchAndRender();
  }

  async fetchAndRender() {
    const res = await fetch(`/api/restaurants/${this.restaurantId}/reviews`);
    const data = await res.json();
    this.reviews = data.data;
    this.render();
  }

  render() {
    this.shadowRoot.innerHTML = `
      <style>
        .review { border: 1px solid #ccc; padding: 10px; margin: 10px 0; border-radius: 6px; }
        .response { margin-left: 1rem; background: #f9f9f9; padding: 8px; border-left: 3px solid #0d9488; border-radius: 4px; }
        .response button { margin-right: 5px; }
        textarea { width: 100%; min-height: 60px; margin-top: 5px; margin-bottom: 5px; }
        button { padding: 6px 12px; background: #0d9488; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .modal {
          display: none;
          position: fixed;
          z-index: 1000;
          top: 0; left: 0;
          width: 100%; height: 100%;
          background-color: rgba(0, 0, 0, 0.5);
          justify-content: center;
          align-items: center;
        }
        .modal.active { display: flex; }
        .modal-content {
          background: white;
          padding: 20px;
          border-radius: 10px;
          max-width: 90%;
          text-align: center;
        }
      </style>
      <div id="reviews-container"></div>
      <div class="modal" id="delete-modal">
        <div class="modal-content">
          <p>Supprimer cette réponse ?</p>
          <button id="confirm-delete">Confirmer</button>
          <button id="cancel-delete">Annuler</button>
        </div>
      </div>
    `;

    const container = this.shadowRoot.querySelector('#reviews-container');

    this.reviews.forEach(review => {
      const div = document.createElement('div');
      div.classList.add('review');

      div.innerHTML = `
        <p><strong>${review.author.username}</strong> - ${review.rating}/5</p>
        ${review.title ? `<p><strong>${review.title}</strong></p>` : ''}
        <p>${review.comment}</p>
        ${review.image ? `<img src="${review.image}" style="width: 100px; margin-top: 5px;">` : ''}
        <div class="response-container" data-review-id="${review.id}">
          ${review.response ? `
            <div class="response" data-id="${review.response.id}">
              <div class="view-mode">
                <p><em>Réponse :</em> ${review.response.comment}</p>
                <small>par ${review.response.author}</small><br>
                <button class="edit-response" data-id="${review.response.id}" data-comment="${review.response.comment}">Modifier</button>
                <button class="delete-response" data-id="${review.response.id}">Supprimer</button>
              </div>
            </div>
          ` : `
            <form class="response-form" data-review-id="${review.id}">
              <textarea name="comment" placeholder="Votre réponse..." required></textarea>
              <button type="submit">Répondre</button>
            </form>
          `}
        </div>
      `;

      container.appendChild(div);
    });

    this.addListeners();
  }

  addListeners() {
    this.shadowRoot.querySelectorAll('.response-form').forEach(form => {
      form.addEventListener('submit', e => this.handleSubmitResponse(e));
    });

    this.shadowRoot.querySelectorAll('.edit-response').forEach(btn => {
      btn.addEventListener('click', () => this.showEditForm(btn.dataset.id, btn.dataset.comment));
    });

    this.shadowRoot.querySelectorAll('.delete-response').forEach(btn => {
      btn.addEventListener('click', () => this.confirmDelete(btn.dataset.id));
    });
  }

  async handleSubmitResponse(e) {
    e.preventDefault();
    const form = e.target;
    const comment = form.comment.value;
    const reviewId = form.dataset.reviewId;

    const res = await fetch(`/api/reviews/${reviewId}/response`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ comment })
    });

    if (res.ok) {
      this.fetchAndRender();
    }
  }

  showEditForm(responseId, currentComment) {
    const responseDiv = this.shadowRoot.querySelector(`.response[data-id="${responseId}"]`);
    responseDiv.innerHTML = `
      <form class="edit-response-form" data-id="${responseId}">
        <textarea name="comment" required>${currentComment}</textarea>
        <button type="submit">Mettre à jour</button>
        <button type="button" class="cancel-edit">Annuler</button>
      </form>
    `;

    responseDiv.querySelector('.edit-response-form').addEventListener('submit', e => this.handleEditSubmit(e, responseId));
    responseDiv.querySelector('.cancel-edit').addEventListener('click', () => this.fetchAndRender());
  }

  async handleEditSubmit(e, responseId) {
    e.preventDefault();
    const comment = e.target.comment.value;

    const res = await fetch(`/api/reviews/${responseId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ comment })
    });

    if (res.ok) {
      this.fetchAndRender();
    }
  }

  confirmDelete(responseId) {
    this.editingResponseId = responseId;
    const modal = this.shadowRoot.querySelector('#delete-modal');
    modal.classList.add('active');

    this.shadowRoot.querySelector('#confirm-delete').onclick = () => this.handleDelete();
    this.shadowRoot.querySelector('#cancel-delete').onclick = () => modal.classList.remove('active');
  }

  async handleDelete() {
    const responseId = this.editingResponseId;
    const modal = this.shadowRoot.querySelector('#delete-modal');

    const res = await fetch(`/api/reviews/${responseId}`, {
      method: 'DELETE'
    });

    modal.classList.remove('active');
    this.editingResponseId = null;

    if (res.ok) {
      this.fetchAndRender();
    }
  }
}

customElements.define('restaurant-review-manager', RestaurantReviewManager);