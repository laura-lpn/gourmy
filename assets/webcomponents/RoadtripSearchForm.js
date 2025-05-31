class RoadtripSearchForm extends HTMLElement {
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
  }

  buildStepFields(index) {
    return `
      <div class="step border p-4 rounded bg-gray-50 space-y-2">
        <input type="text" name="steps[${index}][town]" placeholder="Ville" required class="w-full border rounded px-2 py-1">
        <input type="number" name="steps[${index}][meals]" placeholder="Nb repas" min="1" value="1" class="w-full border rounded px-2 py-1">
        
        <label class="block font-medium">Type(s) de cuisine :</label>
        <select name="steps[${index}][cuisine][]" multiple class="w-full border rounded px-2 py-1 h-32">
          <option value="Végan">Végan</option>
          <option value="Végétarien">Végétarien</option>
          <option value="Sans gluten">Sans gluten</option>
        </select>
      </div>
    `;
  }

  addStep() {
    const container = this.querySelector("#steps-container");
    container.insertAdjacentHTML("beforeend", this.buildStepFields(this.stepIndex));
    this.stepIndex++;
  }
}

customElements.define("roadtrip-search-form", RoadtripSearchForm);