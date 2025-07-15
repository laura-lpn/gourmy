export class RoadtripSearchForm extends HTMLElement {
  connectedCallback() {
    this.innerHTML = `
      <section class="relative w-full min-w-[50rem] mx-auto mt-4 z-10">
        <div class="bg-white p-6 rounded-xl shadow-lg w-full">
          <form method="GET" action="/roadtrip/recherche" id="roadtrip-form" class="space-y-4 w-full">
            <div class="flex items-center w-full h-fit gap-4 p-2">
              <p class="font-second font-medium text-lg w-1/3">
                <i class="fa-solid fa-map-pin text-blue mr-2"></i>Ville
              </p>
              <p class="font-second font-medium text-lg w-auto">
                <i class="fa-solid fa-utensils text-blue mr-2"></i>Nombre de repas
              </p>
              <p class="font-second font-medium text-lg w-1/3 ml-2">
                <i class="fa-solid fa-pepper-hot text-blue mr-2"></i>Type de cuisine
              </p>
            </div>
            <div id="steps-container" class="space-y-4 flex flex-col w-full h-fit">
              ${this.buildStepFields(0)}
            </div>
            <div class="flex justify-between items-center pt-2">
              <button type="button" id="add-step" class="text-orange mx-auto text-sm font-medium flex items-center gap-1">
                <i class="fa-solid fa-plus"></i> Ajouter une étape
              </button>
              <button type="submit" class="bg-blue text-white font-medium rounded px-6 py-2 hover:bg-blue/90 transition">
                <i class="fa-solid fa-magnifying-glass"></i>
              </button>
            </div>
          </form>
        </div>
      </section>
    `;

    this.stepIndex = 1;
    this.querySelector("#add-step").addEventListener("click", () => this.addStep());

    this.addEventListener("click", (e) => {
      if (e.target.classList.contains("toggle-cuisine-dropdown")) {
        const dropdown = e.target.nextElementSibling;
        dropdown.classList.toggle("hidden");
        e.stopPropagation();
        return;
      }

      if (e.target.closest(".cuisine-options")) {
        e.stopPropagation();
        return;
      }

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
        <input type="checkbox" name="steps[${index}][cuisine][]" value="${type}" class="accent-orange">
        <span class="text-sm">${type}</span>
      </label>
    `).join('');

    return `
      <div class="step p-2 rounded-xl space-y-3">
        <div class="flex gap-4 w-full h-10">
          <input type="text" name="steps[${index}][town]" placeholder="Ville" required class="!w-1/3 border border-gray-300 rounded px-3 py-2 placeholder:text-gray-500">
          <input type="number" name="steps[${index}][meals]" placeholder="Nombre de repas" min="1" value="1" class="!w-auto border border-gray-300 rounded px-3 py-2 placeholder:text-gray-500">
          <div class="relative w-1/3">
            <button type="button" class="toggle-cuisine-dropdown bg-white border border-blue rounded-lg px-3 w-full py-2 text-left text-gray-700">
              Choisir les types
            </button>
            <div class="cuisine-options hidden absolute z-10 bg-white border rounded w-full mt-1 shadow p-2 space-y-1 max-h-40 overflow-y-auto">
              ${checkboxes}
            </div>
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