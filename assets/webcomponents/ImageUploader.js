export class ImageUploader extends HTMLElement {
  constructor() {
    super();
  }

  connectedCallback() {
    this.initStructure();

    this.collectionHolder = this.querySelector('[data-collection-holder]');
    this.addBtn = this.querySelector('.add-image-btn');

    this.initExistingImages();
    this.collectionHolder.dataset.index =
      this.collectionHolder.querySelectorAll('.image-item').length;

    this.addBtn.addEventListener('click', () => this.addImageField());
    this.ensureEmptySlot();
  }

  initStructure() {
    if (!this.querySelector('[data-collection-holder]')) {
      const existingImages = this.querySelector('.existing-images');
      const holder = document.createElement('div');

      holder.className = 'grid grid-cols-2 sm:grid-cols-3 gap-4';
      holder.setAttribute('data-collection-holder', '');
      holder.setAttribute('data-prototype', this.dataset.prototype);

      if (existingImages) {
        existingImages.querySelectorAll('.image-item').forEach((img) => {
          holder.appendChild(img);
        });
        existingImages.remove();
      }

      const addBtn = document.createElement('div');
      addBtn.className =
        'add-image-btn relative w-40 h-40 bg-orange/10 rounded-lg flex items-center justify-center cursor-pointer hover:bg-orange/20';
      addBtn.innerHTML =
        '<i class="fa-solid fa-plus text-orange text-2xl"></i>';
      holder.appendChild(addBtn);

      this.appendChild(holder);
    }
  }

  initExistingImages() {
    this.collectionHolder.querySelectorAll('.image-item').forEach((wrapper) => {
      const fileInput = wrapper.querySelector('.image-input');
      const deleteFlag = wrapper.querySelector('.delete-flag');
      const removeBtn = wrapper.querySelector('.remove-image-btn');

      fileInput.classList.add('hidden');

      wrapper.addEventListener('click', (e) => {
        if (!e.target.closest('.remove-image-btn')) {
          fileInput.click();
        }
      });

      removeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (deleteFlag) {
          deleteFlag.checked = true;
        }
        wrapper.style.display = 'none';
        this.ensureEmptySlot();
      });

      fileInput.addEventListener('change', (e) => {
        if (!e.target.files.length) return;
        const reader = new FileReader();
        reader.onload = (ev) => {
          wrapper.style.backgroundImage = `url('${ev.target.result}')`;
          wrapper.style.backgroundSize = 'cover';
          wrapper.style.backgroundPosition = 'center';
        };
        reader.readAsDataURL(e.target.files[0]);
      });
    });
  }

  addImageField() {
    const prototype = this.collectionHolder.dataset.prototype;
    const index = this.collectionHolder.dataset.index;
    const newForm = prototype.replace(/__name__/g, index);
    this.collectionHolder.dataset.index++;

    const wrapper = document.createElement('div');
    wrapper.classList.add(
      'relative',
      'image-item',
      'w-40',
      'h-40',
      'bg-gray-100',
      'rounded-lg',
      'overflow-hidden',
      'cursor-pointer'
    );
    wrapper.innerHTML = newForm;

    const fileInput = wrapper.querySelector('input[type="file"]');
    const deleteFlag = wrapper.querySelector('.delete-flag');

    // Ajout du bouton supprimer
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className =
      'absolute top-1 right-1 bg-blue text-white text-xs rounded-full px-2 py-1 remove-image-btn hidden';
    removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
    wrapper.appendChild(removeBtn);

    if (!fileInput) {
      console.error("Erreur : pas d'input file trouvé dans le prototype", newForm);
      return;
    }

    fileInput.classList.add('hidden', 'image-input');

    wrapper.addEventListener('click', (e) => {
      if (!e.target.closest('.remove-image-btn')) {
        fileInput.click();
      }
    });

    fileInput.addEventListener('change', (e) => {
      if (!e.target.files.length) return;
      const reader = new FileReader();
      reader.onload = (ev) => {
        wrapper.style.backgroundImage = `url('${ev.target.result}')`;
        wrapper.style.backgroundSize = 'cover';
        wrapper.style.backgroundPosition = 'center';
        removeBtn.classList.remove('hidden');
        this.ensureEmptySlot();
      };
      reader.readAsDataURL(e.target.files[0]);
    });

    removeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (deleteFlag) {
        deleteFlag.checked = true;
      }
      wrapper.style.display = 'none';
      this.ensureEmptySlot();
    });

    this.collectionHolder.insertBefore(wrapper, this.addBtn);
    fileInput.click();
  }

  ensureEmptySlot() {
    const emptyExists = Array.from(
      this.collectionHolder.querySelectorAll('.image-input')
    ).some((input) => !input.value);

    if (!emptyExists) {
      this.addBtn.classList.remove('hidden');
    }
  }
}

customElements.define('image-uploader', ImageUploader);