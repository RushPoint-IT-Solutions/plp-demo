document.addEventListener('DOMContentLoaded', function () {
    var printButton = document.getElementById('cor-registrar-print');
    var printedByValue = document.getElementById('cor-printed-by-value');
    var timePrintedValue = document.getElementById('cor-time-printed');
    var datePrintedValue = document.getElementById('cor-date-printed');

    function pad(value) {
        return value < 10 ? '0' + value : String(value);
    }

    function formatDate(date) {
        return pad(date.getMonth() + 1) + '/' + pad(date.getDate()) + '/' + date.getFullYear();
    }

    function formatTime(date) {
        var hours = date.getHours();
        var meridian = hours >= 12 ? 'pm' : 'am';
        var displayHour = hours % 12;

        if (displayHour === 0) {
            displayHour = 12;
        }

        return displayHour + ':' + pad(date.getMinutes()) + ':' + pad(date.getSeconds()) + meridian;
    }

    function makeEditable() {
        if (!printedByValue) {
            return;
        }

        printedByValue.setAttribute('contenteditable', 'true');
        printedByValue.setAttribute('spellcheck', 'false');

        printedByValue.addEventListener('blur', function () {
            var currentValue = printedByValue.textContent.trim();
            if (currentValue === '') {
                var defaultValue = printedByValue.getAttribute('data-default') || 'Registrar User';
                printedByValue.textContent = defaultValue;
            }
        });

        printedByValue.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                printedByValue.blur();
            }
        });
    }

    function updatePrintedTimestamp() {
        var now = new Date();

        if (timePrintedValue) {
            timePrintedValue.textContent = formatTime(now);
        }

        if (datePrintedValue) {
            datePrintedValue.textContent = formatDate(now);
        }
    }

    makeEditable();
    updatePrintedTimestamp();

    window.addEventListener('beforeprint', function () {
        updatePrintedTimestamp();
    });

    if (window.matchMedia) {
        var mediaQueryList = window.matchMedia('print');
        if (mediaQueryList && mediaQueryList.addListener) {
            mediaQueryList.addListener(function (event) {
                var isPrintMode = event && typeof event.matches === 'boolean'
                    ? event.matches
                    : mediaQueryList.matches;

                if (isPrintMode) {
                    updatePrintedTimestamp();
                }
            });
        }
    }

    if (printButton) {
        printButton.addEventListener('click', function () {
            updatePrintedTimestamp();
            window.print();
        });
    }

    var studentSearch = document.querySelector('[data-cor-student-search]');
    var studentIdInput = document.getElementById('cor-student-id');
    var studentSearchInput = document.getElementById('cor-student-search-input');
    var studentResults = document.getElementById('cor-student-results');
    var searchRequest = null;
    var activeIndex = -1;

    function getStudentOptions() {
        if (!studentResults) {
            return [];
        }

        return Array.prototype.slice.call(studentResults.querySelectorAll('.cor-student-option'));
    }

    function setStudentSearchOpen(isOpen) {
        if (!studentSearch || !studentSearchInput) {
            return;
        }

        studentSearch.classList.toggle('is-open', isOpen);
        studentSearchInput.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    function setActiveStudentOption(index) {
        var options = getStudentOptions();

        options.forEach(function (option) {
            option.classList.remove('is-active');
            option.setAttribute('aria-selected', 'false');
        });

        if (!options.length) {
            activeIndex = -1;
            return;
        }

        if (index < 0) {
            index = options.length - 1;
        }

        if (index >= options.length) {
            index = 0;
        }

        activeIndex = index;
        options[activeIndex].classList.add('is-active');
        options[activeIndex].setAttribute('aria-selected', 'true');
        options[activeIndex].scrollIntoView({ block: 'nearest' });
    }

    function chooseStudentOption(option) {
        if (!option || !studentIdInput || !studentSearchInput) {
            return;
        }

        studentIdInput.value = option.getAttribute('data-student-id') || '';
        studentSearchInput.value = option.getAttribute('data-student-label') || option.textContent.trim();
        setStudentSearchOpen(false);

        if (studentIdInput.value && studentIdInput.form) {
            studentIdInput.form.submit();
        }
    }

    function renderStudentResults(items, message) {
        if (!studentResults) {
            return;
        }

        studentResults.innerHTML = '';
        activeIndex = -1;

        if (!items.length) {
            var empty = document.createElement('div');
            empty.className = 'cor-student-empty';
            empty.textContent = message || 'No matching students found';
            studentResults.appendChild(empty);
            setStudentSearchOpen(true);
            return;
        }

        items.forEach(function (item, index) {
            var option = document.createElement('button');
            option.type = 'button';
            option.className = 'cor-student-option';
            option.id = 'cor-student-option-' + index;
            option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', 'false');
            option.setAttribute('data-student-id', item.id);
            option.setAttribute('data-student-label', item.label);

            var number = document.createElement('strong');
            number.textContent = item.student_no || 'No student number';
            var name = document.createElement('span');
            name.textContent = item.name || item.label || 'Unnamed student';

            option.appendChild(number);
            option.appendChild(name);

            option.addEventListener('mouseenter', function () {
                setActiveStudentOption(index);
            });

            option.addEventListener('mousedown', function (event) {
                event.preventDefault();
            });

            option.addEventListener('click', function () {
                chooseStudentOption(option);
            });

            studentResults.appendChild(option);
        });

        setStudentSearchOpen(true);
        setActiveStudentOption(0);
    }

    function searchStudents() {
        if (!studentSearch || !studentSearchInput) {
            return;
        }

        var searchUrl = studentSearch.getAttribute('data-search-url');
        if (!searchUrl) {
            return;
        }

        var query = studentSearchInput.value.trim();

        if (searchRequest && typeof searchRequest.abort === 'function') {
            searchRequest.abort();
        }

        if (window.AbortController) {
            searchRequest = new AbortController();
        }

        renderStudentResults([], 'Searching students...');

        fetch(searchUrl + '?q=' + encodeURIComponent(query) + '&limit=25', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: searchRequest ? searchRequest.signal : undefined
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Student search failed');
                }

                return response.json();
            })
            .then(function (payload) {
                renderStudentResults(payload.results || []);
            })
            .catch(function (error) {
                if (error && error.name === 'AbortError') {
                    return;
                }

                renderStudentResults([], 'Unable to load student matches');
            });
    }

    if (studentSearch && studentIdInput && studentSearchInput && studentResults) {
        var searchTimer = null;

        studentSearchInput.addEventListener('input', function () {
            studentIdInput.value = '';
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(searchStudents, 180);
        });

        studentSearchInput.addEventListener('focus', function () {
            searchStudents();
        });

        studentSearchInput.addEventListener('keydown', function (event) {
            var options = getStudentOptions();

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (!studentSearch.classList.contains('is-open')) {
                    searchStudents();
                    return;
                }
                setActiveStudentOption(activeIndex + 1);
                return;
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                setActiveStudentOption(activeIndex - 1);
                return;
            }

            if (event.key === 'Enter') {
                if (studentSearch.classList.contains('is-open') && options[activeIndex]) {
                    event.preventDefault();
                    chooseStudentOption(options[activeIndex]);
                }
                return;
            }

            if (event.key === 'Escape') {
                setStudentSearchOpen(false);
            }
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('[data-cor-student-search]')) {
                setStudentSearchOpen(false);
            }
        });
    }
});
