import { renderReview } from './ReviewDisplay.js';
import '../ModalConfirm.js';

export class RestaurantReviewManager extends HTMLElement {
  constructor() {
    super();
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

  closeEditingResponse = () => {
    this.editingResponseId = null;
    this.render();
  }

  render() {
    this.innerHTML = `
      <style>.review { margin-bottom: 1rem; }</style>
      <div id="reviews-container" class="space-y-6"></div>
      <modal-confirm id="modal"></modal-confirm>
    `;

    const container = this.querySelector('#reviews-container');
    container.innerHTML = '';
    if (this.reviews.length === 0) {
      container.innerHTML = '<p class="text-center">Aucun avis pour le moment</p>';
      return;
    }

    this.reviews.forEach(review => {
      const div = renderReview({
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
      container.appendChild(div);
    });
  }

  async submitResponse(id, comment, type) {
    if (type === 'refresh') return;

    const endpoint = type === 'new'
      ? `/api/reviews/${id}/response`
      : `/api/reviews/${id}`;

    const res = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ comment })
    });

    if (res.ok) this.fetchAndRender();
  }

  confirmDelete(responseId) {
    this.querySelector('#modal').show("Supprimer cette réponse ?", () => {
      fetch(`/api/reviews/${responseId}`, { method: 'DELETE' })
        .then(res => {
          if (res.ok) this.fetchAndRender();
        });
    });
  }
}
customElements.define('restaurant-review-manager', RestaurantReviewManager);