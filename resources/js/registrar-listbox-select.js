(function () {
    function getWrappers() {
        return Array.prototype.slice.call(document.querySelectorAll('[data-listbox-select]'));
    }

    function getEnabledButtons(menu) {
        return Array.prototype.filter.call(
            menu.querySelectorAll('.rg-listbox-option-btn'),
            function (button) {
                return !button.disabled;
            }
        );
    }

    function setActiveOption(menu, optionButton, shouldFocus) {
        menu.querySelectorAll('.rg-listbox-option-btn').forEach(function (button) {
            button.classList.remove('is-active');
            button.setAttribute('tabindex', '-1');
        });

        if (!optionButton || optionButton.disabled) {
            return;
        }

        optionButton.classList.add('is-active');
        optionButton.setAttribute('tabindex', '0');

        if (shouldFocus) {
            optionButton.focus();
            if (typeof optionButton.scrollIntoView === 'function') {
                optionButton.scrollIntoView({ block: 'nearest' });
            }
        }
    }

    function moveActiveOption(menu, step) {
        var buttons = getEnabledButtons(menu);
        if (!buttons.length) {
            return null;
        }

        var active = menu.querySelector('.rg-listbox-option-btn.is-active');
        var currentIndex = buttons.indexOf(active);
        if (currentIndex < 0) {
            currentIndex = 0;
        }

        var nextIndex = currentIndex + step;
        if (nextIndex < 0) {
            nextIndex = buttons.length - 1;
        }

        if (nextIndex >= buttons.length) {
            nextIndex = 0;
        }

        setActiveOption(menu, buttons[nextIndex], true);
        return buttons[nextIndex];
    }

    function closeSelect(wrapper) {
        wrapper.classList.remove('is-open');

        var state = wrapper.__rgListboxState;
        if (!state) {
            return;
        }

        state.trigger.setAttribute('aria-expanded', 'false');
    }

    function closeAll(exceptWrapper) {
        getWrappers().forEach(function (wrapper) {
            if (exceptWrapper && wrapper === exceptWrapper) {
                return;
            }

            closeSelect(wrapper);
        });
    }

    function openSelect(wrapper) {
        var state = wrapper.__rgListboxState;
        if (!state) {
            return;
        }

        closeAll(wrapper);
        wrapper.classList.add('is-open');
        state.trigger.setAttribute('aria-expanded', 'true');

        var buttons = getEnabledButtons(state.menu);
        if (!buttons.length) {
            return;
        }

        var selectedButton = buttons.find(function (button) {
            return button.getAttribute('data-option-value') === state.select.value;
        }) || buttons[0];

        setActiveOption(state.menu, selectedButton, true);
    }

    function commitOption(wrapper, optionButton) {
        if (!optionButton || optionButton.disabled) {
            return;
        }

        var state = wrapper.__rgListboxState;
        if (!state) {
            return;
        }

        state.select.value = optionButton.getAttribute('data-option-value') || '';
        state.select.dispatchEvent(new Event('change', { bubbles: true }));
        closeAll();
        state.trigger.focus();
    }

    function refreshSelection(wrapper) {
        var state = wrapper.__rgListboxState;
        if (!state) {
            return;
        }

        var selectedOption = state.select.options[state.select.selectedIndex];
        var fallback = state.select.getAttribute('data-placeholder') || '';
        state.currentText.textContent = selectedOption ? selectedOption.textContent : fallback;

        state.menu.querySelectorAll('.rg-listbox-option-btn').forEach(function (button) {
            var value = button.getAttribute('data-option-value');
            button.classList.toggle('is-selected', value === state.select.value);
            button.setAttribute('aria-selected', value === state.select.value ? 'true' : 'false');
        });

        state.trigger.disabled = !!state.select.disabled;
        state.trigger.setAttribute('aria-disabled', state.select.disabled ? 'true' : 'false');
    }

    function buildOptionButton(wrapper, option, index) {
        var state = wrapper.__rgListboxState;
        if (!state) {
            return;
        }

        var item = document.createElement('li');
        item.className = 'rg-listbox-option';

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'rg-listbox-option-btn';
        button.textContent = option.textContent || '';
        button.id = state.select.id + '-option-' + index;
        button.setAttribute('role', 'option');
        button.setAttribute('data-option-value', option.value);
        button.setAttribute('aria-selected', option.selected ? 'true' : 'false');

        if (option.disabled) {
            button.disabled = true;
        }

        if (option.selected) {
            button.classList.add('is-selected');
            state.currentText.textContent = option.textContent || '';
        }

        button.addEventListener('mouseenter', function () {
            setActiveOption(state.menu, button, false);
        });

        button.addEventListener('focus', function () {
            setActiveOption(state.menu, button, false);
        });

        button.addEventListener('click', function () {
            commitOption(wrapper, button);
        });

        item.appendChild(button);
        state.menu.appendChild(item);
    }

    function rebuildOptions(wrapper) {
        var state = wrapper.__rgListboxState;
        if (!state) {
            return;
        }

        state.menu.innerHTML = '';

        Array.prototype.forEach.call(state.select.options, function (option, index) {
            buildOptionButton(wrapper, option, index);
        });

        refreshSelection(wrapper);

        var buttons = getEnabledButtons(state.menu);
        if (!buttons.length) {
            return;
        }

        var selectedButton = buttons.find(function (button) {
            return button.getAttribute('data-option-value') === state.select.value;
        }) || buttons[0];

        setActiveOption(state.menu, selectedButton, false);
    }

    function bindWrapper(wrapper) {
        if (wrapper.__rgListboxState) {
            rebuildOptions(wrapper);
            return;
        }

        var select = wrapper.querySelector('select.js-rg-listbox-native');
        var trigger = wrapper.querySelector('[data-select-trigger]');
        var currentText = wrapper.querySelector('[data-select-current]');
        var menu = wrapper.querySelector('[data-select-menu]');

        if (!select || !trigger || !currentText || !menu) {
            return;
        }

        wrapper.__rgListboxState = {
            select: select,
            trigger: trigger,
            currentText: currentText,
            menu: menu,
        };

        select.classList.add('is-enhanced');
        trigger.setAttribute('aria-controls', select.id + '-custom-menu');
        menu.id = select.id + '-custom-menu';

        trigger.addEventListener('click', function () {
            if (wrapper.classList.contains('is-open')) {
                closeSelect(wrapper);
                return;
            }

            openSelect(wrapper);
        });

        trigger.addEventListener('keydown', function (event) {
            var state = wrapper.__rgListboxState;
            if (!state) {
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();

                if (!wrapper.classList.contains('is-open')) {
                    openSelect(wrapper);
                }

                moveActiveOption(state.menu, event.key === 'ArrowDown' ? 1 : -1);
                return;
            }

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();

                if (!wrapper.classList.contains('is-open')) {
                    openSelect(wrapper);
                    return;
                }

                commitOption(wrapper, state.menu.querySelector('.rg-listbox-option-btn.is-active'));
            }

            if (event.key === 'Escape') {
                closeSelect(wrapper);
            }
        });

        select.addEventListener('change', function () {
            refreshSelection(wrapper);
        });

        menu.addEventListener('keydown', function (event) {
            var state = wrapper.__rgListboxState;
            if (!state) {
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                moveActiveOption(state.menu, event.key === 'ArrowDown' ? 1 : -1);
                return;
            }

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                commitOption(wrapper, state.menu.querySelector('.rg-listbox-option-btn.is-active'));
                return;
            }

            if (event.key === 'Escape') {
                closeSelect(wrapper);
                state.trigger.focus();
            }
        });

        rebuildOptions(wrapper);
    }

    function refresh(target) {
        var wrappers = [];

        if (!target) {
            wrappers = getWrappers();
        } else if (target.matches && target.matches('[data-listbox-select]')) {
            wrappers = [target];
        } else if (target.closest) {
            var closest = target.closest('[data-listbox-select]');
            if (closest) {
                wrappers = [closest];
            }
        }

        wrappers.forEach(function (wrapper) {
            bindWrapper(wrapper);
        });
    }

    if (!getWrappers().length) {
        return;
    }

    refresh();

    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-listbox-select]')) {
            closeAll();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    document.addEventListener('registrar:listbox:refresh', function (event) {
        var target = event && event.detail ? event.detail.target : null;
        refresh(target || null);
    });

    if (!window.registrarListboxSelect || typeof window.registrarListboxSelect !== 'object') {
        window.registrarListboxSelect = {};
    }

    window.registrarListboxSelect.refresh = function (target) {
        refresh(target || null);
    };

    window.registrarListboxSelect.refreshAll = function () {
        refresh();
    };
})();
