export class ImageUploader extends HTMLElement {
  constructor() {
    super();
    this.addImageField = this.addImageField.bind(this);
  }

  connectedCallback() {
    this.initStructure();

    this.collectionHolder = this.querySelector('[data-collection-holder]');
    this.addBtn = this.querySelector('.add-image-btn');

    this.initExistingImages();
    this.collectionHolder.dataset.index =
      this.collectionHolder.querySelectorAll('.image-item').length;

    this.addBtn.addEventListener('click', this.addImageField);

    this.ensureAddTileVisibility();
  }

  standardizeItemEl(el) {
    el.classList.remove('w-40', 'h-40');
    el.classList.add(
      'relative',
      'image-item',
      'w-full',
      'aspect-square',
      'bg-gray-100',
      'rounded-lg',
      'overflow-hidden',
      'cursor-pointer',
      'bg-cover',
      'bg-center'
    );
  }

  standardizeAddBtnEl(el) {
    el.className =
      'add-image-btn relative w-full aspect-square rounded-lg ' +
      'border-2 border-dashed border-orange/40 bg-orange/5 ' +
      'hover:bg-orange/10 transition flex items-center justify-center cursor-pointer';
    el.innerHTML = '<i class="fa-solid fa-plus text-orange text-2xl" aria-hidden="true"></i><span class="sr-only">Ajouter une image</span>';
  }

  initStructure() {
    if (!this.querySelector('[data-collection-holder]')) {
      const existing = this.querySelector('.existing-images');
      const holder = document.createElement('div');

      holder.className = 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4';
      holder.setAttribute('data-collection-holder', '');
      holder.setAttribute('data-prototype', this.dataset.prototype);

      if (existing) {
        existing.querySelectorAll('.image-item').forEach((img) => {
          this.standardizeItemEl(img);
          holder.appendChild(img);
        });
        existing.remove();
      }

      const addBtn = document.createElement('div');
      this.standardizeAddBtnEl(addBtn);
      holder.appendChild(addBtn);

      this.appendChild(holder);
    }
  }

  initExistingImages() {
    this.collectionHolder.querySelectorAll('.image-item').forEach((wrapper) => {
      this.standardizeItemEl(wrapper);

      const fileInput  = wrapper.querySelector('.image-input') || wrapper.querySelector('input[type="file"]');
      const deleteFlag = wrapper.querySelector('.delete-flag');
      const removeBtn  = wrapper.querySelector('.remove-image-btn');

      if (!fileInput) return;

      fileInput.classList.add('hidden', 'image-input');

      wrapper.addEventListener('click', (e) => {
        if (!e.target.closest('.remove-image-btn')) fileInput.click();
      });

      if (removeBtn) {
        removeBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          if (deleteFlag) deleteFlag.checked = true;
          wrapper.style.display = 'none';
          this.ensureAddTileVisibility();
        });
      }

      fileInput.addEventListener('change', (e) => {
        if (!e.target.files.length) return;
        const reader = new FileReader();
        reader.onload = (ev) => {
          wrapper.style.backgroundImage = `url('${ev.target.result}')`;
          if (removeBtn) removeBtn.classList.remove('hidden');
          this.ensureAddTileVisibility();
        };
        reader.readAsDataURL(e.target.files[0]);
      });
    });
  }

  addImageField() {
    const prototype = this.collectionHolder.dataset.prototype;
    const index     = parseInt(this.collectionHolder.dataset.index || '0', 10);
    const newForm   = prototype.replace(/__name__/g, index);
    this.collectionHolder.dataset.index = String(index + 1);

    const wrapper = document.createElement('div');
    this.standardizeItemEl(wrapper);
    wrapper.innerHTML = newForm;

    const fileInput  = wrapper.querySelector('input[type="file"]');
    const deleteFlag = wrapper.querySelector('.delete-flag');

    let removeBtn = wrapper.querySelector('.remove-image-btn');
    if (!removeBtn) {
      removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'absolute top-1 right-1 bg-blue text-white text-xs rounded-full px-2 py-1 remove-image-btn hidden';
      removeBtn.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i><span class="sr-only">Supprimer</span>';
      wrapper.appendChild(removeBtn);
    }

    if (!fileInput) {
      console.error("Erreur : input[type=file] manquant dans le prototype", newForm);
      return;
    }
    fileInput.classList.add('hidden', 'image-input');

    wrapper.addEventListener('click', (e) => {
      if (!e.target.closest('.remove-image-btn')) fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
      if (!e.target.files.length) return;
      const reader = new FileReader();
      reader.onload = (ev) => {
        wrapper.style.backgroundImage = `url('${ev.target.result}')`;
        removeBtn.classList.remove('hidden');
        this.ensureAddTileVisibility();
      };
      reader.readAsDataURL(e.target.files[0]);
    });

    removeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (deleteFlag) deleteFlag.checked = true;
      wrapper.style.display = 'none';
      this.ensureAddTileVisibility();
    });

    const addTile = this.querySelector('.add-image-btn');
    this.collectionHolder.insertBefore(wrapper, addTile);
    fileInput.click();
  }

  ensureAddTileVisibility() {
    const hasEmptySlot = Array.from(this.collectionHolder.querySelectorAll('.image-item'))
      .some((item) => {
        if (item.style.display === 'none') return false;
        const input = item.querySelector('.image-input');
        return input && !input.value && !item.style.backgroundImage;
      });

    if (hasEmptySlot) {
      this.addBtn.classList.add('hidden');
    } else {
      this.addBtn.classList.remove('hidden');
    }
  }
}

customElements.define('image-uploader', ImageUploader);