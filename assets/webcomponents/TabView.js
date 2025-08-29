class TabView extends HTMLElement {
  connectedCallback() {
    const items = Array.from(this.querySelectorAll('tab-item'));
    const tabs = items.map((tab, i) => ({
      title: tab.getAttribute('title') || `Onglet ${i + 1}`,
      content: tab.innerHTML.trim(),
      index: i,
      active: tab.hasAttribute('active')
    }));
    this.renderTabs(tabs);
  }

  renderTabs(tabs) {
    const defaultIndex = Math.max(0, tabs.findIndex(t => t.active));

    this.innerHTML = `
      <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
      </style>

      <div class="w-full">
        <div>
          <ul id="tab-buttons"
              class="flex gap-0 overflow-x-auto no-scrollbar snap-x snap-mandatory pb-1
                     divide-x divide-gray-300
                     md:overflow-visible md:snap-none md:gap-0"
              role="tablist" aria-label="Onglets">
            ${tabs.map(tab => `
              <li class="flex-shrink-0 snap-start md:flex-1">
                <button
                  id="tabbtn-${tab.index}"
                  data-tab="${tab.index}"
                  role="tab"
                  aria-controls="tab-${tab.index}"
                  aria-selected="false"
                  class="tab-btn inline-flex items-center justify-center
                         text-[0.85rem] sm:text-sm lg:text-base
                         text-black font-second font-medium
                         hover:text-blue transition-colors
                         px-3 py-2 sm:px-4 sm:py-3 md:px-5 md:py-3
                         whitespace-nowrap truncate
                         min-w-[6.5rem] sm:min-w-[7rem] md:min-w-0
                         max-w-[9.5rem] sm:max-w-[11rem] md:max-w-none
                         md:w-full md:justify-center
                         border-b-2 border-transparent">
                  ${tab.title}
                </button>
              </li>`).join('')}
          </ul>
        </div>

        <div id="tab-content" class="mt-6 sm:mt-8">
          ${tabs.map(tab => `
            <section id="tab-${tab.index}" class="tab-panel hidden"
                     role="tabpanel" aria-labelledby="tabbtn-${tab.index}" tabindex="0">
              ${tab.content || '<div class="text-gray-400 italic">Contenu indisponible</div>'}
            </section>`).join('')}
        </div>
      </div>
    `;

    // Click
    this.querySelectorAll('[data-tab]').forEach(btn => {
      btn.addEventListener('click', () =>
        this.setActiveTab(parseInt(btn.dataset.tab, 10), { focus: true })
      );
    });

    // Clavier (← → Home End)
    const list = this.querySelector('#tab-buttons');
    list.addEventListener('keydown', (e) => {
      const btns = Array.from(this.querySelectorAll('.tab-btn'));
      const current = btns.findIndex(b => b.getAttribute('aria-selected') === 'true');
      if (current === -1) return;

      let next = current;
      if (e.key === 'ArrowRight') next = (current + 1) % btns.length;
      if (e.key === 'ArrowLeft')  next = (current - 1 + btns.length) % btns.length;
      if (e.key === 'Home')       next = 0;
      if (e.key === 'End')        next = btns.length - 1;

      if (next !== current) {
        e.preventDefault();
        this.setActiveTab(next, { focus: true });
        btns[next].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
      }
    });

    this.setActiveTab(defaultIndex >= 0 ? defaultIndex : 0);
  }

  setActiveTab(id, opts = { focus: false }) {
    const panel = this.querySelector(`#tab-${id}`);
    const btn   = this.querySelector(`#tabbtn-${id}`);
    if (!panel || !btn) return;

    // Panels
    this.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    panel.classList.remove('hidden');

    // Boutons & ARIA
    this.querySelectorAll('.tab-btn').forEach(b => {
      b.classList.remove('text-blue', 'border-blue');
      b.classList.add('border-transparent');
      b.setAttribute('aria-selected', 'false');
      b.setAttribute('tabindex', '-1');
    });
    btn.classList.add('text-blue', 'border-blue');
    btn.classList.remove('border-transparent');
    btn.setAttribute('aria-selected', 'true');
    btn.setAttribute('tabindex', '0');

    if (!window.matchMedia('(min-width: 1024px)').matches) {
      btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }

    if (opts.focus) btn.focus({ preventScroll: true });
  }
}

customElements.define('tab-view', TabView);