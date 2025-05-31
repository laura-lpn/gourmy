export class ReviewSection extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
    this.page = 1;
    this.limit = 5;
    this.totalReviews = 0;
    this.restaurantId = this.getAttribute('restaurant-id');
    this.user = {
      id: this.getAttribute('user-id'),
      username: this.getAttribute('user-username'),
    }
    this.isOwner = this.getAttribute('is-owner');
  }

  connectedCallback() {
    this.render();
    this.fetchReviews();

    if (this.user) {
      this.shadowRoot.querySelector('#review-form')?.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    this.shadowRoot.querySelector('#prev-page').addEventListener('click', () => {
      if (this.page > 1) {
        this.page--;
        this.fetchReviews();
      }
    });

    this.shadowRoot.querySelector('#next-page').addEventListener('click', () => {
      if (this.page * this.limit < this.totalReviews) {
        this.page++;
        this.fetchReviews();
      }
    });
  }

  render() {
    this.shadowRoot.innerHTML = `
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
      <style>
        .border { border: 1px solid #ccc; padding: 10px; margin: 10px 0; border-radius: 8px; }
        .response {
          margin-left: 1.5rem;
          background-color: #f1f5f9;
          padding: 10px;
          border-left: 4px solid #0d9488;
          border-radius: 6px;
          margin-top: 10px;
        }
        .pagination { margin-top: 10px; }
        .btn-icon { background: none; border: none; cursor: pointer; margin-left: 5px; }

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

        .modal.active {
          display: flex;
        }

        .modal-content {
          background: white;
          padding: 20px;
          border-radius: 10px;
          max-width: 90%;
          text-align: center;
        }

        .modal button {
          margin: 10px;
          padding: 8px 16px;
          border: none;
          border-radius: 6px;
          cursor: pointer;
        }

        .confirm-btn { background-color: #dc2626; color: white; }
        .cancel-btn { background-color: #e5e7eb; color: #111827; }
      </style>

      <div>
        <h2>Commentaires</h2>
        ${this.isOwner === '1' ? `
          <p>Vous êtes le propriétaire de ce restaurant. Pour répondre aux avis allez à la section <a href="/restaurateur/mon-restaurant#commantaires">Gestion des avis</a>.</p>`
        : this.user.id ? `
          <form id="review-form" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Titre" required><br>
            <input type="number" name="rating" min="1" max="5" value="5"><br>
            <textarea name="comment" placeholder="Commentaire" required></textarea><br>
            <input type="file" name="imageFile"><br>
            <button type="submit">Envoyer</button>
          </form>` : `
          <p><a href="/connexion">Connectez-vous pour laisser un commentaire</a></p>`
        }

        <div id="reviews-container"></div>

        <div class="pagination">
          <button id="prev-page">Précédent</button>
          <span id="pagination-info"></span>
          <button id="next-page">Suivant</button>
        </div>
      </div>

      <div class="modal" id="confirm-modal">
        <div class="modal-content">
          <p id="confirm-message">Es-tu sûr de vouloir supprimer cet avis ?</p>
          <button id="confirm-delete" class="confirm-btn">Supprimer</button>
          <button id="cancel-delete" class="cancel-btn">Annuler</button>
        </div>
      </div>
    `;
  }

  fetchReviews() {
    fetch(`/api/restaurants/${this.restaurantId}/reviews?page=${this.page}&limit=${this.limit}`)
      .then(res => res.json())
      .then(data => {
        const container = this.shadowRoot.querySelector('#reviews-container');
        container.innerHTML = '';

        data.data.forEach(r => {
          const div = document.createElement('div');
          div.classList.add('border');
          div.setAttribute('data-id', r.id);

          div.innerHTML = `
            <h3>${r.title}</h3>
            <p>${r.comment}</p>
            <p><strong>Note:</strong> ${r.rating}/5</p>
            <p>${r.author.username}</p>
            ${(this.user.id && parseInt(this.user.id) === r.author.id) ? `
              <button class="btn-icon edit-btn" data-id="${r.id}" aria-label="Éditer"><i class="fa-solid fa-pen"></i></button>
              <button class="btn-icon delete-btn" data-id="${r.id}" aria-label="Supprimer"><i class="fa-solid fa-trash"></i></button>
            ` : ''}
          `;

          // Ajoute la réponse s’il y en a une
          if (r.response) {
            const responseDiv = document.createElement('div');
            responseDiv.classList.add('response');
            responseDiv.innerHTML = `
              <p><strong>Réponse du restaurateur :</strong> ${r.response.comment}</p>
              <p>${r.response.author}</p>
            `;
            div.appendChild(responseDiv);
          }

          container.appendChild(div);
        });

        this.totalReviews = data.total;
        this.shadowRoot.querySelector('#pagination-info').innerText = `Page ${this.page} sur ${Math.ceil(this.totalReviews / this.limit)}`;

        this.shadowRoot.querySelectorAll('.delete-btn').forEach(btn => {
          btn.addEventListener('click', () => {
            this.showModalConfirm("Supprimer l'avis ?", () => {
              this.deleteReview(btn.dataset.id);
            });
          });
        });

        this.shadowRoot.querySelectorAll('.edit-btn').forEach(btn => {
          btn.addEventListener('click', () => this.enableEdit(btn.dataset.id));
        });
      });
  }

  deleteReview(id) {
    fetch(`/api/reviews/${id}`, { method: 'DELETE' })
      .then(res => {
        if (res.ok) this.fetchReviews();
      });
  }

  handleSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('restaurant', this.restaurantId);

    fetch('/api/reviews', {
      method: 'POST',
      body: formData
    }).then(res => res.json())
      .then(() => {
        form.reset();
        this.fetchReviews();
      });
  }

  enableEdit(id) {
    const div = this.shadowRoot.querySelector(`[data-id="${id}"]`);
    const title = div.querySelector('h3').textContent;
    const comment = div.querySelector('p:nth-of-type(1)').textContent;
    const rating = div.querySelector('p:nth-of-type(2)').textContent.match(/\d+/)[0];

    const responseHTML = div.querySelector('.response')?.outerHTML || '';

    div.innerHTML = `
      <form class="edit-review-form">
        <input type="text" name="title" value="${title}" required><br>
        <input type="number" name="rating" min="1" max="5" value="${rating}" required><br>
        <textarea name="comment" required>${comment}</textarea><br>
        <button type="submit">Mettre à jour</button>
        <button type="button" class="cancel-edit">Annuler</button>
      </form>
      ${responseHTML}
    `;

    div.querySelector('.edit-review-form').addEventListener('submit', (e) => this.submitEdit(e, id));
    div.querySelector('.cancel-edit').addEventListener('click', () => this.fetchReviews());
  }

  submitEdit(e, id) {
    e.preventDefault();
    const form = e.target;
    const payload = {
      title: form.title.value,
      comment: form.comment.value,
      rating: form.rating.value
    };

    fetch(`/api/reviews/${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    }).then(res => res.json())
      .then(() => this.fetchReviews());
  }

  showModalConfirm(message, onConfirm) {
    const modal = this.shadowRoot.querySelector('#confirm-modal');
    this.shadowRoot.querySelector('#confirm-message').textContent = message;

    const confirmBtn = this.shadowRoot.querySelector('#confirm-delete');
    const cancelBtn = this.shadowRoot.querySelector('#cancel-delete');

    modal.classList.add('active');

    const confirmHandler = () => {
      onConfirm();
      close();
    };

    const close = () => {
      modal.classList.remove('active');
      confirmBtn.removeEventListener('click', confirmHandler);
      cancelBtn.removeEventListener('click', close);
    };

    confirmBtn.addEventListener('click', confirmHandler);
    cancelBtn.addEventListener('click', close);
  }
}

customElements.define('review-section', ReviewSection);