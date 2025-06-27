class TabView extends HTMLElement {
  connectedCallback() {
    let config;

    try {
      config = JSON.parse(this.getAttribute('tabs') || '[]');
    } catch (e) {
      console.error('TabView: impossible de parser les onglets', e);
      return;
    }

    this.renderTabs(config);
  }

  renderTabs(tabs) {
    this.innerHTML = `
      <div class="w-full">
        <ul class="flex w-full mb-4 divide-x divide-gray-300" id="tab-buttons">
          ${tabs.map((tab, i) =>
            `<li class="flex-1 text-center">
              <button data-tab="${i}" class="tab-btn w-full text-lg text-black font-second font-medium hover:text-blue py-2 px-4">
                ${tab.title}
              </button>
            </li>`
          ).join('')}
        </ul>
        <div id="tab-content">
          ${tabs.map((tab, i) =>
            `<div id="tab-${i}" class="tab-panel hidden mt-12">
              ${tab.content || '<div class="text-gray-400 italic">Contenu indisponible</div>'}
            </div>`
          ).join('')}
        </div>
      </div>
    `;

    this.querySelectorAll('[data-tab]').forEach(btn => {
      btn.addEventListener('click', () => this.setActiveTab(btn.dataset.tab));
    });

    // 🔁 Attente active : quand #tab-0 existe, on l’active
    const waitForFirstTab = () => {
      const panel = this.querySelector('#tab-0');
      const btn = this.querySelector('[data-tab="0"]');

      if (panel && btn) {
        this.setActiveTab(0);
      } else {
        setTimeout(waitForFirstTab, 20);
      }
    };

    waitForFirstTab();
  }

  setActiveTab(id) {
    const panel = this.querySelector(`#tab-${id}`);
    const btn = this.querySelector(`[data-tab="${id}"]`);

    if (!panel || !btn) {
      console.warn(`TabView: onglet #${id} introuvable.`);
      return;
    }

    this.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    panel.classList.remove('hidden');

    this.querySelectorAll('.tab-btn').forEach(b =>
      b.classList.remove('border-b-2', 'border-blue', '!text-blue')
    );
    btn.classList.add('border-b-2', 'border-blue', '!text-blue');
  }
}

customElements.define('tab-view', TabView);