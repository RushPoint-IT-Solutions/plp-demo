(function () {
  'use strict';

  var categories = [
    { code: 'C0000', description: 'Aptitude Test' }
  ];

  var editingIdx = -1;
  var deletingIdx = -1;

  var page = document.getElementById('examCategoryPage');
  if (!page) return;

  var searchInput = document.getElementById('examCategorySearchInput');
  var tableBody = document.getElementById('examCategoryTableBody');
  var newBtn = document.getElementById('examCategoryNewBtn');

  var modal = document.getElementById('examCategoryModal');
  var modalTitle = document.getElementById('examCategoryModalTitle');
  var codeInput = document.getElementById('examCategoryCodeInput');
  var descInput = document.getElementById('examCategoryDescriptionInput');
  var cancelBtn = document.getElementById('examCategoryCancelBtn');
  var saveBtn = document.getElementById('examCategorySaveBtn');

  var deleteModal = document.getElementById('examCategoryDeleteModal');
  var deleteCancelBtn = document.getElementById('examCategoryDeleteCancelBtn');
  var deleteConfirmBtn = document.getElementById('examCategoryDeleteConfirmBtn');

  var successModal = document.getElementById('examCategorySuccessModal');
  var successMsg = document.getElementById('examCategorySuccessMsg');
  var okBtn = document.getElementById('examCategoryOkBtn');

  function closeMenus() {
    page.querySelectorAll('.apst-dropdown.open').forEach(function (menu) {
      menu.classList.remove('open');
      menu.classList.remove('drop-up');
      menu.style.top = '';
      menu.style.left = '';
      menu.style.right = '';
      menu.style.bottom = '';
    });
  }

  function toggleMenu(menuId, trigger) {
    var menu = document.getElementById(menuId);
    if (!menu || !trigger) return;

    var isOpen = menu.classList.contains('open');
    closeMenus();
    if (isOpen) return;

    var rect = trigger.getBoundingClientRect();
    var estimatedWidth = 126;
    var estimatedHeight = 92;
    var left = rect.right + 8;
    var top = rect.top;

    if (left + estimatedWidth > window.innerWidth - 8) {
      left = Math.max(8, window.innerWidth - estimatedWidth - 8);
    }

    if ((window.innerHeight - rect.bottom) < estimatedHeight + 8) {
      menu.classList.add('drop-up');
      top = Math.max(8, rect.bottom - estimatedHeight);
    } else {
      menu.style.bottom = 'auto';
    }

    menu.style.left = left + 'px';
    menu.style.right = 'auto';
    menu.style.top = top + 'px';
    menu.style.bottom = 'auto';

    menu.classList.add('open');
  }

  function renderRows() {
    var q = (searchInput.value || '').toLowerCase().trim();
    var rows = categories.filter(function (item) {
      return !q || item.code.toLowerCase().indexOf(q) !== -1 || item.description.toLowerCase().indexOf(q) !== -1;
    });

    tableBody.innerHTML = rows.map(function (item) {
      var idx = categories.indexOf(item);
      return '' +
        '<tr>' +
          '<td>' +
            '<div class="apst-action-btn" data-menu-toggle="' + idx + '"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="ecMenu' + idx + '">' +
              '<button data-edit="' + idx + '">Edit</button>' +
              '<button class="apst-del-btn" data-delete="' + idx + '">Delete</button>' +
            '</div>' +
          '</td>' +
          '<td>' + item.code + '</td>' +
          '<td>' + item.description + '</td>' +
        '</tr>';
    }).join('');
  }

  function openModal(isEdit, idx) {
    editingIdx = isEdit ? idx : -1;
    if (isEdit) {
      modalTitle.textContent = 'EDIT CATEGORY';
      codeInput.value = categories[idx].code;
      descInput.value = categories[idx].description;
    } else {
      modalTitle.textContent = 'NEW CATEGORY';
      codeInput.value = '';
      descInput.value = '';
    }
    modal.style.display = 'flex';
  }

  function closeModal() {
    modal.style.display = 'none';
  }

  function openDeleteModal(idx) {
    deletingIdx = idx;
    deleteModal.style.display = 'flex';
  }

  function closeDeleteModal() {
    deleteModal.style.display = 'none';
  }

  function showSuccess(message) {
    successMsg.textContent = message;
    successModal.style.display = 'flex';
  }

  function closeSuccess() {
    successModal.style.display = 'none';
  }

  function saveCategory() {
    var code = (codeInput.value || '').trim();
    var description = (descInput.value || '').trim();
    if (!code || !description) {
      alert('Category code and description are required.');
      return;
    }

    if (editingIdx > -1) {
      categories[editingIdx] = { code: code, description: description };
    } else {
      categories.push({ code: code, description: description });
    }

    closeModal();
    renderRows();
    showSuccess('Category saved successfully.');
  }

  function deleteCategory() {
    if (deletingIdx > -1) {
      categories.splice(deletingIdx, 1);
    }
    deletingIdx = -1;
    closeDeleteModal();
    renderRows();
  }

  page.addEventListener('click', function (event) {
    var toggle = event.target.closest('[data-menu-toggle]');
    if (toggle) {
      event.preventDefault();
      event.stopPropagation();
      var idx = toggle.getAttribute('data-menu-toggle');
      toggleMenu('ecMenu' + idx, toggle);
      return;
    }

    var editBtn = event.target.closest('[data-edit]');
    if (editBtn) {
      event.preventDefault();
      closeMenus();
      openModal(true, parseInt(editBtn.getAttribute('data-edit'), 10));
      return;
    }

    var deleteBtn = event.target.closest('[data-delete]');
    if (deleteBtn) {
      event.preventDefault();
      closeMenus();
      openDeleteModal(parseInt(deleteBtn.getAttribute('data-delete'), 10));
      return;
    }

    if (event.target === modal) closeModal();
    if (event.target === deleteModal) closeDeleteModal();
    if (event.target === successModal) closeSuccess();
  });

  document.addEventListener('click', function (event) {
    if (!event.target.closest('.apst-action-btn') && !event.target.closest('.apst-dropdown')) {
      closeMenus();
    }
  });

  window.addEventListener('scroll', closeMenus, true);

  searchInput.addEventListener('input', renderRows);
  newBtn.addEventListener('click', function () { openModal(false, -1); });
  cancelBtn.addEventListener('click', closeModal);
  saveBtn.addEventListener('click', saveCategory);
  deleteCancelBtn.addEventListener('click', closeDeleteModal);
  deleteConfirmBtn.addEventListener('click', deleteCategory);
  okBtn.addEventListener('click', closeSuccess);

  renderRows();
})();
