// applicant-form.js – Application Form interactions

(function () {
    // Photo upload preview
    var photoInput = document.getElementById('photoInput');
    var photoLabel = document.querySelector('.photo-upload-label');

    if (photoInput && photoLabel) {
        photoInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var img = photoLabel.querySelector('img.photo-preview-img');
                    if (!img) {
                        img = document.createElement('img');
                        img.className = 'photo-preview-img';
                        photoLabel.innerHTML = '';
                        photoLabel.appendChild(img);
                    }
                    img.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // "Same as Present Address" – copy fields to permanent section
    var sameCheck        = document.getElementById('sameAsPresent');
    var permAddressRows  = [
        document.getElementById('permanentAddressFields'),
        document.getElementById('permanentSelectFields'),
    ];

    var presentFields    = [
        'present_street', 'present_barangay', 'present_zipcode',
        'present_municipality', 'present_province', 'present_region',
    ];
    var permanentFields  = [
        'permanent_street', 'permanent_barangay', 'permanent_zipcode',
        'permanent_municipality', 'permanent_province', 'permanent_region',
    ];

    function syncPermanent(enabled) {
        permAddressRows.forEach(function (row) {
            if (!row) return;
            row.querySelectorAll('input, select').forEach(function (el) {
                el.disabled = enabled;
            });
            row.style.opacity = enabled ? '0.45' : '1';
        });

        if (enabled) {
            presentFields.forEach(function (pf, i) {
                var src  = document.querySelector('[name="' + pf + '"]');
                var dest = document.querySelector('[name="' + permanentFields[i] + '"]');
                if (src && dest) dest.value = src.value;
            });
        }
    }

    if (sameCheck) {
        syncPermanent(sameCheck.checked);
        sameCheck.addEventListener('change', function () {
            syncPermanent(this.checked);
        });
    }

    // Age auto-fill from Date of Birth
    var dobInput = document.querySelector('[name="date_of_birth"]');
    var ageInput = document.querySelector('[name="age"]');

    function calcAge(dob) {
        var today = new Date();
        var birth = new Date(dob);
        var age   = today.getFullYear() - birth.getFullYear();
        var m     = today.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
        return age >= 0 ? age : 0;
    }

    if (dobInput && ageInput) {
        dobInput.addEventListener('change', function () {
            if (this.value) {
                ageInput.value = calcAge(this.value);
            } else {
                ageInput.value = '';
            }
        });
    }
})();
