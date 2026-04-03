(function () {
  'use strict';

  var items = [
    { category: 'Aptitude Test', code: 'I0000', description: 'English' },
    { category: 'Aptitude Test', code: 'I0001', description: 'Mathematics' },
    { category: 'Aptitude Test', code: 'I0002', description: 'Science' }
  ];

  var editingIdx = -1;
  var deletingIdx = -1;

  var page = document.getElementById('examListPage');
  if (!page) return;

  var searchInput = document.getElementById('examListSearchInput');
  var tableBody = document.getElementById('examListTableBody');
  var newBtn = document.getElementById('examListNewBtn');

  var modal = document.getElementById('examListModal');
  var modalTitle = document.getElementById('examListModalTitle');
  var categoryInput = document.getElementById('examListCategoryInput');
  var codeInput = document.getElementById('examListCodeInput');
  var descInput = document.getElementById('examListDescriptionInput');
  var cancelBtn = document.getElementById('examListCancelBtn');
  var saveBtn = document.getElementById('examListSaveBtn');

  var deleteModal = document.getElementById('examListDeleteModal');
  var deleteCancelBtn = document.getElementById('examListDeleteCancelBtn');
  var deleteConfirmBtn = document.getElementById('examListDeleteConfirmBtn');

  var successModal = document.getElementById('examListSuccessModal');
  var successMsg = document.getElementById('examListSuccessMsg');
  var okBtn = document.getElementById('examListOkBtn');

  function closeMenus() {
    page.querySelectorAll('.apst-dropdown.open').forEach(function (menu) {
      menu.classList.remove('open');
      menu.classList.remove('drop-up');
    });
  }

  function toggleMenu(menuId, trigger) {
    var menu = document.getElementById(menuId);
    if (!menu || !trigger) return;

    var isOpen = menu.classList.contains('open');
    closeMenus();
    if (isOpen) return;

    menu.classList.add('open');
  }

  function renderRows() {
    var q = (searchInput.value || '').toLowerCase().trim();
    var rows = items.filter(function (item) {
      return !q ||
        item.category.toLowerCase().indexOf(q) !== -1 ||
        item.code.toLowerCase().indexOf(q) !== -1 ||
        item.description.toLowerCase().indexOf(q) !== -1;
    });

    tableBody.innerHTML = rows.map(function (item) {
      var idx = items.indexOf(item);
      return '' +
        '<tr>' +
          '<td>' +
            '<div class="apst-action-btn" data-menu-toggle="' + idx + '"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="elMenu' + idx + '">' +
              '<button data-edit="' + idx + '">Edit</button>' +
              '<button class="apst-del-btn" data-delete="' + idx + '">Delete</button>' +
            '</div>' +
          '</td>' +
          '<td>' + item.category + '</td>' +
          '<td>' + item.code + '</td>' +
          '<td>' + item.description + '</td>' +
        '</tr>';
    }).join('');
  }

  function openModal(isEdit, idx) {
    editingIdx = isEdit ? idx : -1;
    if (isEdit) {
      modalTitle.textContent = 'EDIT ITEM';
      categoryInput.value = items[idx].category;
      codeInput.value = items[idx].code;
      descInput.value = items[idx].description;
    } else {
      modalTitle.textContent = 'NEW ITEM';
      categoryInput.value = 'Aptitude Test';
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

  function saveItem() {
    var category = (categoryInput.value || '').trim();
    var code = (codeInput.value || '').trim();
    var description = (descInput.value || '').trim();
    if (!category || !code || !description) {
      alert('Category, item code, and description are required.');
      return;
    }

    if (editingIdx > -1) {
      items[editingIdx] = { category: category, code: code, description: description };
    } else {
      items.push({ category: category, code: code, description: description });
    }

    closeModal();
    renderRows();
    showSuccess('Exam item saved successfully.');
  }

  function deleteItem() {
    if (deletingIdx > -1) {
      items.splice(deletingIdx, 1);
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
      toggleMenu('elMenu' + idx, toggle);
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
  saveBtn.addEventListener('click', saveItem);
  deleteCancelBtn.addEventListener('click', closeDeleteModal);
  deleteConfirmBtn.addEventListener('click', deleteItem);
  okBtn.addEventListener('click', closeSuccess);

  renderRows();
})();
