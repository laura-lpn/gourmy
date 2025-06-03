export class ModalConfirm extends HTMLElement {
  connectedCallback() {
    this.innerHTML = `
      <dialog>
        <form method="dialog">
          <p><slot name="message">Confirmer ?</slot></p>
          <menu>
            <button value="cancel">Annuler</button>
            <button id="confirm-btn" value="confirm">OK</button>
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