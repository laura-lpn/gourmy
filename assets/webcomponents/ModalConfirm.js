export class ModalConfirm extends HTMLElement {
  connectedCallback() {
    this.innerHTML = `
      <dialog class="rounded-lg py-6 px-8">
        <form method="dialog" class="flex flex-col items-center justify-between min-h-32">
          <strong class="font-medium text-lg"><slot name="message">Confirmer ?</slot></strong>
          <menu class="flex gap-6 mt-4 ml-auto">
            <button class="text-red-600 border border-red-600 rounded-md px-6 py-2" value="cancel">Annuler</button>
            <button id="confirm-btn" value="confirm" class="text-white bg-blue border border-blue rounded-md px-6 py-2">Confirmer</button>
          </menu>
        </form>
      </dialog>
    `;
    this.dialog = this.querySelector('dialog');
    this.confirmBtn = this.querySelector('#confirm-btn');
  }

  show(message, onConfirm) {
    this.querySelector('[name=message]').textContent = message;
    const handler = () => {
      onConfirm();
      this.dialog.close();
      this.confirmBtn.removeEventListener('click', handler);
    };
    this.confirmBtn.addEventListener('click', handler);
    this.dialog.showModal();
  }
}
customElements.define('modal-confirm', ModalConfirm);