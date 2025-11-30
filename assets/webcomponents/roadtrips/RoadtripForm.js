import Sortable from "sortablejs";
import "../ModalConfirm.js";
import "../SearchBar.js";

export class RoadtripForm extends HTMLElement {
  connectedCallback() {
    this.stepsContainer = this.querySelector("#steps-container");
    this.modal = document.querySelector("modal-confirm");
    this.restaurantResults = null;

    this.querySelector("#add-step")?.addEventListener("click", () =>
      this.addStep()
    );

    if (this.stepsContainer) {
      Sortable.create(this.stepsContainer, {
        handle: ".step-card",
        animation: 150,
      });
    }

    this.stepIndex = 0;
  }

  addStep() {
    const index = this.stepIndex++;
    const step = document.createElement("div");
    step.className = "bg-white p-4 border rounded-xl step-card space-y-4";
    step.dataset.index = index;
    step.innerHTML = `
    <div class="flex justify-between items-start">
      <h4 class="font-medium text-blue">Étape ${index + 1}</h4>
      <button class="text-red-600 text-sm remove-step"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="space-y-4">
      <input type="text" name="steps[${index}][town]" placeholder="Ville" class="w-full input-town" required>
      <div class="restaurant-selection"></div>
      <div class="selected-restaurants grid grid-cols-1 md:grid-cols-2 gap-2"></div>
      <div class="types-container flex gap-2 flex-wrap"></div>
      <input type="hidden" name="steps[${index}][cuisine]">
    </div>
  `;

    step
      .querySelector(".remove-step")
      .addEventListener("click", () => step.remove());

    this.stepsContainer.appendChild(step);

    const input = step.querySelector(".input-town");
    input.addEventListener("change", () =>
      this.loadFavorites(input.value, step)
    );
  }

  selectRestaurant(r, container, typesContainer, hiddenInput) {
    if (container.querySelector(`[data-id='${r.id}']`)) return;

    const index = container.closest(".step-card")?.dataset.index;
    const div = document.createElement("div");
    div.dataset.id = r.id;
    div.className = "p-2 bg-blue/10 rounded flex justify-between items-center";
    div.innerHTML = `
    <span>${r.name}</span>
    <button type="button" class="text-red-600"><i class="fa-solid fa-xmark"></i></button>
    <input type="hidden" name="steps[${index}][restaurantIds][]" value="${r.id}">
  `;
    div.querySelector("button").addEventListener("click", () => {
      div.remove();
      this.updateCuisine(container, typesContainer, hiddenInput);
    });
    container.appendChild(div);
    this.updateCuisine(container, typesContainer, hiddenInput);
  }

  async loadFavorites(town, container) {
    const resultContainer = container.querySelector(".restaurant-selection");
    const selectedContainer = container.querySelector(".selected-restaurants");
    const typesContainer = container.querySelector(".types-container");
    const hiddenInput = container.querySelector("input[type='hidden']");

    selectedContainer.innerHTML = "";
    typesContainer.innerHTML = "";
    hiddenInput.value = "";

    const res = await fetch("/api/user/restaurants/favorites");
    const data = await res.json();
    const filtered = data.filter(
      (r) => r.city.toLowerCase() === town.toLowerCase()
    );

    const cards = filtered.map((r) => {
      const div = document.createElement("div");
      div.className =
        "border rounded-xl p-2 flex gap-4 cursor-pointer hover:bg-orange/10";
      div.innerHTML = `
        <img src="${
          r.banner || "/images/default.jpg"
        }" class="w-16 h-16 object-cover rounded-md">
        <div class="flex flex-col">
          <strong>${r.name}</strong>
          <small class="text-sm text-gray-500">${r.city}</small>
        </div>
      `;
      div.addEventListener("click", () =>
        this.selectRestaurant(r, selectedContainer, typesContainer, hiddenInput)
      );
      return div;
    });

    resultContainer.innerHTML = `
      <h5 class="text-sm font-medium mb-2">Favoris dans ${town}</h5>
      <div class="space-y-2">${cards.map((c) => c.outerHTML).join("")}</div>
      <button class="btn-secondary mt-2" id="search-more">Autre ?</button>
    `;

    resultContainer
      .querySelector("#search-more")
      .addEventListener("click", () => {
        this.modal.showWithContent(
          `
        <h3 class="text-lg font-medium mb-4">Rechercher un restaurant</h3>
        <search-bar data-url="/api/restaurants/by-town?q=&town=${encodeURIComponent(
          town
        )}"></search-bar>
        <div id="restaurant-results" class="mt-4 space-y-4"></div>
      `,
          {
            showCloseIcon: true,
          }
        );

        this.restaurantResults = container.querySelector(
          ".selected-restaurants"
        );
      });
  }

  updateCuisine(container, typesContainer, hiddenInput) {
    const ids = [
      ...container.querySelectorAll("input[name*='restaurantIds']"),
    ].map((input) => parseInt(input.value));
    if (ids.length === 0) return;

    fetch(`/api/restaurants/common-types?ids=${ids.join(",")}`)
      .then((res) => res.json())
      .then((types) => {
        typesContainer.innerHTML = "";
        types.forEach((t) => {
          const btn = document.createElement("button");
          btn.className =
            "px-3 py-1 rounded-full text-sm bg-blue/10 text-blue hover:bg-blue/20";
          btn.textContent = t;
          btn.addEventListener("click", () => {
            hiddenInput.value = t;
            [...typesContainer.children].forEach((b) =>
              b.classList.remove("bg-blue", "text-white")
            );
            btn.classList.add("bg-blue", "text-white");
          });
          typesContainer.appendChild(btn);
        });
      });
  }
}

customElements.define("roadtrip-form", RoadtripForm);
