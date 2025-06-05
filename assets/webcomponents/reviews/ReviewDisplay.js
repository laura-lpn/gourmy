export function renderReview({ review, currentUserId, editable, onEdit, onDelete, allowResponse, onResponseEdit, onResponseDelete }) {
  const div = document.createElement('div');
  div.setAttribute('data-id', review.id);

  console.log('Rendering review:', review);

  div.className = "bg-white rounded-2xl w-4/5 mx-auto shadow-main p-6 flex flex-col md:flex-row gap-6 items-start relative";

  div.innerHTML = `    
    ${review.image ? `
      <img src="${review.image}" alt="Image de l'avis" class="w-44 h-44 object-cover rounded-xl shadow-sm">
    ` : `
      <div class="w-44 h-44 bg-gray-100 rounded-xl flex items-center justify-center text-black">Aucune image</div>
    `}

      <div class="flex justify-between items-start w-[inherit] flex-col">
        <h4 class="text-blue font-medium font-second text-lg">${review.title}</h4>
        <div class="flex items-center justify-between mt-2 w-full">
          <div class="flex items-center gap-2">
            ${review.author.avatarName ? `
              <img src="/uploads/users/avatars/${review.author.avatarName}" alt="Avatar de ${review.author.username}" class="w-8 h-8 rounded-full">
            ` : `
              <img src="/images/avatar-placeholder.png" alt="Avatar par défaut" class="w-8 h-8 rounded-full">
            `}
            <p class="text-sm mb-2">${review.author.username}</p>
          </div>
          <p class="text-orange font-second text-sm text-right">${review.rating}/5</p>
        </div>
        <p class="text-sm mt-2">${review.comment}</p>
      </div>


      ${editable && currentUserId === review.author.id ? `
        <div class="mt-3 flex gap-2 absolute top-2 right-6">
          <button class="btn-icon edit-btn text-blue hover:text-blue" title="Modifier" data-id="${review.id}">
            <i class="fa-solid fa-pen"></i>
          </button>
          <button class="btn-icon delete-btn text-red-500 hover:text-red-700" title="Supprimer" data-id="${review.id}">
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      ` : ''}

    ${review.response ? `
      <div class="response-container mt-4 w-full" data-review-id="${review.id}">
        <div class="bg-blue/5 p-3 rounded-md text-sm" data-id="${review.response.id}">
          <p class="italic mb-1">Réponse :</p>
          <p class="response-text mb-1">${review.response.comment}</p>
          <small class="text-blue">par ${review.response.author}</small>

          ${allowResponse ? `
            <div class="mt-2 flex gap-2">
              <button class="text-blue text-sm edit-response" data-id="${review.response.id}" data-comment="${review.response.comment}">Modifier</button>
              <button class="text-red-600 text-sm delete-response" data-id="${review.response.id}">Supprimer</button>
            </div>
          ` : ''}
        </div>
      ` : allowResponse ? `
        <form class="response-form flex flex-col gap-2 mt-2" data-review-id="${review.id}">
          <textarea name="comment" placeholder="Votre réponse..." class="rounded border p-2" required></textarea>
          <button type="submit" class="btn">Répondre</button>
        </form>
      </div>
    ` : ''}
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
        <form class="response-form-edit space-y-2" data-id="${id}">
          <textarea name="comment" required class="rounded border p-2 w-full">${comment}</textarea>
          <div class="flex gap-2">
            <button type="submit" class="btn">Mettre à jour</button>
            <button type="button" class="btn-secondary">Annuler</button>
            <button type="button" class="delete-response text-sm text-red-500" data-id="${id}">Supprimer</button>
          </div>
        </form>
      `;

      container.querySelector('.response-form-edit').addEventListener('submit', ev => {
        ev.preventDefault();
        const comment = ev.target.comment.value;
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