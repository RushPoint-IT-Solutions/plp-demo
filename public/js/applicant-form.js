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

    // ── Cascading address dropdowns (region → province → municipality) ──────────────────
    function fillSelect(selectEl, items, placeholder) {
        selectEl.innerHTML = '<option value="" disabled selected>' + placeholder + '</option>';
        items.forEach(function (item) {
            var opt = document.createElement('option');
            opt.value = item;
            opt.textContent = item;
            selectEl.appendChild(opt);
        });
        selectEl.disabled = false;
    }

    function resetSelect(selectEl, placeholder) {
        selectEl.innerHTML = '<option value="" disabled selected>' + placeholder + '</option>';
        selectEl.disabled = true;
        selectEl.value = '';
    }

    function setupCascade(prefix, addressData) {
        var regionEl   = document.querySelector('[name="' + prefix + '_region"]');
        var provinceEl = document.querySelector('[name="' + prefix + '_province"]');
        var cityEl     = document.querySelector('[name="' + prefix + '_municipality"]');
        if (!regionEl || !provinceEl || !cityEl) { return; }

        fillSelect(regionEl, addressData.map(function (r) { return r.name; }), 'Choose Region');
        regionEl.disabled = false;

        regionEl.addEventListener('change', function () {
            resetSelect(provinceEl, 'Choose Province');
            resetSelect(cityEl, 'Choose City/Municipality');

            var region = addressData.find(function (r) { return r.name === regionEl.value; });
            if (!region) { return; }

            if (region.provinces.length === 1) {
                fillSelect(provinceEl, [region.provinces[0].name], 'Choose Province');
                provinceEl.value = region.provinces[0].name;
                provinceEl.dispatchEvent(new Event('change'));
            } else {
                fillSelect(provinceEl, region.provinces.map(function (p) { return p.name; }), 'Choose Province');
            }
        });

        provinceEl.addEventListener('change', function () {
            resetSelect(cityEl, 'Choose City/Municipality');

            var region = addressData.find(function (r) { return r.name === regionEl.value; });
            if (!region) { return; }
            var province = region.provinces.find(function (p) { return p.name === provinceEl.value; });
            if (!province) { return; }
            fillSelect(cityEl, province.cities, 'Choose City/Municipality');
        });
    }

    function syncAddressCascade(prefixFrom, prefixTo) {
        var fromRegion   = document.querySelector('[name="' + prefixFrom + '_region"]');
        var fromProvince = document.querySelector('[name="' + prefixFrom + '_province"]');
        var fromCity     = document.querySelector('[name="' + prefixFrom + '_municipality"]');
        var toRegion     = document.querySelector('[name="' + prefixTo + '_region"]');
        var toProvince   = document.querySelector('[name="' + prefixTo + '_province"]');
        var toCity       = document.querySelector('[name="' + prefixTo + '_municipality"]');

        if (fromRegion && toRegion) {
            toRegion.value = fromRegion.value;
            toRegion.dispatchEvent(new Event('change'));
        }

        setTimeout(function () {
            if (fromProvince && toProvince && fromProvince.value) {
                toProvince.value = fromProvince.value;
                toProvince.dispatchEvent(new Event('change'));
            }
        }, 60);

        setTimeout(function () {
            if (fromCity && toCity && fromCity.value) {
                toCity.value = fromCity.value;
            }
        }, 120);
    }

    fetch('/js/ph-address.json')
        .then(function (res) { return res.json(); })
        .then(function (addressData) {
            setupCascade('present', addressData);
            setupCascade('permanent', addressData);

            function restoreCascade(prefix) {
                if (!window.applicantAddressDraft) { return; }
                var draft = window.applicantAddressDraft;
                var regionEl   = document.querySelector('[name="' + prefix + '_region"]');
                var provinceEl = document.querySelector('[name="' + prefix + '_province"]');
                var cityEl     = document.querySelector('[name="' + prefix + '_municipality"]');
                if (!regionEl || !provinceEl || !cityEl) { return; }

                var savedRegion   = draft[prefix + '_region'];
                var savedProvince = draft[prefix + '_province'];
                var savedCity     = draft[prefix + '_municipality'];
                if (!savedRegion) { return; }

                regionEl.value = savedRegion;
                regionEl.dispatchEvent(new Event('change'));

                setTimeout(function () {
                    if (savedProvince) {
                        provinceEl.value = savedProvince;
                        provinceEl.dispatchEvent(new Event('change'));
                    }
                }, 50);

                setTimeout(function () {
                    if (savedCity) {
                        cityEl.value = savedCity;
                    }
                }, 120);
            }

            restoreCascade('present');
            restoreCascade('permanent');

            if (sameCheck && sameCheck.checked) {
                syncAddressCascade('present', 'permanent');
            }
        })
        .catch(function (err) {
            console.warn('Could not load address data:', err);
        });

    goToStep(1);
})();
