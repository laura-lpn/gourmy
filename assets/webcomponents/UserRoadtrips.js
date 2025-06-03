import './ModalConfirm.js';

export class UserRoadtrips extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
  }

  connectedCallback() {
    this.render();
    this.fetchRoadtrips();
  }

  render() {
    this.shadowRoot.innerHTML = `
      <style>
        .card { border: 1px solid #ccc; padding: 1em; margin-bottom: 1em; border-radius: 8px; background: #f9f9f9; }
        input, textarea { display: block; width: 100%; margin: 0.5em 0; padding: 0.5em; }
        button { margin-right: 0.5em; }
        .edit-form { margin-top: 1em; }
      </style>
      <h2>Mes roadtrips</h2>
      <div id="roadtrips-container"></div>
      <modal-confirm id="confirm"></modal-confirm>
    `;
  }

  fetchRoadtrips() {
    fetch('/api/user/roadtrips')
      .then(res => res.json())
      .then(data => {
        const container = this.shadowRoot.querySelector('#roadtrips-container');
        container.innerHTML = '';

        data.forEach(rt => {
          const div = document.createElement('div');
          div.className = 'card';
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
      <div class="display">
        <strong>${rt.title}</strong><br/>
        <p>${rt.description}</p>
        <em>${rt.isPublic ? "Public" : "Privé"}</em><br/>
        <button data-action="edit">Modifier</button>
        <button data-action="delete">Supprimer</button>
      </div>
    `;
  }

  enableEdit(rt) {
    const div = this.shadowRoot.querySelector(`[data-id="${rt.id}"]`);
    div.innerHTML = `
      <form class="edit-form">
        <label>Titre :
          <input type="text" name="title" value="${rt.title}" required>
        </label>
        <label>Description :
          <textarea name="description" required>${rt.description}</textarea>
        </label>
        <label>
          Public :
          <input type="checkbox" name="isPublic" ${rt.isPublic ? 'checked' : ''}>
        </label>
        <button type="submit">Enregistrer</button>
        <button type="button" class="cancel-edit">Annuler</button>
      </form>
      <p class="update-status" style="margin-top: 0.5em; color: green;"></p>
    `;

    div.querySelector('.edit-form').addEventListener('submit', (e) => this.submitEdit(e, rt.id));
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
        form.parentElement.querySelector('.update-status').textContent = 'Modifications enregistrées.';
        setTimeout(() => this.fetchRoadtrips(), 1000);
      });
  }

  confirmDelete(id) {
    this.shadowRoot.querySelector('#confirm').show('Supprimer ce roadtrip ?', () => {
      fetch(`/api/user/roadtrips/${id}`, { method: 'DELETE' })
        .then(res => {
          if (res.ok) this.fetchRoadtrips();
        });
    });
  }
}

customElements.define('user-roadtrips', UserRoadtrips);