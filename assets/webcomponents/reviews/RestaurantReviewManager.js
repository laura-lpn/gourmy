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
      <modal-confirm id="confirm"></modal-confirm>
    `;

    const container = this.querySelector('#reviews-container');
    container.innerHTML = '';

    this.reviews.forEach(review => {
      const div = renderReview({
        review,
        currentUserId: null,
        editable: false,
        onEdit: () => {},
        onDelete: () => {},
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
    if (!comment?.trim()) {
      alert("Le champ commentaire est requis.");
      return;
    }

    const formData = new FormData();
    formData.append('comment', comment.trim());

    const endpoint = type === 'new'
      ? `/api/reviews/${id}/response`
      : `/api/reviews/${id}`;

    const res = await fetch(endpoint, {
      method: 'POST',
      body: formData
    });

    if (res.ok) {
      this.fetchAndRender();
    } else {
      alert("Erreur lors de l'envoi de la réponse.");
    }
  }

  confirmDelete(responseId) {
    this.querySelector('#confirm').show("Supprimer cette réponse ?", () => {
      fetch(`/api/reviews/${responseId}`, { method: 'DELETE' })
        .then(res => {
          if (res.ok) this.fetchAndRender();
        });
    });
  }
}
customElements.define('restaurant-review-manager', RestaurantReviewManager);