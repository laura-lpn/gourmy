class TabView extends HTMLElement {
  connectedCallback() {
    const config = JSON.parse(this.getAttribute('tabs') || '[]');
    this.renderTabs(config);
  }

  renderTabs(tabs) {
    this.innerHTML = `
      <div>
        <ul class="flex space-x-4 border-b mb-4" id="tab-buttons">
          ${tabs.map((tab, i) =>
            `<li><button data-tab="${i}" class="tab-btn text-gray-600 hover:text-black py-2 px-4">${tab.title}</button></li>`
          ).join('')}
        </ul>
        <div id="tab-content">
          ${tabs.map((tab, i) =>
            `<div id="tab-${i}" class="tab-panel hidden">${tab.content}</div>`
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
    this.querySelectorAll('.tab-panel').forEach(panel => {
      panel.classList.add('hidden')
    });
    this.querySelector(`#tab-${id}`).classList.remove('hidden');

    this.querySelectorAll('.tab-btn').forEach(btn => {
      btn.classList.remove('border-b-2', 'border-black', 'font-bold');
    });
    this.querySelector(`[data-tab="${id}"]`).classList.add('border-b-2', 'border-black', 'font-bold');
  }
}
customElements.define('tab-view', TabView);
