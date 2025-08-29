export class RoadtripSearchForm extends HTMLElement {
  connectedCallback() {
    this.stepIndex = 0;
    this.minSteps = parseInt(this.getAttribute('data-min-steps') || '1', 10);
    this.cuisineOptions = this.parseCuisineOptions(this.getAttribute('data-cuisines'));

    this.innerHTML = `
      <section class="relative w-full max-w-4xl mx-auto mt-6 z-10">
        <div class="bg-white p-4 rounded-xl shadow-lg w-full">
          <form method="GET" action="/roadtrip/recherche" id="roadtrip-form" class="w-full">
            <!-- En-tête colonnes: visible seulement en sm+ -->
            <!-- En-tête colonnes: visible seulement en sm+ -->
            <div class="hidden sm:grid grid-cols-12 items-center w-full gap-4 px-3">
              <p class="col-span-3 font-second font-medium text-lg">
                <i class="fa-solid fa-map-pin text-blue mr-2" aria-hidden="true"></i><span>Ville</span>
              </p>
              <p class="col-span-4 font-second font-medium text-lg">
                <i class="fa-solid fa-utensils text-blue mr-2" aria-hidden="true"></i><span>Nombre de repas</span>
              </p>
              <p class="col-span-4 font-second font-medium text-lg">
                <i class="fa-solid fa-pepper-hot text-blue mr-2" aria-hidden="true"></i><span>Type de cuisine</span>
              </p>
              <!-- Colonne vide pour aligner le bouton supprimer -->
              <span class="col-span-1"></span>
            </div>
            <div id="steps-container" class="flex flex-col w-full h-fit mt-2 sm:gap-4" aria-live="polite"></div>

            <div class="flex justify-between items-center pt-2 gap-4">
              <button type="button" id="add-step" class="text-orange mx-auto text-sm font-medium flex items-center gap-2 rounded px-2 py-1">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Ajouter une étape</span>
              </button>
              <button type="submit" class="bg-blue text-white font-medium rounded px-6 py-2 hover:bg-blue/90 transition focus:outline-none focus:ring-2 focus:ring-blue">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
              </button>
            </div>
          </form>
        </div>
      </section>
    `;

    // 1ère étape (ou plus selon minSteps)
    const container = this.querySelector('#steps-container');
    for (let i = 0; i < this.minSteps; i++) {
      container.insertAdjacentHTML('beforeend', this.buildStepFields(this.stepIndex++));
    }

    // Écouteurs
    const addBtn = this.querySelector('#add-step');
    if (addBtn) addBtn.addEventListener('click', () => this.addStep());

    this.addEventListener('click', (e) => {
      const target = e.target instanceof Element ? e.target : null;

      if (target && target.closest('.cuisine-options')) {
        e.stopPropagation();
        return;
      }

      // Toggle dropdown cuisines (avec animation)
      const toggleBtn = target ? target.closest('.toggle-cuisine-dropdown') : null;
      if (toggleBtn) {
        const panel = toggleBtn.nextElementSibling;
        const isOpen = toggleBtn.getAttribute('aria-expanded') === 'true';
        // fermer les autres dropdowns d'abord (anim)
        this.hideAllCuisineDropdowns();
        if (!isOpen && panel) this.openDropdown(toggleBtn, panel);
        e.stopPropagation();
        return;
      }

      // Supprimer étape
      const removeBtn = target ? target.closest('.remove-step') : null;
      if (removeBtn) {
        const step = removeBtn.closest('.step');
        if (step) step.remove();
        this.reindexSteps();
        this.updateRemoveButtonsState();
        return;
      }

      // Clic à l'extérieur => fermer dropdowns (anim)
      if (!target || !target.closest('.cuisine-options')) {
        this.hideAllCuisineDropdowns();
      }
    });

    // ESC pour fermer dropdowns
    this.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') this.hideAllCuisineDropdowns();
    });

    // MàJ libellé cuisines
    this.addEventListener('change', (e) => {
      const t = e.target;
      if (t && t.type === 'checkbox' && t.name.includes('[cuisines]')) {
        const step = t.closest('.step');
        if (step) {
          const option = t.closest('[role="option"]');
          if (option) option.setAttribute('aria-selected', t.checked ? 'true' : 'false');
          this.updateCuisineLabel(step);
        }
      }
    });

    // Clavier: Enter/Espace sur le bouton dropdown
    this.addEventListener('keydown', (e) => {
      const t = e.target;
      if (t instanceof Element && t.classList.contains('toggle-cuisine-dropdown')) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          t.click();
        }
      }
    });

    // Init UI
    this.updateRemoveButtonsState();
  }

  parseCuisineOptions(raw) {
    if (!raw || !raw.trim()) return ['Végan', 'Végétarien', 'Sans gluten'];
    try {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed) && parsed.length) return parsed.map(String);
    } catch (_) { /* CSV fallback */ }
    return raw.split(',').map(s => s.trim()).filter(Boolean);
  }

  buildStepFields(index) {
    const idBase = `rt-step-${index}`;
    const cuisineChecks = this.cuisineOptions.map((type, i) => {
      const cid = `${idBase}-cuisine-${i}`;
      return `
        <label class="flex items-center space-x-2 px-1 py-1 rounded hover:bg-gray-50 focus-within:bg-gray-50" role="option" aria-selected="false">
          <input id="${cid}" type="checkbox" name="steps[${index}][cuisines][]" value="${this.escape(type)}" class="accent-orange" />
          <span class="text-sm">${this.escape(type)}</span>
        </label>
      `;
    }).join('');

    return `
      <fieldset class="step relative px-3 pb-2 space-y-3 transition-all duration-200 ease-out" data-index="${index}">
        <legend class="sr-only">Étape ${index + 1}</legend>

        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 sm:items-center w-full">
          <!-- Ville -->
          <div class="sm:col-span-3 w-full">
            <label for="${idBase}-town" class="sm:hidden flex items-center gap-2 font-second font-medium text-lg text-blue mb-1">
              <i class="fa-solid fa-map-pin" aria-hidden="true"></i><span class="text-black">Ville</span>
            </label>
            <label for="${idBase}-town" class="sr-only sm:not-sr-only hidden">Ville</label>
            <input id="${idBase}-town" type="text" name="steps[${index}][town]" placeholder="Ville" required autocomplete="off"
              class="w-full border border-gray-300 rounded px-3 py-2 placeholder:text-gray-500" />
          </div>

          <!-- Nombre de repas -->
          <div class="sm:col-span-4 w-full">
            <label for="${idBase}-meals" class="sm:hidden flex items-center gap-2 font-second font-medium text-lg text-blue mb-1">
              <i class="fa-solid fa-utensils" aria-hidden="true"></i><span class="text-black">Nombre de repas</span>
            </label>
            <label for="${idBase}-meals" class="sr-only sm:not-sr-only hidden">Nombre de repas</label>
            <input id="${idBase}-meals" type="number" name="steps[${index}][meals]" placeholder="Nombre de repas" min="1" step="1" value="1"
              class="w-full border border-gray-300 rounded px-3 py-2 placeholder:text-gray-500" />
          </div>

          <!-- Type de cuisine -->
          <div class="relative sm:col-span-4 w-full">
            <label class="sm:hidden flex items-center gap-2 font-second font-medium text-lg text-blue mb-1">
              <i class="fa-solid fa-pepper-hot" aria-hidden="true"></i><span class="text-black">Type de cuisine</span>
            </label>
            <button type="button"
              class="toggle-cuisine-dropdown relative z-10 bg-white border border-blue rounded-lg px-3 w-full py-2 text-left flex items-center justify-between"
              aria-haspopup="listbox" aria-expanded="false" aria-controls="${idBase}-cuisine-list" data-placeholder="Choisir les types">
              <span class="truncate">Choisir les types</span>
              <i class="fa-solid fa-chevron-down ml-2 text-gray-500" aria-hidden="true"></i>
            </button>
            <!-- z-50 pour passer au-dessus des étapes suivantes -->
            <div id="${idBase}-cuisine-list"
              class="cuisine-options hidden absolute left-0 top-full z-[60] bg-white border rounded w-full mt-1 shadow p-2 space-y-1 max-h-44 overflow-y-auto transition-all duration-200 ease-out opacity-0 -translate-y-2 pointer-events-auto"
              role="listbox" aria-multiselectable="true">
              ${cuisineChecks}
            </div>
          </div>

          <!-- Supprimer -->
          <div class="sm:col-span-1 w-full sm:w-auto flex justify-center">
            <button type="button" class="remove-step text-red-600 text-lg font-medium flex items-center gap-2 rounded" aria-label="Supprimer l’étape">
              <i class="fa-solid fa-xmark mr-1 py-1 sm:py-0 sm:mr-0" aria-hidden="true"></i><span class="sm:hidden text-sm">Supprimer</span>
            </button>
          </div>
        </div>
      </fieldset>
    `;
  }

  addStep() {
    const container = this.querySelector('#steps-container');
    if (!container) return;

    // construire l’élément
    const tmp = document.createElement('div');
    tmp.innerHTML = this.buildStepFields(this.stepIndex).trim();
    const el = tmp.firstElementChild;

    // état initial (avant anim)
    el.classList.add('opacity-0', '-translate-y-2');

    // insérer en bas (grandit vers le bas)
    container.appendChild(el);

    // lancer l’anim au prochain frame
    requestAnimationFrame(() => {
      el.classList.remove('opacity-0', '-translate-y-2');
      el.classList.add('opacity-100', 'translate-y-0');
    });

    this.stepIndex++;
    this.updateRemoveButtonsState();
  }


  reindexSteps() {
    const steps = Array.from(this.querySelectorAll('.step'));
    steps.forEach((step, newIdx) => {
      step.dataset.index = String(newIdx);
      const idBase = `rt-step-${newIdx}`;

      const town = step.querySelector('input[name^="steps"][name$="[town]"]');
      const meals = step.querySelector('input[name^="steps"][name$="[meals]"]');
      const cuisineBtn = step.querySelector('.toggle-cuisine-dropdown');
      const list = step.querySelector('.cuisine-options');

      if (town) {
        town.name = `steps[${newIdx}][town]`;
        town.id = `${idBase}-town`;
        const townLabel = step.querySelector('label[for$="-town"]');
        if (townLabel) townLabel.setAttribute('for', town.id);
      }
      if (meals) {
        meals.name = `steps[${newIdx}][meals]`;
        meals.id = `${idBase}-meals`;
        const mealsLabel = step.querySelector('label[for$="-meals"]');
        if (mealsLabel) mealsLabel.setAttribute('for', meals.id);
      }
      if (cuisineBtn && list) {
        cuisineBtn.setAttribute('aria-controls', `${idBase}-cuisine-list`);
        list.id = `${idBase}-cuisine-list`;
        const options = list.querySelectorAll('input[type="checkbox"]');
        options.forEach((opt, i) => {
          opt.setAttribute('name', `steps[${newIdx}][cuisines][]`);
          const lid = `${idBase}-cuisine-${i}`;
          opt.id = lid;
          const lbl = opt.closest('label');
          if (lbl) lbl.setAttribute('for', lid);
        });
      }

      const legend = step.querySelector('legend');
      if (legend) legend.textContent = `Étape ${newIdx + 1}`;

      this.updateCuisineLabel(step);
    });

    this.stepIndex = steps.length;
  }

  updateCuisineLabel(stepElement) {
    const checkboxes = stepElement.querySelectorAll('input[type="checkbox"][name*="[cuisines]"]');
    const selected = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
    const button = stepElement.querySelector('.toggle-cuisine-dropdown');
    if (!button) return;
    const placeholder = button.getAttribute('data-placeholder') || 'Choisir les types';
    const label = selected.length ? selected.join(', ') : placeholder;
    const span = button.querySelector('span');
    if (span) span.textContent = label;
  }

  // --- Animations dropdown ---
  openDropdown(btn, panel) {
    const step = btn.closest('.step');
    if (step) step.classList.add('z-50');
    btn.setAttribute('aria-expanded', 'true');
    // état initial
    panel.classList.remove('hidden');
    panel.classList.add('opacity-0', '-translate-y-2');
    panel.classList.remove('opacity-100', 'translate-y-0');
    // lancer l'anim au frame suivant
    requestAnimationFrame(() => {
      panel.classList.remove('opacity-0', '-translate-y-2');
      panel.classList.add('opacity-100', 'translate-y-0');
    });
  }

  closeDropdown(btn, panel) {
    btn.setAttribute('aria-expanded', 'false');
    panel.classList.add('opacity-0', '-translate-y-2');
    panel.classList.remove('opacity-100', 'translate-y-0');
    const onEnd = (e) => {
      if (e.target !== panel) return;
      panel.removeEventListener('transitionend', onEnd);
      panel.classList.add('hidden');
    };
    panel.addEventListener('transitionend', onEnd, { once: true });
  }

  hideAllCuisineDropdowns() {
    this.querySelectorAll('.toggle-cuisine-dropdown[aria-expanded="true"]').forEach((btn) => {
      const panel = btn.nextElementSibling;
      if (panel) this.closeDropdown(btn, panel);
    });
    this.querySelectorAll('.step.z-50').forEach(step => step.classList.remove('z-50'));
  }
  // --- /Animations dropdown ---

  updateRemoveButtonsState() {
    const steps = this.querySelectorAll('.step');
    const canRemove = steps.length > this.minSteps;
    this.querySelectorAll('.remove-step').forEach(btn => {
      btn.disabled = !canRemove;
      btn.classList.toggle('opacity-50', !canRemove);
      btn.classList.toggle('cursor-not-allowed', !canRemove);
      btn.setAttribute('aria-disabled', String(!canRemove));
    });
  }

  escape(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
}

if (!customElements.get('roadtrip-search-form')) {
  customElements.define('roadtrip-search-form', RoadtripSearchForm);
}
