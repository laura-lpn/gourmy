export class SearchBar extends HTMLElement {
  connectedCallback() {
    this.innerHTML = `
      <div class="w-full max-w-xl mx-auto mt-8">
        <input type="text" placeholder="Rechercher un restaurant..."
          class="w-full p-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange"
          id="restaurant-search">
        <button id="clear-search" class="absolute top-1/2 right-3 transform -translate-y-1/2 text-gray-500 hover:text-orange hidden">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    `;

    this.url = this.dataset.url;
    this.input = this.querySelector("#restaurant-search");
    this.clearBtn = this.querySelector("#clear-search");
    this.timeout = null;

    this.input.addEventListener("input", () => {
      clearTimeout(this.timeout);
      this.timeout = setTimeout(() => this.search(), 300);

      this.clearBtn.classList.toggle("hidden", !this.input.value.trim());
    });

    this.clearBtn.addEventListener("click", () => {
      this.input.value = '';
      this.clearBtn.classList.add("hidden");
      this.search();
    });
  }

  async search() {
    const query = this.input.value.trim();
    const response = await fetch(`${this.url}?q=${encodeURIComponent(query)}`);
    const html = await response.text();
    const container = document.querySelector("#restaurant-results");
    if (container) container.innerHTML = html;
  }
}

customElements.define("search-bar", SearchBar);