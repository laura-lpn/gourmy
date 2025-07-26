class TabView extends HTMLElement {
  connectedCallback() {
    const tabs = Array.from(this.querySelectorAll('tab-item')).map((tab, i) => ({
      title: tab.getAttribute('title'),
      content: tab.innerHTML.trim(),
      index: i
    }));

    this.renderTabs(tabs);
  }

  renderTabs(tabs) {
    this.innerHTML = `
      <div class="w-full">
        <ul class="flex w-full mb-4 divide-x divide-gray-300" id="tab-buttons">
          ${tabs.map(tab =>
            `<li class="flex-1 text-center">
              <button data-tab="${tab.index}" class="tab-btn w-full text-lg text-black font-second font-medium hover:text-blue py-2 px-4">
                ${tab.title}
              </button>
            </li>`
          ).join('')}
        </ul>
        <div id="tab-content">
          ${tabs.map(tab =>
            `<div id="tab-${tab.index}" class="tab-panel hidden mt-12">
              ${tab.content || '<div class="text-gray-400 italic">Contenu indisponible</div>'}
            </div>`
          ).join('')}
        </div>
      </div>
    `;

    this.querySelectorAll('[data-tab]').forEach(btn => {
      btn.addEventListener('click', () => this.setActiveTab(btn.dataset.tab));
    });

    this.setActiveTab(0);
  }

  setActiveTab(id) {
    const panel = this.querySelector(`#tab-${id}`);
    const btn = this.querySelector(`[data-tab="${id}"]`);

    if (!panel || !btn) return;

    this.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    panel.classList.remove('hidden');

    this.querySelectorAll('.tab-btn').forEach(b =>
      b.classList.remove('border-b-2', 'border-blue', '!text-blue')
    );
    btn.classList.add('border-b-2', 'border-blue', '!text-blue');
  }
}

customElements.define('tab-view', TabView);