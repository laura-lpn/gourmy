import { renderReview } from './ReviewDisplay.js';
import '../ModalConfirm.js';

export class ReviewList extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
    this.page = 1;
    this.limit = 5;
    this.total = 0;
  }

  connectedCallback() {
    this.userId = this.getAttribute('user-id');
    this.username = this.getAttribute('user-username');
    this.restaurantId = this.getAttribute('restaurant-id');
    this.editable = this.getAttribute('editable') === 'true';
    this.isOwner = this.getAttribute('is-owner') === '1';
    this.added = this.getAttribute('added') === 'true';
    this.render();
    this.fetchReviews();

    if (this.userId && !this.isOwner) {
      this.shadowRoot.querySelector('#review-form')?.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    this.shadowRoot.querySelector('#prev-page').addEventListener('click', () => {
      if (this.page > 1) {
        this.page--;
        this.fetchReviews();
      }
    });

    this.shadowRoot.querySelector('#next-page').addEventListener('click', () => {
      if (this.page * this.limit < this.total) {
        this.page++;
        this.fetchReviews();
      }
    });
  }

  render() {
    this.shadowRoot.innerHTML = `
      <style>
        .border { border: 1px solid #ccc; padding: 10px; margin: 10px 0; border-radius: 8px; }
        .pagination { margin-top: 10px; }
        .btn-icon { background: none; border: none; cursor: pointer; margin-left: 5px; }
      </style>

      <h2>Avis</h2>

      ${this.isOwner ? `
        <p>Vous êtes le propriétaire de ce restaurant. Pour répondre aux avis allez à la section <a href="/restaurateur/mon-restaurant#commentaires">Gestion des avis</a>.</p>
      ` : this.userId && this.added ? `
        <form id="review-form" enctype="multipart/form-data">
          <label>
            Titre :<br>
            <input type="text" name="title" required>
          </label><br>
          <label>
            Note :<br>
            <input type="number" name="rating" min="1" max="5" value="5" required>
          </label><br>
          <label>
            Commentaire :<br>
            <textarea name="comment" required></textarea>
          </label><br>
          <label>
            Image :<br>
            <input type="file" name="imageFile" accept="image/*">
          </label><br>
          <button type="submit">Envoyer</button>
        </form>
        <p class="add-status" style="color: green; margin-top: 10px;"></p>
      ` : this.added ? `
        <p><a href="/connexion">Connectez-vous pour laisser un commentaire</a></p>
      ` : ''
      }

      <div id="reviews-container"></div>
      <div class="pagination">
        <button id="prev-page">Précédent</button>
        <span id="pagination-info"></span>
        <button id="next-page">Suivant</button>
      </div>
      <modal-confirm id="confirm"></modal-confirm>
    `;
  }

  fetchReviews() {
    const url = this.restaurantId
      ? `/api/restaurants/${this.restaurantId}/reviews?page=${this.page}&limit=${this.limit}`
      : `/api/users/${this.userId}/reviews?page=${this.page}&limit=${this.limit}`;

    fetch(url)
      .then(res => res.json())
      .then(data => {
        const container = this.shadowRoot.querySelector('#reviews-container');
        container.innerHTML = '';

        this.total = data.total;
        this.shadowRoot.querySelector('#pagination-info').innerText = `Page ${this.page} / ${Math.ceil(this.total / this.limit)}`;

        data.data.forEach(review => {
          const div = renderReview({
            review,
            currentUserId: parseInt(this.userId),
            editable: this.editable,
            onEdit: id => this.enableEdit(id),
            onDelete: id => this.confirmDelete(id),
            allowResponse: false,
            onResponseEdit: () => {},
            onResponseDelete: () => {}
          });
          container.appendChild(div);
        });
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
    })
      .then(res => res.json())
      .then(data => {
        const status = this.shadowRoot.querySelector('.add-status');
        if (data.message) {
          status.textContent = data.message;
          status.style.color = 'green';
          form.reset();
          setTimeout(() => this.fetchReviews(), 1000);
        } else if (data.errors) {
          status.textContent = Object.values(data.errors).join(', ');
          status.style.color = 'red';
        }
      });
  }

  confirmDelete(id) {
    this.shadowRoot.querySelector('#confirm').show("Supprimer cet avis ?", () => {
      fetch(`/api/reviews/${id}`, { method: 'DELETE' })
        .then(res => {
          if (res.ok) this.fetchReviews();
        });
    });
  }

  submitEdit(e, id) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData();

    formData.append('title', form.title.value);
    formData.append('comment', form.comment.value);
    formData.append('rating', form.rating.value);

    if (form.imageFile.files.length > 0) {
      formData.append('imageFile', form.imageFile.files[0]);
    }

    if (form.deleteImage?.checked) {
      formData.append('deleteImage', '1');
    }

    fetch(`/api/reviews/${id}`, {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.message) {
          form.parentElement.querySelector('.update-status').textContent = data.message;
          setTimeout(() => this.fetchReviews(), 1000);
        } else if (data.errors) {
          form.parentElement.querySelector('.update-status').style.color = 'red';
          form.parentElement.querySelector('.update-status').textContent = Object.values(data.errors).join(', ');
        }
      });
  }

  enableEdit(id) {
    const div = this.shadowRoot.querySelector(`[data-id="${id}"]`);
    const title = div.querySelector('h4')?.textContent || '';
    const comment = div.querySelector('p')?.textContent || '';
    const rating = div.querySelector('p:nth-of-type(2)')?.textContent.match(/\d+/)?.[0] || 5;
    const image = div.querySelector('img')?.src || null;

    div.innerHTML = `
      <form class="edit-review-form" enctype="multipart/form-data">
        <label>
          Titre :<br>
          <input type="text" name="title" value="${title}" required>
        </label><br>
        <label>
          Note :<br>
          <input type="number" name="rating" min="1" max="5" value="${rating}" required>
        </label><br>
        <label>
          Commentaire :<br>
          <textarea name="comment" required>${comment}</textarea>
        </label><br>
        <label>
          Image :<br>
          <input type="file" name="imageFile" accept="image/*"><br>
          ${image ? `
            <div>
              <img src="${image}" alt="Image actuelle" style="width: 100px; margin-top: 5px;"><br>
              <label>
                <input type="checkbox" name="deleteImage" value="1">
                Supprimer l’image
              </label>
            </div>
          ` : '<em>Aucune image actuellement</em>'}
        </label><br>
        <button type="submit">Mettre à jour</button>
        <button type="button" class="cancel-edit">Annuler</button>
      </form>
      <p class="update-status" style="color: green; margin-top: 10px;"></p>
    `;

    div.querySelector('.edit-review-form').addEventListener('submit', (e) => this.submitEdit(e, id));
    div.querySelector('.cancel-edit').addEventListener('click', () => this.fetchReviews());
  }
}

customElements.define('review-list', ReviewList);