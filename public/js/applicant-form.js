// applicant-form.js - Application Form interactions

(function () {
    function goToStep(step) {
        var panels = document.querySelectorAll('.step-panel');
        var items = document.querySelectorAll('.step-item');
        var lines = document.querySelectorAll('.step-line');

        panels.forEach(function (panel) {
            panel.style.display = 'none';
        });

        var activePanel = document.getElementById('step-' + step);
        if (activePanel) {
            activePanel.style.display = 'block';
        }

        items.forEach(function (item) {
            var itemStep = parseInt(item.getAttribute('data-step') || '0', 10);
            item.classList.toggle('active', itemStep === step);
            item.classList.toggle('completed', itemStep < step);
        });

        lines.forEach(function (line, index) {
            line.classList.toggle('active', index < (step - 1));
        });
    }

    window.goToStep = goToStep;

    document.querySelectorAll('[data-go-step]').forEach(function (button) {
        button.addEventListener('click', function () {
            var nextStep = parseInt(this.getAttribute('data-go-step') || '1', 10);
            goToStep(nextStep);
        });
    });

    document.querySelectorAll('.step-item .step-pill').forEach(function (pill) {
        pill.addEventListener('click', function () {
            var parent = this.closest('.step-item');
            if (!parent) return;
            var step = parseInt(parent.getAttribute('data-step') || '1', 10);
            goToStep(step);
        });
    });

    // Photo upload preview
    var photoInput = document.getElementById('photoInput');
    var photoImg = document.getElementById('photoImg');

    if (photoInput && photoImg) {
        photoInput.addEventListener('change', function () {
            if (!this.files || !this.files[0]) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                photoImg.src = e.target.result;
                photoImg.style.display = 'block';
                var icon = document.querySelector('.profile-photo-icon');
                if (icon) icon.style.display = 'none';
            };
            reader.readAsDataURL(this.files[0]);
        });
    }

    // Same as present address
    var sameCheck = document.getElementById('sameAsPresent');
    var permAddressRows = [
        document.getElementById('permanentAddressFields'),
        document.getElementById('permanentSelectFields')
    ];
    var presentFields = [
        'present_street', 'present_barangay', 'present_zipcode',
        'present_municipality', 'present_province', 'present_region'
    ];
    var permanentFields = [
        'permanent_street', 'permanent_barangay', 'permanent_zipcode',
        'permanent_municipality', 'permanent_province', 'permanent_region'
    ];

    function copyAddressValues() {
        presentFields.forEach(function (presentName, i) {
            var source = document.querySelector('[name="' + presentName + '"]');
            var target = document.querySelector('[name="' + permanentFields[i] + '"]');
            if (source && target) {
                target.value = source.value;
            }
        });
    }

    function syncPermanent(enabled) {
        permAddressRows.forEach(function (row) {
            if (!row) return;
            row.querySelectorAll('input, select').forEach(function (el) {
                el.disabled = enabled;
            });
            row.style.opacity = enabled ? '0.45' : '1';
        });

        if (enabled) {
            copyAddressValues();
        }
    }

    if (sameCheck) {
        syncPermanent(sameCheck.checked);
        sameCheck.addEventListener('change', function () {
            syncPermanent(this.checked);
        });

        presentFields.forEach(function (presentName) {
            var source = document.querySelector('[name="' + presentName + '"]');
            if (!source) return;
            source.addEventListener('input', function () {
                if (sameCheck.checked) copyAddressValues();
            });
            source.addEventListener('change', function () {
                if (sameCheck.checked) copyAddressValues();
            });
        });
    }

    // Age auto-fill from Date of Birth
    var dobInput = document.querySelector('[name="date_of_birth"]');
    var ageInput = document.querySelector('[name="age"]');

    function calcAge(dob) {
        var today = new Date();
        var birth = new Date(dob);
        var age = today.getFullYear() - birth.getFullYear();
        var monthDiff = today.getMonth() - birth.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            age--;
        }
        return age >= 0 ? age : 0;
    }

    if (dobInput && ageInput) {
        dobInput.addEventListener('change', function () {
            ageInput.value = this.value ? calcAge(this.value) : '';
        });
    }

    goToStep(1);
})();
