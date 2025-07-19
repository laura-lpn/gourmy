export class MyCarousel extends HTMLElement {
  constructor() {
    super();
    this.currentIndex = 0;
    this.isDragging = false;
    this.startX = 0;
    this.currentTranslate = 0;
    this.prevTranslate = 0;
  }

  connectedCallback() {
    this.title = this.getAttribute("title") || "";
    this.color = this.getAttribute("color") || "orange";
    this.visible = parseInt(this.getAttribute("visible")) || 4;

    if (!this.hasAttribute("data-rendered")) {
      this.originalChildren = Array.from(this.children);
      this.render();
      this.setAttribute("data-rendered", "true");
    }

    this.track = this.querySelector(".carousel-track");
    this.cards = this.track.querySelectorAll(".card");
    this.setupEvents();
    this.resetPosition();

    window.addEventListener("pageshow", (event) => {
      if (event.persisted) {
        this.resetPosition();
      }
    });

    window.addEventListener("resize", () => this.slide(0));
  }

  setupEvents() {
    this.querySelector("#next").addEventListener("click", () => this.slide(1));
    this.querySelector("#prev").addEventListener("click", () => this.slide(-1));

    this.track.addEventListener("mousedown", this.startDrag.bind(this));
    this.track.addEventListener("touchstart", this.startDrag.bind(this), { passive: true });
    window.addEventListener("mouseup", this.endDrag.bind(this));
    window.addEventListener("touchend", this.endDrag.bind(this));
    this.track.addEventListener("mousemove", this.onDrag.bind(this));
    this.track.addEventListener("touchmove", this.onDrag.bind(this), { passive: false });
  }

  startDrag(e) {
    this.isDragging = true;
    this.startX = this.getPositionX(e);
    this.track.classList.remove("transition-transform");
  }

  onDrag(e) {
    if (!this.isDragging) return;
    e.preventDefault();
    const currentX = this.getPositionX(e);
    const diff = currentX - this.startX;
    this.currentTranslate = this.prevTranslate + diff;
    this.track.style.transform = `translateX(${this.currentTranslate}px)`;
  }

  endDrag() {
    if (!this.isDragging) return;
    this.isDragging = false;
    this.track.classList.add("transition-transform", "duration-500", "ease-out");

    const movedBy = this.currentTranslate - this.prevTranslate;
    const threshold = this.cards[0].offsetWidth / 4;

    if (movedBy < -threshold) {
      this.slide(1);
    } else if (movedBy > threshold) {
      this.slide(-1);
    } else {
      this.slide(0);
    }
  }

  getPositionX(e) {
    return e.type.includes("touch") ? e.touches[0].clientX : e.clientX;
  }

  slide(direction) {
    const total = this.cards.length;
    this.currentIndex = (this.currentIndex + direction + total) % total;

    const cardWidth = this.cards[0].offsetWidth;
    const gap = parseFloat(getComputedStyle(this.track).gap) || 0;

    this.prevTranslate = -(this.currentIndex * (cardWidth + gap));
    this.track.style.transform = `translateX(${this.prevTranslate}px)`;
  }

  resetPosition() {
    this.currentIndex = 0;
    this.prevTranslate = 0;
    if (this.track) {
      this.track.style.transform = "translateX(0)";
    }
  }

  render() {
    const colorClass =
      this.color === "blue"
        ? "text-blue border-blue hover:bg-blue hover:text-white"
        : "text-orange border-orange hover:bg-orange hover:text-white";

    const barClass = this.color === "blue" ? "bg-blue" : "bg-orange";

    const lgWidthClass =
      this.visible === 5 ? "lg:w-1/5" :
      this.visible === 4 ? "lg:w-1/4" :
      "lg:w-1/3";

    const cards = (this.originalChildren || Array.from(this.children))
      .map(
        (child) => `
        <div class="card flex-shrink-0 w-full sm:w-1/2 ${lgWidthClass} px-2 flex">
          ${child.outerHTML}
        </div>
      `
      )
      .join("");

    this.innerHTML = `
      <div class="bg-${this.color === "blue" ? "blue" : "orange"}/5 rounded-2xl pl-4 sm:pl-8 lg:pl-20 py-6 sm:py-8 overflow-hidden">
        <div class="flex items-center justify-between mb-4 sm:mb-6 pl-2 pr-4 sm:pr-8 lg:pr-20">
          ${
            this.title
              ? `
            <h2 class="${
              this.color === "blue" ? "text-blue" : "text-orange"
            } text-lg sm:text-xl font-second font-medium flex items-center gap-4 sm:gap-6">
              <span class="block w-6 sm:w-8 h-0.5 rounded-md ${barClass}"></span>
              ${this.title}
            </h2>`
              : ""
          }
          <div class="flex gap-2">
            <button id="prev" class="w-8 sm:w-10 h-8 sm:h-10 rounded-full bg-transparent ${colorClass} p-1.5 sm:p-2 border-2 flex items-center justify-center text-base sm:text-lg">
              <i class="fas fa-arrow-left"></i>
            </button>
            <button id="next" class="w-8 sm:w-10 h-8 sm:h-10 rounded-full bg-transparent ${colorClass} p-1.5 sm:p-2 border-2 flex items-center justify-center text-base sm:text-lg">
              <i class="fas fa-arrow-right"></i>
            </button>
          </div>
        </div>

        <div class="overflow-hidden">
          <div class="carousel-track flex items-stretch gap-2 sm:gap-4 transition-transform duration-500 ease-out cursor-grab">
            ${cards}
          </div>
        </div>
      </div>
    `;
  }
}

customElements.define("my-carousel", MyCarousel);