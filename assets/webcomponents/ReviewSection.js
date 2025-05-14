export class ReviewSection extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
    this.page = 1;
    this.limit = 5;
    this.totalReviews = 0;
    this.restaurantId = this.getAttribute('restaurant-id');
    this.user = JSON.parse(this.getAttribute('user') || 'null');
  }

  connectedCallback() {
    this.render();
    this.fetchReviews();

    if (this.user) {
      this.shadowRoot.querySelector('#review-form').addEventListener('submit', (e) => this.handleSubmit(e));
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
      <style>
        .border { border: 1px solid #ccc; padding: 10px; margin: 10px 0; }
        .pagination { margin-top: 10px; }
      </style>
      <div>
        <h2>Commentaires</h2>
        ${this.user ? `
          <form id="review-form" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Titre" required><br>
            <input type="number" name="rating" min="1" max="5" value="5"><br>
            <textarea name="comment" placeholder="Commentaire" required></textarea><br>
            <input type="file" name="imageFile"><br>
            <button type="submit">Envoyer</button>
          </form>
        ` : `
          <p><a href="/connexion">Connectez-vous pour laisser un commentaire</a></p>
        `}
        <div id="reviews-container"></div>
        <div class="pagination">
          <button id="prev-page">Précédent</button>
          <span id="pagination-info"></span>
          <button id="next-page">Suivant</button>
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
          div.innerHTML = `
            <h3>${r.title}</h3>
            <p>${r.comment}</p>
            <p><strong>Note:</strong> ${r.rating}/5</p>
            <p>${r.author}</p>
            ${r.response ? `
              <div class="response">
                <p><strong>Réponse du restaurateur:</strong> ${r.response.comment}</p>
                <p>${r.response.author}</p>
              </div>
            ` : ''}
            ${(this.user && this.user.id === r.author.id) ? `<button data-id="${r.id}" class="delete-btn">Supprimer</button>` : ''}
          `;
          container.appendChild(div);
        });

        this.totalReviews = data.total;
        this.shadowRoot.querySelector('#pagination-info').innerText = `Page ${this.page} sur ${Math.ceil(this.totalReviews / this.limit)}`;

        this.shadowRoot.querySelectorAll('.delete-btn').forEach(btn => {
          btn.addEventListener('click', () => this.deleteReview(btn.dataset.id));
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
}

customElements.define('review-section', ReviewSection);
