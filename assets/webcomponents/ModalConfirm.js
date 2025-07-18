export class ModalConfirm extends HTMLElement {
    connectedCallback() {
     this.innerHTML = `
        <dialog class="rounded-lg py-6 px-8 w-full bg-white max-w-xl backdrop:bg-black/50 fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50 relative">
          <button id="close-icon" class="absolute top-3 right-3 text-gray-500 hover:text-black hidden">
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
          <form method="dialog" class="flex flex-col items-start gap-4 w-full">
            <div id="modal-content" class="w-full"></div>
            <menu id="modal-actions" class="flex gap-6 ml-auto mt-4">
              <button id="close-btn" class="btn-secondary" value="cancel">Annuler</button>
              <button id="confirm-btn" value="confirm" class="btn">Confirmer</button>
            </menu>
          </form>
        </dialog>
      `;

    this.dialog = this.querySelector('dialog');
    this.confirmBtn = this.querySelector('#confirm-btn');
    this.closeBtn = this.querySelector('#close-btn');
    this.closeIcon = this.querySelector('#close-icon');
    this.content = this.querySelector('#modal-content');
    this.actions = this.querySelector('#modal-actions');

    this.closeBtn.addEventListener('click', () => this.close());
    this.closeIcon.addEventListener('click', () => this.close());
  }

  /**
   * Affiche la modal avec un message simple et un bouton Confirmer
   * @param {string} message
   * @param {Function} onConfirm
   */
  show(message, onConfirm) {
    this.setContent(`<strong class="font-medium text-lg">${message}</strong>`);
    this.actions.style.display = 'flex';
    this.confirmBtn.style.display = 'inline-block'; 
    this.closeIcon.style.display = 'none';
    const handler = () => {
      onConfirm();
      this.dialog.close();
      this.confirmBtn.removeEventListener('click', handler);
    };
    this.confirmBtn.addEventListener('click', handler);
    this.dialog.showModal();
  }

  /**
   * Affiche la modal avec un contenu HTML personnalisé
   * @param {string} html HTML à injecter dans #modal-content
   * @param {Object} options { showActions: boolean, onConfirm?: function }
    * @param {boolean} options.showActions - Affiche les boutons Confirmer/Fermer en bas
    * @param {function|null} options.onConfirm - Fonction exécutée au clic sur Confirmer
   * @param {boolean} options.showCloseIcon - Affiche une croix en haut à droite (par défaut false)
   * 
   */
  showWithContent(html, options = {}) {
    const {
      showActions = false,
      onConfirm = null,
      showCloseIcon = false
    } = options;

    this.setContent(html);
    this.actions.style.display = showActions ? 'flex' : 'none';
    this.confirmBtn.style.display = showActions ? 'inline-block' : 'none';
    this.closeIcon.style.display = showCloseIcon ? 'block' : 'none';

    if (onConfirm) {
      const handler = () => {
        onConfirm();
        this.dialog.close();
        this.confirmBtn.removeEventListener('click', handler);
      };
      this.confirmBtn.addEventListener('click', handler);
    }

    this.dialog.showModal();
  }

  setContent(html) {
    this.content.innerHTML = html;
  }

  close() {
    this.dialog.close();
  }
}
customElements.define('modal-confirm', ModalConfirm);