export function renderReview({
  review,
  currentUserId,
  editable,
  onEditReview,
  onDeleteReview,
  allowResponse,
  onResponseEdit,
  onResponseDelete
}) {
  const wrapper = document.createElement('div');

  // ---- Carte de l'avis
  const reviewCard = document.createElement('div');
  reviewCard.className = [
    // conteneur
    "bg-white rounded-2xl w-full lg:w-4/5 mx-auto shadow-main",
    // paddings responsive
    "p-4 sm:p-5 md:p-6",
    // layout responsive
    "flex flex-col md:flex-row items-start",
    // espacements
    "gap-4 sm:gap-5 md:gap-6",
    "relative"
  ].join(' ');
  reviewCard.setAttribute('data-id', review.id);

  reviewCard.innerHTML = `
    ${review.image ? `
      <div class="w-full max-w-[12rem] sm:max-w-[14rem] md:w-48 aspect-square rounded-xl overflow-hidden self-center md:self-auto">
        <img src="${review.image}" class="w-full h-full object-cover" alt="">
      </div>
    ` : `
      <div class="w-full max-w-[12rem] sm:max-w-[14rem] md:w-48 aspect-square bg-gray-100 rounded-xl flex items-center justify-center text-black self-center md:self-auto">
        Aucune image
      </div>
    `}

    <div class="flex-1 w-full flex flex-col">
      <h4 class="text-blue font-medium font-second text-base sm:text-lg leading-snug break-words">${review.title}</h4>

      <div class="flex items-center justify-between mt-2 w-full gap-3">
        <div class="flex items-center gap-2 min-w-0">
          ${review.author.avatarName ? `
            <img src="/uploads/users/avatars/${review.author.avatarName}" alt="Avatar de ${review.author.username}" class="w-8 h-8 rounded-full flex-shrink-0">
          ` : `
            <img src="/images/avatar-placeholder.png" alt="Avatar par défaut" class="w-8 h-8 rounded-full flex-shrink-0">
          `}
          <p class="text-sm mb-0 truncate">${review.author.username}</p>
        </div>
        <p class="text-orange font-second text-xs sm:text-sm text-right rating whitespace-nowrap">${review.rating}/5</p>
      </div>

      <p class="text-sm sm:text-base mt-2 comment leading-relaxed break-words">${review.comment}</p>
    </div>

    ${editable && currentUserId === review.author.id ? `
      <div class="mt-3 flex gap-2 absolute top-2 right-3 sm:right-4">
        <button data-action="edit-review" class="btn-icon text-blue hover:text-blue/80" title="Modifier" data-id="${review.id}">
          <i class="fa-solid fa-pen"></i>
        </button>
        <button data-action="delete-review" class="btn-icon text-red-600 hover:text-red-700" title="Supprimer" data-id="${review.id}">
          <i class="fa-solid fa-trash"></i>
        </button>
      </div>
    ` : ''}
  `;

  wrapper.appendChild(reviewCard);

  // ---- Bloc réponse (indenté)
  const responseBlock = document.createElement('div');
  responseBlock.className = "w-full sm:w-11/12 md:w-4/5 mx-auto mt-2";

  if (review.response) {
    responseBlock.innerHTML = `
      <div class="response-container sm:ml-6 mt-3 bg-blue/5 shadow-main rounded-xl">
        <div class="p-4 text-sm sm:text-base" data-id="${review.response.id}">
          <p class="italic mb-1">Réponse :</p>
          <p class="response-text mb-1 break-words">${review.response.comment}</p>
          <small class="text-blue">par ${review.response.author}</small>
          ${allowResponse ? `
            <div class="mt-2 flex gap-4">
              <button data-action="edit-response" title="Modifier" class="text-blue text-sm" data-id="${review.response.id}" data-comment="${review.response.comment}">
                <i class="fa-solid fa-pen"></i>
              </button>
              <button data-action="delete-response" title="Supprimer" class="text-red-600 text-sm" data-id="${review.response.id}">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          ` : ''}
        </div>
      </div>
    `;
  } else if (allowResponse) {
    responseBlock.innerHTML = `
      <form class="response-form flex flex-col gap-2 sm:gap-3 sm:ml-6 mt-2 w-full" data-review-id="${review.id}">
        <textarea name="comment" placeholder="Votre réponse..." class="rounded border p-2 min-h-24" required></textarea>
        <button type="submit" class="btn self-start">Répondre</button>
      </form>
    `;
  }

  wrapper.appendChild(responseBlock);

  // ---- Events
  if (editable && currentUserId === review.author.id) {
    reviewCard.querySelector('[data-action="edit-review"]')?.addEventListener('click', () => onEditReview(review.id));
    reviewCard.querySelector('[data-action="delete-review"]')?.addEventListener('click', () => onDeleteReview(review.id));
  }

  if (allowResponse) {
    // Nouvelle réponse
    responseBlock.querySelector('.response-form')?.addEventListener('submit', e => {
      e.preventDefault();
      const comment = e.target.comment.value;
      const reviewId = e.target.dataset.reviewId;
      onResponseEdit(reviewId, comment, 'new');
    });

    // Edit / Delete existants (robuste aux clics sur l’icône <i>)
    responseBlock.addEventListener('click', (e) => {
      const editBtn = e.target.closest?.('[data-action="edit-response"]');
      const delBtn  = e.target.closest?.('[data-action="delete-response"]');

      if (editBtn) {
        const id = editBtn.dataset.id;
        const comment = editBtn.dataset.comment || '';
        const container = editBtn.closest('.response-container');
        container.innerHTML = `
          <form class="response-form-edit space-y-2 px-4 sm:px-6 py-2" data-id="${id}">
            <textarea name="comment" required class="rounded border p-2 w-full bg-transparent min-h-24">${comment}</textarea>
            <div class="flex flex-wrap gap-2">
              <button type="submit" class="btn">Mettre à jour</button>
              <button type="button" data-action="cancel-edit-response" class="btn-secondary bg-transparent">Annuler</button>
              <button type="button" data-action="delete-response" class="text-sm text-red-600" data-id="${id}">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </form>
        `;

        const form = container.querySelector('.response-form-edit');
        form?.addEventListener('submit', ev => {
          ev.preventDefault();
          const newComment = ev.target.comment.value;
          onResponseEdit(id, newComment, 'edit');
        });
        container.querySelector('[data-action="cancel-edit-response"]')?.addEventListener('click', () => {
          onResponseEdit(null, null, 'refresh');
        });
        container.querySelector('[data-action="delete-response"]')?.addEventListener('click', ev => {
          const deleteId = ev.currentTarget.dataset.id;
          onResponseDelete(deleteId);
        });

        return;
      }

      if (delBtn) {
        const id = delBtn.dataset.id;
        onResponseDelete(id);
      }
    });
  }

  return wrapper;
}