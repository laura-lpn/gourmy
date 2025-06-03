export function renderReview({ review, currentUserId, editable, onEdit, onDelete, allowResponse, onResponseEdit, onResponseDelete }) {
  const div = document.createElement('div');
  div.classList.add('border');
  div.setAttribute('data-id', review.id);

  div.innerHTML = `
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <h4>${review.title}</h4>
    ${review.image ? (`
      <img src="${review.image}" alt="Image de l'avis" style="max-width: 100%; height: auto;">
    `) : ''}
    <p>${review.comment}</p>
    <p><strong>Note:</strong> ${review.rating}/5</p>
    <p><em>par ${review.author.username}</em></p>
    ${editable && currentUserId === review.author.id ? `
      <button class="btn-icon edit-btn" data-id="${review.id}"><i class="fa-solid fa-pen"></i></button>
      <button class="btn-icon delete-btn" data-id="${review.id}"><i class="fa-solid fa-trash"></i></button>
    ` : ''}
    <div class="response-container" data-review-id="${review.id}">
      ${review.response ? `
        <div class="response" data-id="${review.response.id}">
          <p><em>Réponse :</em> <span class="response-text">${review.response.comment}</span></p>
          <small>par ${review.response.author}</small>
          ${allowResponse ? `
            <button class="edit-response" data-id="${review.response.id}" data-comment="${review.response.comment}">Modifier</button>
            <button class="delete-response" data-id="${review.response.id}">Supprimer</button>
          ` : ''}
        </div>
      ` : allowResponse ? `
        <form class="response-form" data-review-id="${review.id}">
          <textarea name="comment" placeholder="Votre réponse..." required></textarea>
          <button type="submit">Répondre</button>
        </form>
      ` : ''}
    </div>
  `;

  if (editable && currentUserId === review.author.id) {
    div.querySelector('.edit-btn')?.addEventListener('click', () => onEdit(review.id));
    div.querySelector('.delete-btn')?.addEventListener('click', () => onDelete(review.id));
  }

  if (allowResponse) {
    div.querySelector('.response-form')?.addEventListener('submit', e => {
      e.preventDefault();
      const comment = e.target.comment.value;
      const reviewId = e.target.dataset.reviewId;
      onResponseEdit(reviewId, comment, 'new');
    });

    div.querySelector('.edit-response')?.addEventListener('click', e => {
      const target = e.target;
      const id = target.dataset.id;
      const comment = target.dataset.comment || '';

      const container = target.closest('.response');
      container.innerHTML = `
        <form class="response-form-edit" data-id="${id}">
          <textarea name="comment" required>${comment}</textarea> 
          <button type="submit">Mettre à jour</button>
          <button type="button" class="cancel-edit">Annuler</button>
          <button type="button" class="delete-response" data-id="${id}">Supprimer</button>
        </form>
      `;

      container.querySelector('.response-form-edit').addEventListener('submit', ev => {
        ev.preventDefault();
        const comment = ev.target.comment.value
        onResponseEdit(id, comment, 'edit');
      });

      container.querySelector('.cancel-edit').addEventListener('click', () => {
        onResponseEdit(null, null, 'refresh');
      });

      container.querySelector('.delete-response').addEventListener('click', ev => {
        onResponseDelete(ev.target.dataset.id);
      });
    });

    div.querySelector('.delete-response')?.addEventListener('click', e => {
      onResponseDelete(e.target.dataset.id);
    });
  }

  return div;
}