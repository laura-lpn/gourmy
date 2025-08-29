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
        <!-- Toggle -->
        <div class="flex justify-center">
          <div id="favorites-toggle"
               class="inline-flex rounded-md border border-blue overflow-hidden"
               role="tablist" aria-label="Favoris">
            <button id="fav-tab-restaurants"
                    data-type="restaurants"
                    role="tab"
                    aria-controls="panel-restaurants"
                    aria-selected="true"
                    class="px-4 py-2 text-sm bg-blue text-white focus:outline-none focus:ring-2 focus:ring-blue">
              Restaurants
            </button>
            <button id="fav-tab-roadtrips"
                    data-type="roadtrips"
                    role="tab"
                    aria-controls="panel-roadtrips"
                    aria-selected="false"
                    class="px-4 py-2 text-sm bg-white hover:bg-blue/5 focus:outline-none focus:ring-2 focus:ring-blue">
              Roadtrips
            </button>
          </div>
        </div>

        <!-- Panels -->
        <div id="favorites-restaurants"
             role="tabpanel"
             aria-labelledby="fav-tab-restaurants"
             class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6"
             id="panel-restaurants">
        </div>

        <div id="favorites-roadtrips"
             role="tabpanel"
             aria-labelledby="fav-tab-roadtrips"
             class="hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6"
             id="panel-roadtrips">
        </div>
      </div>
    `;

    this.toggleEvents();
  }

  toggleEvents() {
    const toggle = this.querySelector('#favorites-toggle');
    const btns = toggle.querySelectorAll('button');
    const restaurantsContainer = this.querySelector('#favorites-restaurants');
    const roadtripsContainer = this.querySelector('#favorites-roadtrips');

    const activate = (type) => {
      btns.forEach(b => {
        const isActive = b.dataset.type === type;
        b.setAttribute('aria-selected', String(isActive));
        b.classList.toggle('bg-blue', isActive);
        b.classList.toggle('text-white', isActive);
        b.classList.toggle('bg-white', !isActive);
        b.classList.toggle('hover:bg-blue/5', !isActive);
      });

      if (type === 'restaurants') {
        restaurantsContainer.classList.remove('hidden');
        roadtripsContainer.classList.add('hidden');
      } else {
        restaurantsContainer.classList.add('hidden');
        roadtripsContainer.classList.remove('hidden');
      }
    };

    btns.forEach(btn => {
      btn.addEventListener('click', () => activate(btn.dataset.type));
      btn.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
          e.preventDefault();
          const currentIndex = Array.from(btns).indexOf(btn);
          const nextIndex = e.key === 'ArrowRight'
            ? (currentIndex + 1) % btns.length
            : (currentIndex - 1 + btns.length) % btns.length;
          btns[nextIndex].focus();
          activate(btns[nextIndex].dataset.type);
        }
      });
    });

    // état initial
    activate('restaurants');
  }

  fetchFavorites() {
    // Restaurants favoris
    fetch('/api/user/restaurants/favorites')
      .then(res => res.json())
      .then(data => {
        this.restaurants = data || [];
        this.renderRestaurants();
      });

    // Roadtrips favoris
    fetch('/api/user/roadtrips/favorites')
      .then(res => res.json())
      .then(data => {
        this.roadtrips = data || [];
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
      <div class="bg-white rounded-2xl shadow-main p-4 flex flex-col gap-2 w-full">
        <a href="/restaurants/${r.slug}" class="block w-full">
          ${r.banner
            ? `<div class="w-full aspect-[4/3] overflow-hidden rounded-xl">
                 <img src="${r.banner}" alt="Photo de ${this.escape(r.name)}" class="w-full h-full object-cover">
               </div>`
            : `<div class="w-full aspect-[4/3] bg-gray-100 rounded-xl flex items-center justify-center text-gray-400">
                 Pas d'image
               </div>`
          }
          <div class="mt-3">
            <h3 class="text-base sm:text-lg font-medium font-second text-blue mb-1 line-clamp-2">${this.escape(r.name)}</h3>
            ${r.description ? `<p class="text-sm line-clamp-2">${this.escape(r.description)}</p>` : ''}
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
      const steps = Array.isArray(rt.steps) ? rt.steps : [];
      const stepCount = steps.length;
      const cities = [...new Set(steps.map(s => s?.town).filter(Boolean))];

      // FIX: utiliser rt.steps (et pas "steps" global absent)
      const images = steps
        .flatMap(s => s?.restaurants || [])
        .filter(r => r?.banner)
        .slice(0, 3)
        .map(r => r.banner);

      const imageHtml = (() => {
        if (images.length === 1) {
          return `
            <div class="w-full aspect-[4/3] overflow-hidden rounded-xl">
              <img src="${images[0]}" alt="Preview" class="object-cover w-full h-full">
            </div>`;
        } else if (images.length === 2) {
          return `
            <div class="grid grid-cols-2 gap-2">
              ${images.map(img => `
                <div class="aspect-[4/3] overflow-hidden rounded-xl">
                  <img src="${img}" alt="Preview" class="object-cover w-full h-full">
                </div>`).join('')}
            </div>`;
        } else if (images.length === 3) {
          return `
            <div class="grid grid-cols-2 gap-2">
              <div class="row-span-2 aspect-[4/3] overflow-hidden rounded-xl">
                <img src="${images[0]}" alt="Preview" class="object-cover w-full h-full">
              </div>
              <div class="aspect-[4/3] overflow-hidden rounded-xl">
                <img src="${images[1]}" alt="Preview" class="object-cover w-full h-full">
              </div>
              <div class="aspect-[4/3] overflow-hidden rounded-xl">
                <img src="${images[2]}" alt="Preview" class="object-cover w-full h-full">
              </div>
            </div>`;
        }
        return '';
      })();

      return `
        <div class="bg-orange/10 rounded-xl p-4 sm:p-5 hover:shadow-main group space-y-3 w-full">
          <a href="/roadtrips/${rt.id}" class="block w-full">
            ${imageHtml ? `<div class="mb-3">${imageHtml}</div>` : ''}
            <h3 class="text-base sm:text-lg font-second font-medium text-orange mb-1 line-clamp-2">${this.escape(rt.title)}</h3>
            <div class="text-sm text-gray-700 space-y-1">
              <p class="flex items-center"><i class="fa-solid fa-signs-post text-orange mr-2"></i>${stepCount} étapes</p>
              ${cities.length ? `
                <div class="flex flex-wrap gap-2 mt-1">
                  ${cities.slice(0, 10).map(ville => `
                    <span class="bg-blue text-white rounded-full py-1 px-3 text-[11px] sm:text-xs">${this.escape(ville)}</span>
                  `).join('')}
                  ${cities.length > 10 ? `<span class="text-xs text-gray-500">+${cities.length - 10}</span>` : ''}
                </div>` : ''
              }
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

  escape(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
}

customElements.define('user-favorites', UserFavorites);