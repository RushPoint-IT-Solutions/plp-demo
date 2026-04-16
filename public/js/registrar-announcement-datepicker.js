(function () {
    function getInputs() {
        return [
            document.getElementById('anFrom'),
            document.getElementById('anTo'),
        ].filter(function (input) {
            return !!input;
        });
    }

    function initPicker(input, modalOverlay) {
        if (!window.flatpickr || input._flatpickr) {
            return null;
        }

        return window.flatpickr(input, {
            altInput: true,
            altInputClass: 'req-modal-input an-date-display',
            appendTo: modalOverlay || document.body,
            allowInput: true,
            clickOpens: true,
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
            }
        });
    }

    function initAnnouncementDatePickers() {
        var modalOverlay = document.getElementById('anModal');
        getInputs().forEach(function (input) {
            initPicker(input, modalOverlay);
        });
    }

    function closeAnnouncementDatePickers() {
        getInputs().forEach(function (input) {
            if (input && input._flatpickr) {
                input._flatpickr.close();
            }
        });
    }

    function setAnnouncementDateValue(inputId, value) {
        var input = document.getElementById(inputId);
        if (!input) {
            return;
        }

        if (input._flatpickr) {
            if (value) {
                input._flatpickr.setDate(value, false, 'Y-m-d');
            } else {
                input._flatpickr.clear();
            }
            return;
        }

        input.value = value || '';
    }

    function clearAnnouncementDateValue(inputId) {
        setAnnouncementDateValue(inputId, '');
    }

    if (!window.registrarAnnouncementDatePicker || typeof window.registrarAnnouncementDatePicker !== 'object') {
        window.registrarAnnouncementDatePicker = {};
    }

    window.registrarAnnouncementDatePicker.refresh = initAnnouncementDatePickers;
    window.registrarAnnouncementDatePicker.closeAll = closeAnnouncementDatePickers;
    window.registrarAnnouncementDatePicker.setValue = setAnnouncementDateValue;
    window.registrarAnnouncementDatePicker.clearValue = clearAnnouncementDateValue;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAnnouncementDatePickers);
    } else {
        initAnnouncementDatePickers();
    }
})();