export class UserFavorites extends HTMLElement {
  constructor() {
    super();
    this.restaurants = [];
    this.roadtrips = [];
  }

  connectedCallback() {
    this.render();
    this.fetchFavorites();
  }

  render() {
    this.innerHTML = `
      <div class="space-y-6">
        <div class="flex justify-center mb-6">
          <div id="favorites-toggle" class="inline-flex rounded-md border border-blue overflow-hidden">
            <button data-type="restaurants"
                    class="px-4 py-2 text-sm bg-blue text-white active">
              Restaurants
            </button>
            <button data-type="roadtrips"
                    class="px-4 py-2 text-sm bg-white hover:bg-blue/5">
              Roadtrips
            </button>
          </div>
        </div>

        <div id="favorites-restaurants" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"></div>
        <div id="favorites-roadtrips" class="hidden grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"></div>
      </div>
    `;

    this.toggleEvents();
  }

  toggleEvents() {
    const toggle = this.querySelector('#favorites-toggle');
    const btns = toggle.querySelectorAll('button');
    const restaurantsContainer = this.querySelector('#favorites-restaurants');
    const roadtripsContainer = this.querySelector('#favorites-roadtrips');

    btns.forEach(btn => {
      btn.addEventListener('click', () => {
        btns.forEach(b => {
          b.classList.remove('bg-blue', 'text-white', 'active');
          b.classList.add('bg-white', 'hover:bg-blue/5');
        });

        btn.classList.remove('bg-white', 'hover:bg-blue/5');
        btn.classList.add('bg-blue', 'text-white', 'active');

        if (btn.dataset.type === 'restaurants') {
          restaurantsContainer.classList.remove('hidden');
          roadtripsContainer.classList.add('hidden');
        } else {
          restaurantsContainer.classList.add('hidden');
          roadtripsContainer.classList.remove('hidden');
        }
      });
    });
  }

  fetchFavorites() {
    // Restaurants favoris
    fetch('/api/user/restaurants/favorites')
      .then(res => res.json())
      .then(data => {
        this.restaurants = data;
        this.renderRestaurants();
      });

    // Roadtrips favoris
    fetch('/api/user/roadtrips/favorites')
      .then(res => res.json())
      .then(data => {
        this.roadtrips = data;
        this.renderRoadtrips();
      });
  }

  renderRestaurants() {
    const container = this.querySelector('#favorites-restaurants');
    if (!this.restaurants.length) {
      container.innerHTML = `<p class="text-center col-span-full">Aucun restaurant favori</p>`;
      return;
    }

    container.innerHTML = this.restaurants.map(r => `
      <div class="bg-white rounded-2xl shadow-main p-4 flex flex-col gap-2 items-center w-full max-w-xs">

        <a href="/restaurants/${r.slug}" class="block w-full">
          ${r.banner
            ? `<img src="${r.banner}" alt="Photo de ${r.name}" class="rounded-xl h-40 w-full object-cover">`
            : `<div class="h-40 w-full bg-gray-100 rounded-xl flex items-center justify-center text-gray-400">Pas d'image</div>`
          }

          <div class="mt-2">
            <h3 class="text-lg font-medium font-second text-blue mb-2">${r.name}</h3>
            <p class="text-sm line-clamp-2">${r.description ?? ''}</p>
            <div class="flex gap-1 items-center text-orange mt-2">
              ${this.renderStars(r.averageRating)}
              <span class="text-sm text-black ml-2">${r.reviewsCount ?? 0} avis</span>
            </div>
          </div>
        </a>
      </div>
    `).join('');
  }

  renderRoadtrips() {
    const container = this.querySelector('#favorites-roadtrips');
    if (!this.roadtrips.length) {
      container.innerHTML = `<p class="text-center col-span-full">Aucun roadtrip favori</p>`;
      return;
    }

    container.innerHTML = this.roadtrips.map(rt => {
      const stepCount = rt.steps.length;
      const cities = [...new Set(rt.steps.map(s => s.town).filter(Boolean))];
      const images = steps
        .flatMap(s => s.restaurants || [])
        .filter(r => r?.banner)
        .slice(0, 3)
        .map(r => r.banner);

      const imageHtml = (() => {
        if (images.length === 1) {
          return `<img src="${images[0]}" alt="Preview" class="object-cover h-24 w-full rounded-md">`;
        } else if (images.length === 2) {
          return `
            <div class="flex gap-2">
              ${images.map(img => `<img src="${img}" alt="Preview" class="object-cover h-24 w-1/2 rounded-md">`).join('')}
            </div>`;
        } else if (images.length === 3) {
          return `
            <div class="flex gap-2 h-24">
              <img src="${images[0]}" alt="Preview" class="object-cover w-1/2 h-full rounded-md">
              <div class="flex flex-col gap-2 w-1/2">
                <img src="${images[1]}" alt="Preview" class="object-cover h-1/2 w-full rounded-md">
                <img src="${images[2]}" alt="Preview" class="object-cover h-1/2 w-full rounded-md">
              </div>
            </div>`;
        }
        return '';
      })();

      return `
        <div class="bg-orange/10 rounded-xl p-6 hover:shadow-main group space-y-2">
        

          <a href="/roadtrips/${rt.id}" class="block w-full">
            ${imageHtml ? `<div class="mb-4">${imageHtml}</div>` : ''}
            <h3 class="text-lg font-second font-medium text-orange mb-2">${rt.title}</h3>
            <div class="text-sm text-gray-700 space-y-1">
              <p><i class="fa-solid fa-signs-post text-orange mr-1"></i>${stepCount} étapes</p>
              <p><i class="fa-solid fa-earth-americas text-orange mr-1"></i>Villes</p>
              <div class="flex flex-wrap gap-2">
                ${cities.map(ville => `<span class="bg-blue text-white rounded-full py-1 px-3 text-xs">${ville}</span>`).join('')}
              </div>
            </div>
          </a>
        </div>
      `;
    }).join('');

  }

  renderStars(avg) {
    if (!avg) avg = 0;
    return Array.from({ length: 5 }).map((_, i) => {
      const index = i + 1;
      if (avg >= index) {
        return `<i class="fas fa-star text-sm"></i>`;
      } else if (avg >= index - 0.5) {
        return `<i class="fas fa-star-half-alt text-sm"></i>`;
      } else {
        return `<i class="far fa-star text-sm"></i>`;
      }
    }).join('');
  }
}

customElements.define('user-favorites', UserFavorites);