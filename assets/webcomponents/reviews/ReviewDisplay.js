export function renderReview({ review, currentUserId, editable, onEditReview, onDeleteReview, allowResponse, onResponseEdit, onResponseDelete }) {
  const wrapper = document.createElement('div');

  // Bloc de l'avis
  const reviewCard = document.createElement('div');
  reviewCard.className = "bg-white rounded-2xl w-4/5 mx-auto shadow-main p-6 flex flex-col md:flex-row gap-6 items-start relative";
  reviewCard.setAttribute('data-id', review.id);

  reviewCard.innerHTML = `    
    ${review.image ? `
      <div class="w-48 aspect-square rounded-xl overflow-hidden">
        <img src="${review.image}" class="w-full h-full object-cover">
      </div>
    ` : `
      <div class="w-48 aspect-square bg-gray-100 rounded-xl flex items-center justify-center text-black">Aucune image</div>
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
        <p class="text-orange font-second text-sm text-right rating">${review.rating}/5</p>
      </div>
      <p class="text-sm mt-2 comment">${review.comment}</p>
    </div>
    ${editable && currentUserId === review.author.id ? `
      <div class="mt-3 flex gap-2 absolute top-2 right-6">
        <button data-action="edit-review" class="btn-icon text-blue hover:text-blue" title="Modifier" data-id="${review.id}">
          <i class="fa-solid fa-pen"></i>
        </button>
        <button data-action="delete-review" class="btn-icon text-red-600 hover:text-red-700" title="Supprimer" data-id="${review.id}">
          <i class="fa-solid fa-trash"></i>
        </button>
      </div>
    ` : ''}
  `;

  wrapper.appendChild(reviewCard);

  // Réponse : affichée en dessous, décalée
  const responseBlock = document.createElement('div');
  responseBlock.className = "w-4/5 mx-auto mt-2";

  if (review.response) {
    responseBlock.innerHTML = `
      <div class="response-container ml-6 mt-4 bg-blue/5 shadow-main rounded-xl">
        <div class="p-4 text-sm" data-id="${review.response.id}">
          <p class="italic mb-1">Réponse :</p>
          <p class="response-text mb-1">${review.response.comment}</p>
          <small class="text-blue">par ${review.response.author}</small>
          ${allowResponse ? `
            <div class="mt-2 flex gap-4">
              <button data-action="edit-response" title="Modifier" class="text-blue text-sm" data-id="${review.response.id}" data-comment="${review.response.comment}"><i class="fa-solid fa-pen"></i></button>
              <button data-action="delete-response" title="Supprimer" class="text-red-600 text-sm" data-id="${review.response.id}"><i class="fa-solid fa-trash"></i></button>
            </div>
          ` : ''}
        </div>
      </div>
    `;
  } else if (allowResponse) {
    responseBlock.innerHTML = `
      <form class="response-form flex flex-col gap-2 pl-8 mt-2 w-full" data-review-id="${review.id}">
        <textarea name="comment" placeholder="Votre réponse..." class="rounded border p-2" required></textarea>
        <button type="submit" class="btn self-start">Répondre</button>
      </form>
    `;
  }

  wrapper.appendChild(responseBlock);

  // Event bindings
  if (editable && currentUserId === review.author.id) {
    reviewCard.querySelector('[data-action="edit-review"]')?.addEventListener('click', () => onEditReview(review.id));
    reviewCard.querySelector('[data-action="delete-review"]')?.addEventListener('click', () => onDeleteReview(review.id));
  }

  if (allowResponse) {
    responseBlock.querySelector('.response-form')?.addEventListener('submit', e => {
      e.preventDefault();
      const comment = e.target.comment.value;
      const reviewId = e.target.dataset.reviewId;
      onResponseEdit(reviewId, comment, 'new');
    });

    responseBlock.querySelector('[data-action="edit-response"]')?.addEventListener('click', e => {
      const target = e.target;
      const id = target.dataset.id;
      const comment = target.dataset.comment || '';

      const container = target.closest('.response-container');
      container.innerHTML = `
        <form class="response-form-edit space-y-2 px-6 py-2" data-id="${id}">
          <textarea name="comment" required class="rounded border p-2 w-full bg-transparent">${comment}</textarea>
          <div class="flex gap-2">
            <button type="submit" class="btn">Mettre à jour</button>
            <button type="button" data-action="cancel-edit-response" class="btn-secondary bg-transparent">Annuler</button>
            <button type="button" data-action="delete-response" class="text-sm text-red-600" data-id="${id}"><i class="fa-solid fa-trash"></i></button>
          </div>
        </form>
      `;

      container.querySelector('.response-form-edit')?.addEventListener('submit', ev => {
        ev.preventDefault();
        const comment = ev.target.comment.value;
        onResponseEdit(id, comment, 'edit');
      });

      container.querySelector('[data-action="cancel-edit-response"]')?.addEventListener('click', () => {
        onResponseEdit(null, null, 'refresh');
      });

      container.querySelector('[data-action="delete-response"]')?.addEventListener('click', ev => {
        onResponseDelete(ev.target.dataset.id);
      });
    });

    responseBlock.querySelector('[data-action="delete-response"]')?.addEventListener('click', e => {
      onResponseDelete(e.target.dataset.id);
    });
  }

  return wrapper;
}