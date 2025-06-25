// ReviewList.js
import { renderReview } from './ReviewDisplay.js';
import '../ModalConfirm.js';

export class ReviewList extends HTMLElement {
  constructor() {
    super();
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

    this.querySelector('#open-form')?.addEventListener('click', () => this.openAddForm());

    this.querySelector('#prev-page').addEventListener('click', () => {
      if (this.page > 1) {
        this.page--;
        this.fetchReviews();
      }
    });

    this.querySelector('#next-page').addEventListener('click', () => {
      if (this.page * this.limit < this.total) {
        this.page++;
        this.fetchReviews();
      }
    });
  }

  render() {
    this.innerHTML = `
      ${this.isOwner ? `
        <div class="text-sm mb-6 p-4 bg-orange/10 rounded">
          Vous êtes le propriétaire de ce restaurant. Pour répondre aux avis, allez à la section 
          <a href="/restaurateur/mon-restaurant" class="text-orange font-medium underline">Gestion des avis</a>.
        </div>
      ` : this.userId && this.added ? `
        <button id="open-form" class="btn mx-auto block mb-6">Ajouter un avis</button>
      ` : this.added ? `
        <p class="text-center text-sm">
          <a href="/connexion?target=${location.pathname}" class="blue">Connectez-vous pour laisser un commentaire</a>
        </p>
      ` : ''}

      <div id="reviews-container" class="space-y-6"></div>
      <div class="pagination flex items-center justify-center gap-4 mt-6">
        <button id="prev-page" class="btn-secondary">Précédent</button>
        <span id="pagination-info" class="text-sm"></span>
        <button id="next-page" class="btn-secondary">Suivant</button>
      </div>

      <modal-confirm id="confirm"></modal-confirm>
    `;
  }

  openAddForm() {
    const formHtml = `
      <div class="flex flex-col">
        <h2 class="text-xl text-center font-medium font-second mb-4">Ajouter un avis</h2>
        <form id="review-form" enctype="multipart/form-data" class="my-form">
          <div>
            <label>Titre</label>
            <input type="text" name="title" required>
          </div>
          <div>
            <label>Note</label>
            <input type="number" name="rating" min="1" max="5" value="5" required>
          </div>
          <div>
            <label>Commentaire</label>
            <textarea name="comment" rows="4" required></textarea>
          </div>
          <div>
            <label>Image</label>
            <input type="file" name="imageFile" accept="image/*">
          </div>
          <div class="flex gap-4 justify-end mt-4">
            <button type="button" id="cancel-btn" class="btn-secondary">Annuler</button>
            <button type="submit" class="btn">Envoyer</button>
          </div>
        </form>
      </div>
    `;

    const modal = this.querySelector('#confirm');
    modal.showWithContent(formHtml, () => {});
    setTimeout(() => {
      modal.querySelector('form')?.addEventListener('submit', (e) => this.handleSubmit(e));
      modal.querySelector('#cancel-btn')?.addEventListener('click', () => modal.close());
    });
  }

  fetchReviews() {
    const url = this.restaurantId
      ? `/api/restaurants/${this.restaurantId}/reviews?page=${this.page}&limit=${this.limit}`
      : `/api/users/${this.userId}/reviews?page=${this.page}&limit=${this.limit}`;

    fetch(url)
      .then(res => res.json())
      .then(data => {
        const container = this.querySelector('#reviews-container');
        container.innerHTML = '';

        const totalPages = Math.max(1, Math.ceil(this.total / this.limit));
        this.querySelector('#pagination-info').innerText = `Page ${this.page} / ${totalPages}`;

        data.data.forEach(review => {
          const div = renderReview({
            review,
            currentUserId: parseInt(this.userId),
            editable: this.editable,
            onEditReview: id => this.enableEditReview(id),
            onDeleteReview: id => this.confirmDeleteReview(id),
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
        const modal = this.querySelector('#confirm');
        if (data.message) {
          modal.close();
          this.fetchReviews();
        } else if (data.errors) {
          modal.querySelector('.add-status').textContent = Object.values(data.errors).join(', ');
        }
      });
  }

  confirmDeleteReview(id) {
    const modal = this.querySelector('#confirm');
    modal.show("Supprimer cet avis ?", async () => {
      try {
        const res = await fetch(`/api/reviews/${id}`, { method: 'DELETE' });
        if (res.ok) {
          modal.close();
          this.fetchReviews();
        } else {
          console.error("Échec de la suppression");
        }
      } catch (err) {
        console.error("Erreur lors de la suppression :", err);
      }
    });
  }

    enableEditReview(id) {
      const div = this.querySelector(`[data-id="${id}"]`);
      const title = div.querySelector('h4')?.textContent || '';
      const comment = div.querySelector('p.comment')?.textContent || '';
      const rating = div.querySelector('p.rating')?.textContent.match(/\d+/)?.[0] || 5;
      const image = div.querySelector('img')?.src || null;

      const formHtml = `
        <div class="flex flex-col w-full">
          <h2 class="text-xl text-center font-medium font-second mb-4">Modifier un avis</h2>
          <form class="edit-review-form my-form" enctype="multipart/form-data">
            <div>
              <label>Titre</label>
              <input type="text" name="title" value="${title}" required>
            </div>
            <div>
              <label>Note</label>
              <input type="number" name="rating" min="1" max="5" value="${rating}" required>
            </div>
            <div>
              <label>Commentaire</label>
              <textarea name="comment" rows="4" required>${comment}</textarea>
            </div>
            <div class="flex flex-col">
              <label>Image</label>
              <input type="file" name="imageFile" accept="image/*">
              ${image ? `<div class="mt-2"><img src="${image}" class="w-24 mb-2" alt="Image actuelle">
              <label><input type="checkbox" name="deleteImage" value="1" class="mr-2">Supprimer l’image</label></div>` : ''}
            </div>
            <div class="flex gap-4 justify-end mt-4">
              <button type="button" id="cancel-btn" class="btn-secondary">Annuler</button>
              <button type="submit" class="btn">Mettre à jour</button>
            </div>
          </form>
        </div>
      `;

      const modal = this.querySelector('#confirm');
      modal.showWithContent(formHtml, () => {});
      setTimeout(() => {
        modal.querySelector('form')?.addEventListener('submit', (e) => this.submitEdit(e, id));
        modal.querySelector('#cancel-btn')?.addEventListener('click', () => modal.close());
      });
    }

  submitEdit(e, id) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch(`/api/reviews/${id}`, {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        const modal = this.querySelector('#confirm');
        if (data.message) {
          modal.close();
          this.fetchReviews();
        } else if (data.errors) {
          modal.querySelector('.update-status').textContent = Object.values(data.errors).join(', ');
        }
      });
  }
}

customElements.define('review-list', ReviewList);