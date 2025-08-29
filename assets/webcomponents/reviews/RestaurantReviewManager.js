// RestaurantReviewManager.js
import { renderReview } from './ReviewDisplay.js';
import '../ModalConfirm.js';

export class RestaurantReviewManager extends HTMLElement {
  constructor() {
    super();
    this.restaurantId = this.getAttribute('restaurant-id');
    this.csrf = this.getAttribute('csrf') || '';
    this.reviews = [];
    this.loading = false;
    this.error = null;
    this._mounted = false;
  }

  connectedCallback() {
    this._mounted = true;
    this.fetchAndRender();
  }

  disconnectedCallback() {
    this._mounted = false;
  }

  async fetchAndRender() {
    try {
      this.loading = true;
      this.error = null;
      this.render(); // affiche le skeleton
      const res = await fetch(`/api/restaurants/${this.restaurantId}/reviews`);
      if (!res.ok) throw new Error('Impossible de récupérer les avis.');
      const data = await res.json();
      this.reviews = Array.isArray(data?.data) ? data.data : [];
      this.loading = false;
      if (!this._mounted) return;
      this.render();
      // focus accessible sur le conteneur après MAJ
      const region = this.querySelector('[data-region="reviews"]');
      region && region.focus({ preventScroll: true });
    } catch (err) {
      this.loading = false;
      this.error = err?.message || 'Une erreur est survenue.';
      if (!this._mounted) return;
      this.render();
      alert(this.error);
    }
  }

  closeEditingResponse = () => {
    // Pas de state compliqué ici, c’est le composant enfant qui gère l’édition inline.
    this.render();
  };

  render() {
    this.innerHTML = `
      <div class="py-6 w-full">
        <div class="max-w-4xl w-11/12 mx-auto">
          <div class="mb-4 sr-only" role="status" aria-live="polite">${this.loading ? 'Chargement…' : (this.error || '')}</div>

          <div id="reviews-container"
               data-region="reviews"
               class="space-y-6 outline-none"
               tabindex="-1"
               aria-busy="${this.loading ? 'true' : 'false'}">
            ${this.loading ? this._renderSkeleton(3) : ''}
          </div>

          <modal-confirm id="modal"></modal-confirm>
        </div>
      </div>
    `;

    if (this.loading) return;

    const container = this.querySelector('#reviews-container');
    container.innerHTML = '';

    if (!this.reviews.length) {
      container.innerHTML = '<p class="text-center text-sm text-gray-500">Aucun avis pour le moment</p>';
      return;
    }

    this.reviews.forEach(review => {
      const node = renderReview({
        review,
        currentUserId: null,
        editable: false,
        onEditReview: () => {},
        onDeleteReview: () => {},
        allowResponse: true,
        onResponseEdit: (id, comment, type) => {
          if (type === 'refresh') {
            this.closeEditingResponse();
          } else {
            this.submitResponse(id, comment, type);
          }
        },
        onResponseDelete: id => this.confirmDelete(id)
      });
      container.appendChild(node);
    });
  }

  _renderSkeleton(n = 3) {
    // petites cartes skeleton responsives
    return Array.from({ length: n }).map(() => `
      <div class="bg-white rounded-2xl shadow-main p-6 flex flex-col md:flex-row gap-6 animate-pulse">
        <div class="w-48 aspect-square bg-gray-200 rounded-xl"></div>
        <div class="flex-1 space-y-3">
          <div class="h-5 bg-gray-200 rounded w-1/3"></div>
          <div class="h-4 bg-gray-200 rounded w-1/2"></div>
          <div class="h-4 bg-gray-200 rounded w-2/3"></div>
          <div class="h-4 bg-gray-200 rounded w-1/4"></div>
        </div>
      </div>
    `).join('');
  }

  async submitResponse(id, comment, type) {
    if (type === 'refresh') return;

    const endpoint = (type === 'new')
      ? `/api/reviews/${id}/response`
      : `/api/reviews/${id}`;

    try {
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          ...(this.csrf ? { 'X-CSRF-TOKEN': this.csrf } : {})
        },
        body: JSON.stringify({ comment })
      });
      if (!res.ok) throw new Error('Échec de l’enregistrement de la réponse.');
      await this.fetchAndRender();
    } catch (err) {
      alert(err?.message || 'Une erreur est survenue.');
    }
  }

  confirmDelete(responseId) {
    const modal = this.querySelector('#modal');
    modal.show('Supprimer cette réponse ?', async () => {
      try {
        const res = await fetch(`/api/reviews/${responseId}`, {
          method: 'DELETE',
          headers: this.csrf ? { 'X-CSRF-TOKEN': this.csrf } : {}
        });
        if (!res.ok) throw new Error('Suppression impossible.');
        await this.fetchAndRender();
      } catch (err) {
        alert(err?.message || 'Une erreur est survenue.');
      }
    });
  }
}

customElements.define('restaurant-review-manager', RestaurantReviewManager);