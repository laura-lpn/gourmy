import '../ModalConfirm.js';

export class UserRoadtrips extends HTMLElement {
  constructor() {
    super();
  }

  connectedCallback() {
    this.render();
    this.fetchRoadtrips();
  }

  render() {
    this.innerHTML = `
      <div class="space-y-6">
        <div id="roadtrips-container" class="space-y-6"></div>
        <modal-confirm id="modal"></modal-confirm>
      </div>
    `;
  }

  fetchRoadtrips() {
    fetch('/api/user/roadtrips')
      .then(res => res.json())
      .then(data => {
        const container = this.querySelector('#roadtrips-container');
        container.innerHTML = '';

        data.forEach(rt => {
          const div = document.createElement('div');
          div.className = 'bg-white rounded-xl shadow-main p-6 border border-gray-100';
          div.dataset.id = rt.id;
          div.innerHTML = this.renderDisplay(rt);
          container.appendChild(div);

          div.querySelector('[data-action="edit-roadtrip"]').addEventListener('click', () => this.enableEditRoadtrip(rt));
          div.querySelector('[data-action="delete-roadtrip"]').addEventListener('click', () => this.confirmDeleteRoadtrip(rt.id));
        });
      });
  }

  renderDisplay(rt) {
    return `
      <div class="display space-y-2">
        <h3 class="text-xl font-second text-blue font-medium">${rt.title}</h3>
        <p class="text-gray-700">${rt.description}</p>
        <span class="text-sm text-gray-500 font-medium">${rt.isPublic ? "Public" : "Privé"}</span>
        <div class="flex flex-wrap gap-4 pt-2">
          <a href="/roadtrips/${rt.id}" title="Voir" class="text-blue text-sm"><i class="fa-solid fa-eye"></i></a>
          <button data-action="edit-roadtrip" title="Modifier" class="text-blue text-sm"><i class="fa-solid fa-pen"></i></button>
          <button data-action="delete-roadtrip" title="Supprimer" class="text-red-600 text-sm"><i class="fa-solid fa-trash"></i></button>
        </div>
      </div>
    `;
  }


  enableEditRoadtrip(rt) {
    const modal = this.querySelector('#modal');
    const formHtml = `
    <div class="flex flex-col w-full">
        <h2 class="text-xl text-center font-medium font-second mb-4">Modifier le roadtrip</h2>
      <form id="edit-form" class="my-form">
        <div>
          <label for="title">Titre :</label>
          <input type="text" name="title" value="${rt.title}" required>
        </div>
        <div>
          <label for="description">Description :</label>
          <textarea name="description" rows="3" required>${rt.description}</textarea>
        </div>
        <div class="flex items-center gap-2 mt-2">
          <input type="checkbox" name="isPublic" id="isPublic" class="accent-orange" ${rt.isPublic ? 'checked' : ''}>
          <label for="isPublic">Public</label>
        </div>
        <div class="flex gap-4 justify-end mt-4">
          <button type="button" id="cancel-btn" class="btn-secondary">Annuler</button>
          <button type="submit" class="btn">Modifier</button>
        </div>
      </form>
    </div>
    `;

    modal.showWithContent(formHtml, () => {});
    setTimeout(() => {
      modal.querySelector('form')?.addEventListener('submit', (e) => this.submitEditRoadtrip(e));
      modal.querySelector('#cancel-btn')?.addEventListener('click', () => modal.close());
    });
  }

  submitEditRoadtrip(id) {
    const form = this.querySelector('#edit-form');
    const data = {
      title: form.title.value,
      description: form.description.value,
      isPublic: form.isPublic.checked
    };

    fetch(`/api/user/roadtrips/${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
      .then(res => res.json())
      .then(() => {
        this.fetchRoadtrips();
      });
  }

  confirmDeleteRoadtrip(id) {
    this.querySelector('#modal').show('Supprimer ce roadtrip ?', () => {
      fetch(`/api/user/roadtrips/${id}`, { method: 'DELETE' })
        .then(res => {
          if (res.ok) this.fetchRoadtrips();
        });
    });
  }
}

customElements.define('user-roadtrips', UserRoadtrips);