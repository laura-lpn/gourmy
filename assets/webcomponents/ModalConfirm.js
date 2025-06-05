export class ModalConfirm extends HTMLElement {
    connectedCallback() {
    this.innerHTML = `
      <dialog class="rounded-lg py-6 px-8 w-full bg-white max-w-xl backdrop:bg-black/50 fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50">
        <form method="dialog" class="flex flex-col items-start gap-4 w-full">
          <div id="modal-content" class="w-full"></div>
          <menu id="modal-actions" class="flex gap-6 ml-auto mt-4">
            <button class="text-red-600 border border-red-600 rounded-md px-6 py-2" value="cancel">Annuler</button>
            <button id="confirm-btn" value="confirm" class="text-white bg-blue border border-blue rounded-md px-6 py-2">Confirmer</button>
          </menu>
        </form>
      </dialog>
    `;

    this.dialog = this.querySelector('dialog');
    this.confirmBtn = this.querySelector('#confirm-btn');
    this.content = this.querySelector('#modal-content');
    this.actions = this.querySelector('#modal-actions');

    // 🔧 Ajout du comportement du bouton Annuler
    this.querySelector('button[value="cancel"]')?.addEventListener('click', () => {
      this.close();
    });
  }

  /**
   * Affiche la modal avec un message simple et un bouton Confirmer
   * @param {string} message
   * @param {Function} onConfirm
   */
  show(message, onConfirm) {
    this.setContent(`<strong class="font-medium text-lg">${message}</strong>`);
    this.actions.style.display = 'flex';
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
   */
  showWithContent(html, options = {}) {
    const { showActions = false, onConfirm = null } = options;
    this.setContent(html);
    this.actions.style.display = showActions ? 'flex' : 'none';

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

  /**
   * Injecte du HTML dans la modale
   */
  setContent(html) {
    this.content.innerHTML = html;
  }

  close() {
    this.dialog.close();
  }
}

customElements.define('modal-confirm', ModalConfirm);