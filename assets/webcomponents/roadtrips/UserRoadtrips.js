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
        <modal-confirm id="confirm"></modal-confirm>
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

          div.querySelector('[data-action="edit"]').addEventListener('click', () => this.enableEdit(rt));
          div.querySelector('[data-action="delete"]').addEventListener('click', () => this.confirmDelete(rt.id));
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
          <a href="/roadtrips/${rt.id}" class="btn-secondary border-blue text-blue hover:bg-blue/10">Voir</a>
          <button data-action="edit" class="btn">Modifier</button>
          <button data-action="delete" class="btn-secondary text-red-600 border-red-600 hover:bg-red-100">Supprimer</button>
        </div>
      </div>
    `;
  }


  enableEdit(rt) {
    const div = this.querySelector(`[data-id="${rt.id}"]`);
    div.innerHTML = `
      <form class="my-form">
        <div>
          <label for="title">Titre :</label>
          <input type="text" name="title" value="${rt.title}" required>
        </div>
        <div>
          <label for="description">Description :</label>
          <textarea name="description" rows="3" required>${rt.description}</textarea>
        </div>
        <div class="flex items-center gap-2">
          <input type="checkbox" name="isPublic" id="isPublic" class="accent-orange" ${rt.isPublic ? 'checked' : ''}>
          <label for="isPublic">Public</label>
        </div>
        <div class="flex gap-4 justify-end mt-4">
          <button type="submit" class="btn">Enregistrer</button>
          <button type="button" class="btn-secondary text-red-600 border-red-600 hover:bg-red-100 cancel-edit">Annuler</button>
        </div>
        <p class="update-status text-green-600 text-sm mt-2"></p>
      </form>
    `;

    div.querySelector('.my-form').addEventListener('submit', (e) => this.submitEdit(e, rt.id));
    div.querySelector('.cancel-edit').addEventListener('click', () => this.fetchRoadtrips());
  }

  submitEdit(e, id) {
    e.preventDefault();
    const form = e.target;
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
      .then(response => {
        form.querySelector('.update-status').textContent = 'Modifications enregistrées.';
        setTimeout(() => this.fetchRoadtrips(), 1000);
      });
  }

  confirmDelete(id) {
    this.querySelector('#confirm').show('Supprimer ce roadtrip ?', () => {
      fetch(`/api/user/roadtrips/${id}`, { method: 'DELETE' })
        .then(res => {
          if (res.ok) this.fetchRoadtrips();
        });
    });
  }
}

customElements.define('user-roadtrips', UserRoadtrips);