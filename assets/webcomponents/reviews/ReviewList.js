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

    this.querySelector('#prev-page')?.addEventListener('click', () => {
      if (this.page > 1) {
        this.page--;
        this.fetchReviews();
      }
    });

    this.querySelector('#next-page')?.addEventListener('click', () => {
      if (this.page * this.limit < this.total) {
        this.page++;
        this.fetchReviews();
      }
    });
  }

  render() {
    this.innerHTML = `
    <div class="py-6 max-w-5xl w-11/12 mx-auto">
      ${this.isOwner ? `
        <div class="text-sm mb-6 p-4 bg-orange/10 rounded">
          Vous êtes le propriétaire de ce restaurant. Pour répondre aux avis, allez à la section 
          <a href="/restaurateur/mon-restaurant" class="text-orange font-medium underline">Gestion des avis</a>.
        </div>
      ` : this.userId && this.added ? `
        <button id="open-form" class="btn mx-auto block mb-6">Ajouter un avis</button>
      ` : this.added ? `
        <p class="text-center text-sm mb-4">
          <a href="/connexion?target=${location.pathname}" class="btn-secondary">Connectez-vous pour laisser un commentaire</a>
        </p>
      ` : ''}

      <div id="reviews-container" class="space-y-4 sm:space-y-6"></div>

      <div class="pagination mt-6 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
        <button id="prev-page" class="btn-secondary w-full sm:w-auto">Précédent</button>
        <span id="pagination-info" class="text-sm order-first sm:order-none" aria-live="polite"></span>
        <button id="next-page" class="btn-secondary w-full sm:w-auto">Suivant</button>
      </div>

      <modal-confirm id="confirm"></modal-confirm>
    </div>
    `;
  }

  openAddForm() {
    const formHtml = `
      <div class="flex flex-col">
        <h2 class="text-xl text-center font-medium font-second mb-4">Ajouter un avis</h2>
        <form id="review-form" enctype="multipart/form-data" class="my-form grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <div>
            <label class="block mb-1">Titre</label>
            <input type="text" name="title" required class="w-full border px-3 py-2 rounded">
          </div>
          <div>
            <label class="block mb-1">Note</label>
            <input type="number" name="rating" min="1" max="5" value="5" required class="w-full border px-3 py-2 rounded">
          </div>
          <div class="sm:col-span-2">
            <label class="block mb-1">Commentaire</label>
            <textarea name="comment" rows="4" required class="w-full border px-3 py-2 rounded"></textarea>
          </div>
          <div class="sm:col-span-2">
            <label class="block mb-1">Image</label>
            <input type="file" name="imageFile" accept="image/*" class="w-full border px-3 py-2 rounded">
          </div>
          <div class="sm:col-span-2 flex flex-col-reverse sm:flex-row gap-3 justify-end mt-2">
            <button type="button" id="cancel-btn" class="btn-secondary w-full sm:w-auto">Annuler</button>
            <button type="submit" class="btn w-full sm:w-auto">Envoyer</button>
          </div>
        </form>
      </div>
    `;

    const modal = this.querySelector('#confirm');
    modal.showWithContent(formHtml, () => {});
    setTimeout(() => {
      const frm = modal.querySelector('form');
      frm?.addEventListener('submit', (e) => this.handleSubmit(e));
      modal.querySelector('#cancel-btn')?.addEventListener('click', () => modal.close());
      frm?.querySelector('input[name="title"]')?.focus();
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
        const pagin = this.querySelector('.pagination');
        const prev = this.querySelector('#prev-page');
        const next = this.querySelector('#next-page');
        const info = this.querySelector('#pagination-info');

        container.innerHTML = '';

        // total depuis l’API (fallbacks courants)
        this.total = data.total ?? data.meta?.total ?? data.count ?? data.data?.length ?? 0;

        const totalPages = Math.max(1, Math.ceil(this.total / this.limit));
        if (info) info.textContent = `Page ${Math.min(this.page, totalPages)} / ${totalPages}`;

        // Affichage/état pagination
        if (!this.total || (data.data && data.data.length === 0)) {
          pagin?.classList.add('hidden');
        } else {
          pagin?.classList.remove('hidden');
        }

        if (prev) {
          prev.disabled = this.page <= 1;
          prev.classList.toggle('opacity-50', this.page <= 1);
          prev.setAttribute('aria-disabled', String(this.page <= 1));
        }
        if (next) {
          const isLast = this.page >= totalPages;
          next.disabled = isLast;
          next.classList.toggle('opacity-50', isLast);
          next.setAttribute('aria-disabled', String(isLast));
        }

        if (!data.data || data.data.length === 0) {
          container.innerHTML = '<p class="text-center">Aucun avis pour le moment</p>';
          return;
        }

        data.data.forEach(review => {
          const div = renderReview({
            review,
            currentUserId: this.userId ? parseInt(this.userId, 10) : null,
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

    fetch('/api/reviews', { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
        const modal = this.querySelector('#confirm');
        if (data.message) {
          modal.close();
          this.page = 1; // retour page 1 après ajout
          this.fetchReviews();
        } else if (data.errors) {
          // Affichage simple des erreurs
          alert(Object.values(data.errors).join('\n'));
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
          // si on supprime le dernier de la page, remonte d’une page si besoin
          if ((this.page - 1) * this.limit >= this.total - 1) {
            this.page = Math.max(1, this.page - 1);
          }
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
        <form class="edit-review-form my-form grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4" enctype="multipart/form-data">
          <div>
            <label class="block mb-1">Titre</label>
            <input type="text" name="title" value="${title}" required class="w-full border px-3 py-2 rounded">
          </div>
          <div>
            <label class="block mb-1">Note</label>
            <input type="number" name="rating" min="1" max="5" value="${rating}" required class="w-full border px-3 py-2 rounded">
          </div>
          <div class="sm:col-span-2">
            <label class="block mb-1">Commentaire</label>
            <textarea name="comment" rows="4" required class="w-full border px-3 py-2 rounded">${comment}</textarea>
          </div>
          <div class="sm:col-span-2 flex flex-col">
            <label class="block mb-1">Image</label>
            <input type="file" name="imageFile" accept="image/*" class="w-full border px-3 py-2 rounded">
            ${image ? `
              <div class="mt-2">
                <img src="${image}" class="w-24 mb-2 rounded" alt="Image actuelle">
                <label class="inline-flex items-center gap-2 text-sm">
                  <input type="checkbox" name="deleteImage" value="1" class="accent-orange">Supprimer l’image
                </label>
              </div>` : ''}
          </div>
          <div class="sm:col-span-2 flex flex-col-reverse sm:flex-row gap-3 justify-end mt-2">
            <button type="button" id="cancel-btn" class="btn-secondary w-full sm:w-auto">Annuler</button>
            <button type="submit" class="btn w-full sm:w-auto">Mettre à jour</button>
          </div>
        </form>
      </div>
    `;

    const modal = this.querySelector('#confirm');
    modal.showWithContent(formHtml, () => {});
    setTimeout(() => {
      const frm = modal.querySelector('form');
      frm?.addEventListener('submit', (e) => this.submitEdit(e, id));
      modal.querySelector('#cancel-btn')?.addEventListener('click', () => modal.close());
      frm?.querySelector('input[name="title"]')?.focus();
    });
  }

  submitEdit(e, id) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch(`/api/reviews/${id}`, { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
        const modal = this.querySelector('#confirm');
        if (data.message) {
          modal.close();
          this.fetchReviews();
        } else if (data.errors) {
          alert(Object.values(data.errors).join('\n'));
        }
      });
  }
}

customElements.define('review-list', ReviewList);