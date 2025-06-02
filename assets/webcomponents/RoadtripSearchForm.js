export class RoadtripSearchForm extends HTMLElement {
  connectedCallback() {
    this.innerHTML = `
      <form method="GET" action="/roadtrip/recherche" id="roadtrip-form" class="space-y-4">
        <div id="steps-container" class="space-y-4">
          ${this.buildStepFields(0)}
        </div>
        <button type="button" id="add-step" class="bg-blue-500 text-white px-4 py-2 rounded">+ Ajouter une étape</button>
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Rechercher</button>
      </form>
    `;

    this.stepIndex = 1;
    this.querySelector("#add-step").addEventListener("click", () => this.addStep());

    this.addEventListener("click", (e) => {
      if (e.target.classList.contains("toggle-cuisine-dropdown")) {
        const dropdown = e.target.nextElementSibling;
        dropdown.classList.toggle("hidden");
        return;
      }

      if (e.target.closest(".cuisine-options")) return;

      this.querySelectorAll(".cuisine-options").forEach(el => el.classList.add("hidden"));
    });

    this.addEventListener("change", (e) => {
      if (e.target.type === "checkbox" && e.target.name.includes("[cuisine]")) {
        this.updateCuisineLabel(e.target.closest(".step"));
      }
    });
  }

  buildStepFields(index) {
    const cuisineOptions = ['Végan', 'Végétarien', 'Sans gluten'];
    const checkboxes = cuisineOptions.map(type => `
      <label class="flex items-center space-x-2">
        <input type="checkbox" name="steps[${index}][cuisine][]" value="${type}">
        <span>${type}</span>
      </label>
    `).join('');

    return `
      <div class="step border p-4 rounded bg-gray-50 space-y-2 relative">
        <input type="text" name="steps[${index}][town]" placeholder="Ville" required class="w-full border rounded px-2 py-1">
        <input type="number" name="steps[${index}][meals]" placeholder="Nb repas" min="1" value="1" class="w-full border rounded px-2 py-1">

        <label class="block font-medium">Type(s) de cuisine :</label>
        <div class="relative">
          <button type="button" class="toggle-cuisine-dropdown bg-white border rounded px-2 py-1 w-full text-left">Choisir les types</button>
          <div class="cuisine-options hidden absolute z-10 bg-white border rounded w-full mt-1 shadow p-2 space-y-1 max-h-40 overflow-y-auto">
            ${checkboxes}
          </div>
        </div>
      </div>
    `;
  }

  addStep() {
    const container = this.querySelector("#steps-container");
    container.insertAdjacentHTML("beforeend", this.buildStepFields(this.stepIndex));
    this.stepIndex++;
  }

  updateCuisineLabel(stepElement) {
    const checkboxes = stepElement.querySelectorAll('input[type="checkbox"][name*="[cuisine]"]');
    const selected = Array.from(checkboxes)
      .filter(cb => cb.checked)
      .map(cb => cb.value);

    const button = stepElement.querySelector(".toggle-cuisine-dropdown");
    button.textContent = selected.length ? selected.join(", ") : "Choisir les types";
  }
}

customElements.define("roadtrip-search-form", RoadtripSearchForm);