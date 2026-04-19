function setPasswordVisibility(passwordInputId, eyeIconId, eyeSlashIconId, toggleButtonId, isVisible) {
    var passwordInput = document.getElementById(passwordInputId);
    var eyeIcon = document.getElementById(eyeIconId);
    var eyeSlashIcon = document.getElementById(eyeSlashIconId);
    var toggleButton = document.getElementById(toggleButtonId);

    if (!passwordInput || !eyeIcon || !eyeSlashIcon || !toggleButton) {
        return;
    }

    passwordInput.type = isVisible ? 'text' : 'password';
    eyeIcon.style.display = isVisible ? 'none' : 'inline';
    eyeSlashIcon.style.display = isVisible ? 'inline' : 'none';
    toggleButton.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
    toggleButton.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
}

function togglePassword(passwordInputId, eyeIconId, eyeSlashIconId, toggleButtonId) {
    var passwordInput = document.getElementById(passwordInputId);

    if (!passwordInput) {
        return;
    }

    setPasswordVisibility(passwordInputId, eyeIconId, eyeSlashIconId, toggleButtonId, passwordInput.type === 'password');
}

function bindPasswordToggle(options) {
    var passwordToggleBtn = document.getElementById(options.toggleButtonId);
    var passwordInput = document.getElementById(options.passwordInputId);

    if (passwordToggleBtn) {
        passwordToggleBtn.addEventListener('click', function (event) {
            event.preventDefault();
            togglePassword(options.passwordInputId, options.eyeIconId, options.eyeSlashIconId, options.toggleButtonId);
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('focus', function () {
            if (passwordInput.type === 'text') {
                setPasswordVisibility(options.passwordInputId, options.eyeIconId, options.eyeSlashIconId, options.toggleButtonId, false);
            }
        });

        passwordInput.addEventListener('mousedown', function () {
            if (passwordInput.type === 'text') {
                setPasswordVisibility(options.passwordInputId, options.eyeIconId, options.eyeSlashIconId, options.toggleButtonId, false);
            }
        });

        passwordInput.addEventListener('blur', function () {
            setPasswordVisibility(options.passwordInputId, options.eyeIconId, options.eyeSlashIconId, options.toggleButtonId, false);
        });
    }
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', function () {
    var passwordToggleBtn = document.getElementById('passwordToggleBtn');
    var trigger = document.getElementById('parentCreateAccountTrigger');
    var modal = document.getElementById('parentCreateAccountModal');
    var closeBtn = document.getElementById('parentCreateAccountClose');
    var createForm = document.getElementById('parentCreateAccountForm');
    var studentSearchInput = document.getElementById('childStudentNoSearch');
    var studentValueInput = document.getElementById('childStudentNoValue');
    var studentDropdown = document.getElementById('childStudentNoDropdown');
    var childBirthdateInput = document.getElementById('childBirthdate');
    var studentLookupTimer = null;
    var studentLookupRequestId = 0;
    var studentOptions = [];
    var activeStudentOptionIndex = -1;
    var childBirthdatePicker = null;
    var shouldOpenOnLoad = modal && modal.getAttribute('data-open-on-load') === '1';

    bindPasswordToggle({
        toggleButtonId: 'passwordToggleBtn',
        passwordInputId: 'password',
        eyeIconId: 'eye-icon',
        eyeSlashIconId: 'eye-slash-icon'
    });

    bindPasswordToggle({
        toggleButtonId: 'parentPasswordToggleBtn',
        passwordInputId: 'parentPassword',
        eyeIconId: 'parentPasswordEyeIcon',
        eyeSlashIconId: 'parentPasswordEyeSlashIcon'
    });

    bindPasswordToggle({
        toggleButtonId: 'parentPasswordConfirmationToggleBtn',
        passwordInputId: 'parentPasswordConfirmation',
        eyeIconId: 'parentPasswordConfirmationEyeIcon',
        eyeSlashIconId: 'parentPasswordConfirmationEyeSlashIcon'
    });

    if (!trigger || !modal) {
        return;
    }

    function setSearchExpanded(expanded) {
        if (!studentSearchInput) {
            return;
        }

        studentSearchInput.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    }

    function closeStudentDropdown() {
        if (!studentDropdown) {
            return;
        }

        studentDropdown.classList.remove('is-open');
        setSearchExpanded(false);
        activeStudentOptionIndex = -1;
    }

    function markActiveStudentOption() {
        if (!studentDropdown) {
            return;
        }

        var optionButtons = studentDropdown.querySelectorAll('.smrg-search-option');
        optionButtons.forEach(function (button, index) {
            var shouldBeActive = index === activeStudentOptionIndex;
            button.classList.toggle('is-active', shouldBeActive);
            button.setAttribute('aria-selected', shouldBeActive ? 'true' : 'false');

            if (shouldBeActive && typeof button.scrollIntoView === 'function') {
                button.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    function renderStudentOptions() {
        if (!studentDropdown || !studentSearchInput) {
            return;
        }

        if (!studentOptions.length) {
            studentDropdown.innerHTML = '<div class="smrg-search-empty">No matching students found.</div>';
            studentDropdown.classList.add('is-open');
            setSearchExpanded(true);
            activeStudentOptionIndex = -1;
            return;
        }

        var selectedStudentNo = (studentValueInput && studentValueInput.value) ? studentValueInput.value.trim() : '';

        studentDropdown.innerHTML = studentOptions.map(function (option, index) {
            var selectedClass = selectedStudentNo !== '' && option.student_no === selectedStudentNo ? ' is-selected' : '';

            return ''
                + '<button type="button" class="smrg-search-option login-parent-student-option' + selectedClass + '" data-student-index="' + index + '" role="option" aria-selected="false">'
                + '<span class="login-parent-student-option-main">' + escapeHtml(option.student_no) + '</span>'
                + '<span class="login-parent-student-option-sub">' + escapeHtml(option.name) + '</span>'
                + '</button>';
        }).join('');

        studentDropdown.classList.add('is-open');
        setSearchExpanded(true);
        activeStudentOptionIndex = studentOptions.length ? 0 : -1;
        markActiveStudentOption();
    }

    function applySelectedStudent(option) {
        if (!option || !studentSearchInput || !studentValueInput) {
            return;
        }

        studentValueInput.value = option.student_no;
        studentSearchInput.value = option.label;
        studentSearchInput.setAttribute('data-selected-student-no', option.student_no);
        studentSearchInput.setAttribute('data-selected-student-label', option.label);
        closeStudentDropdown();
    }

    function clearSelectedStudentIfModified() {
        if (!studentSearchInput || !studentValueInput) {
            return;
        }

        var selectedNo = (studentSearchInput.getAttribute('data-selected-student-no') || '').trim();
        var selectedLabel = (studentSearchInput.getAttribute('data-selected-student-label') || '').trim();
        var currentValue = studentSearchInput.value.trim();

        if (selectedNo !== '' && currentValue !== selectedLabel && currentValue !== selectedNo) {
            studentValueInput.value = '';
            studentSearchInput.removeAttribute('data-selected-student-no');
            studentSearchInput.removeAttribute('data-selected-student-label');
        }
    }

    function fetchStudentOptions(queryText) {
        if (!studentSearchInput || !studentDropdown) {
            return;
        }

        var lookupUrl = studentSearchInput.getAttribute('data-student-lookup-url') || '';
        if (!lookupUrl) {
            closeStudentDropdown();
            return;
        }

        var requestId = ++studentLookupRequestId;

        fetch(lookupUrl + '?query=' + encodeURIComponent(queryText), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Failed to fetch student list.');
                }

                return response.json();
            })
            .then(function (payload) {
                if (requestId !== studentLookupRequestId) {
                    return;
                }

                var students = Array.isArray(payload.students) ? payload.students : [];
                studentOptions = students.map(function (item) {
                    var studentNo = String(item.student_no || '').trim();
                    var studentName = String(item.name || '').trim();

                    return {
                        student_no: studentNo,
                        name: studentName,
                        label: String(item.label || (studentNo + ' - ' + studentName)).trim()
                    };
                });

                renderStudentOptions();
            })
            .catch(function () {
                if (requestId !== studentLookupRequestId) {
                    return;
                }

                studentOptions = [];
                renderStudentOptions();
            });
    }

    function queueStudentLookup(queryText) {
        if (studentLookupTimer) {
            clearTimeout(studentLookupTimer);
        }

        studentLookupTimer = setTimeout(function () {
            fetchStudentOptions(queryText);
        }, 180);
    }

    function moveActiveStudentOption(step) {
        if (!studentOptions.length) {
            return;
        }

        if (activeStudentOptionIndex < 0) {
            activeStudentOptionIndex = 0;
        } else {
            activeStudentOptionIndex += step;
        }

        if (activeStudentOptionIndex < 0) {
            activeStudentOptionIndex = studentOptions.length - 1;
        }

        if (activeStudentOptionIndex >= studentOptions.length) {
            activeStudentOptionIndex = 0;
        }

        markActiveStudentOption();
    }

    function initParentBirthdatePicker() {
        if (!childBirthdateInput || !window.flatpickr || childBirthdatePicker) {
            return childBirthdatePicker;
        }

        childBirthdatePicker = window.flatpickr(childBirthdateInput, {
            altInput: true,
            altInputClass: 'req-modal-input an-date-display',
            appendTo: modal,
            allowInput: true,
            clickOpens: true,
            position: 'below',
            disableMobile: true,
            dateFormat: 'Y-m-d',
            altFormat: 'M j, Y',
            monthSelectorType: 'dropdown',
            nextArrow: '<span aria-hidden="true">›</span>',
            prevArrow: '<span aria-hidden="true">‹</span>',
            onReady: function (selectedDates, dateStr, instance) {
                if (instance && instance.calendarContainer) {
                    instance.calendarContainer.classList.add('an-flatpickr-calendar');
                }
            },
            onOpen: function (selectedDates, dateStr, instance) {
                if (instance && instance.calendarContainer) {
                    instance.calendarContainer.classList.add('an-flatpickr-calendar');
                }
            }
        });

        return childBirthdatePicker;
    }

    function closeParentBirthdatePicker() {
        if (childBirthdatePicker && typeof childBirthdatePicker.close === 'function') {
            childBirthdatePicker.close();
        }
    }

    function enableControls() {
        var controls = modal.querySelectorAll('input, select, textarea, button');
        controls.forEach(function (control) {
            control.disabled = false;
            if ('readOnly' in control) {
                control.readOnly = false;
            }
        });
    }

    function openModal() {
        modal.hidden = false;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('parent-create-modal-open');
        enableControls();

        var firstField = modal.querySelector('input, select, textarea');
        if (firstField) {
            firstField.focus();
        }
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        modal.hidden = true;
        document.body.classList.remove('parent-create-modal-open');
        closeStudentDropdown();
        closeParentBirthdatePicker();
    }

    initParentBirthdatePicker();
    closeModal();

    trigger.addEventListener('click', function (event) {
        event.preventDefault();
        openModal();
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', function (event) {
            event.preventDefault();
            closeModal();
        });
    }

    if (studentSearchInput && studentDropdown) {
        studentSearchInput.addEventListener('input', function () {
            clearSelectedStudentIfModified();

            var queryText = studentSearchInput.value.trim();
            if (queryText === '') {
                studentOptions = [];
                closeStudentDropdown();
                return;
            }

            queueStudentLookup(queryText);
        });

        studentSearchInput.addEventListener('focus', function () {
            var queryText = studentSearchInput.value.trim();
            if (queryText !== '') {
                queueStudentLookup(queryText);
            }
        });

        studentSearchInput.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown') {
                event.preventDefault();

                if (!studentDropdown.classList.contains('is-open') && studentSearchInput.value.trim() !== '') {
                    queueStudentLookup(studentSearchInput.value.trim());
                    return;
                }

                moveActiveStudentOption(1);
                return;
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                moveActiveStudentOption(-1);
                return;
            }

            if (event.key === 'Enter') {
                if (studentDropdown.classList.contains('is-open')
                    && activeStudentOptionIndex >= 0
                    && studentOptions[activeStudentOptionIndex]) {
                    event.preventDefault();
                    applySelectedStudent(studentOptions[activeStudentOptionIndex]);
                }
                return;
            }

            if (event.key === 'Escape') {
                closeStudentDropdown();
            }
        });

        studentDropdown.addEventListener('click', function (event) {
            var optionButton = event.target.closest('.smrg-search-option');
            if (!optionButton) {
                return;
            }

            var index = parseInt(optionButton.getAttribute('data-student-index'), 10);
            if (isNaN(index) || !studentOptions[index]) {
                return;
            }

            applySelectedStudent(studentOptions[index]);
        });
    }

    if (createForm) {
        createForm.addEventListener('submit', function () {
            if (studentValueInput && studentSearchInput && studentValueInput.value.trim() === '') {
                studentValueInput.value = studentSearchInput.value.trim();
            }

            var submitButton = createForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.setAttribute('aria-disabled', 'true');
            }
        });
    }

    if (shouldOpenOnLoad) {
        openModal();
    }

    document.addEventListener('click', function (event) {
        if (!studentSearchInput || !studentDropdown) {
            return;
        }

        if (!event.target.closest('.login-parent-student-search-wrap')) {
            closeStudentDropdown();
        }
    });
});
