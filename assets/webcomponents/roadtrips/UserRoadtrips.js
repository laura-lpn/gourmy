import '../ModalConfirm.js';

export class UserRoadtrips extends HTMLElement {
  constructor() {
    super();
    this.editingRoadtripId = null;
    this.isPublicTab = this.getAttribute('is-public') === "true" || false;
    this.username = this.getAttribute('username') || '';
  }

  connectedCallback() {
    this.render();
    this.fetchRoadtrips();
  }

  render() {
    this.innerHTML = `
      <div class="space-y-6">
        <div id="roadtrips-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
        <modal-confirm id="modal"></modal-confirm>
      </div>
    `;
  }

  fetchRoadtrips() {
  const api = this.isPublicTab && this.username ? `/api/user/${this.username}/roadtrips` : '/api/user/roadtrips';
  fetch(api)
    .then(res => res.json())
    .then(data => {
      const container = this.querySelector('#roadtrips-container');
      container.innerHTML = '';

      if (!data.length) {
        container.innerHTML = `
          <div class="col-span-full py-6">
            <p class="text-center">
              ${this.isPublicTab 
                ? "Cet utilisateur n'a pas encore créé de roadtrip"
                : "Vous n'avez pas encore créé de roadtrip"}
            </p>
          </div>
        `;
        return;
      }

      data.forEach(rt => {
        const div = document.createElement('div');
        div.dataset.id = rt.id;
        div.innerHTML = this.renderDisplay(rt);
        container.appendChild(div);

        const editBtn = div.querySelector('[data-action="edit-roadtrip"]');
        if (editBtn) {
          editBtn.addEventListener('click', () => this.enableEditRoadtrip(rt));
        }

        const deleteBtn = div.querySelector('[data-action="delete-roadtrip"]');
        if (deleteBtn) {
          deleteBtn.addEventListener('click', () => this.confirmDeleteRoadtrip(rt.id));
        }
      });
    });
  }

  renderDisplay(rt) {
    const steps = Array.isArray(rt.steps) ? rt.steps : [];
    const stepCount = steps.length;
    const cities = [...new Set(steps.map(s => s.town).filter(Boolean))];
    const images = steps
      .flatMap(s => s.restaurants || [])
      .filter(r => r.banner)
      .slice(0, 3)
      .map(r => r.banner);


    const imageHtml = (() => {
      if (images.length === 1) {
        return `<img src="${images[0]}" alt="Preview" class="object-cover h-24 w-full rounded-md">`;
      } else if (images.length === 2) {
        return `
          <div class="flex gap-2">
            ${images.map(img => `<img src="${img}" alt="Preview" class="object-cover h-24 w-1/2 rounded-md">`).join('')}
          </div>`;
      } else if (images.length === 3) {
        return `
          <div class="flex gap-2 h-24">
            <img src="${images[0]}" alt="Preview" class="object-cover w-1/2 h-full rounded-md">
            <div class="flex flex-col gap-2 w-1/2">
              <img src="${images[1]}" alt="Preview" class="object-cover h-[calc(50%_-_0.25rem)] w-full rounded-md">
              <img src="${images[2]}" alt="Preview" class="object-cover h-[calc(50%_-_0.25rem)] w-full rounded-md">
            </div>
          </div>`;
      }
      return '';
    })();

    return `
      <div class="bg-orange/10 rounded-xl p-6 hover:shadow-main group space-y-2">
        ${imageHtml ? `<div class="mb-4">${imageHtml}</div>` : ''}
        
        <h3 class="text-lg font-second font-medium text-orange mb-2">${rt.title}</h3>

        <div class="text-sm text-gray-700 space-y-1">
          <p><i class="fa-solid fa-signs-post text-orange mr-1"></i>${stepCount} étapes</p>
          <p><i class="fa-solid fa-earth-americas text-orange mr-1"></i>Villes</p>
          <div class="flex flex-wrap gap-2">
            ${cities.map(ville => `<span class="bg-blue text-white rounded-full py-1 px-3 text-xs">${ville}</span>`).join('')}
          </div>
        </div>

        ${this.isPublicTab ? '' : `
          <p class="text-sm mt-2 text-blue/50 font-medium">${rt.isPublic ? 'Public' : 'Privé'}</p>
        `}

        <div class="flex flex-wrap gap-4 pt-2">
          <a href="/roadtrips/${rt.id}" title="Voir" class="text-blue text-sm"><i class="fa-solid fa-eye"></i></a>
          ${this.isPublicTab ? '' : `
            <button data-action="edit-roadtrip" title="Modifier" class="text-blue text-sm"><i class="fa-solid fa-pen"></i></button>
            <button data-action="delete-roadtrip" title="Supprimer" class="text-red-600 text-sm"><i class="fa-solid fa-trash"></i></button>
          `}
        </div>
      </div>
    `;
  }

  enableEditRoadtrip(rt) {
    this.editingRoadtripId = rt.id;
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

  submitEditRoadtrip(e) {
    e.preventDefault();
    const form = e.target;
    const data = {
      title: form.title.value,
      description: form.description.value,
      isPublic: form.isPublic.checked
    };

    fetch(`/api/user/roadtrips/${this.editingRoadtripId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
      .then(res => res.json())
      .then(() => {
        this.querySelector('#modal')?.close();
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