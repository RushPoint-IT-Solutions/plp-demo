// Auto-jump to the step containing the first validation error, and highlight error fields.
// Expects window.profileErrorKeys to be set in the blade before this script is loaded.
(function () {
    var errorKeys = window.profileErrorKeys;
    if (!errorKeys || !errorKeys.length) return;

    function highlightErrorFields() {
        errorKeys.forEach(function (key) {
            var el = document.querySelector('[name="' + key + '"]');
            if (el) {
                el.classList.add('input-error');
                var col = el.closest('.setup-col');
                if (col) col.classList.add('input-error-col');
            }
        });
    }

    var stepMap = {
        1: ['student_number','last_name','first_name','middle_name','suffix','nickname','gender',
            'nationality','nationality_other','religion','religion_other','date_of_birth',
            'place_of_birth','civil_status','mobile_number','student_email',
            'present_street','present_barangay','present_zipcode','present_municipality','present_province','present_region',
            'permanent_street','permanent_barangay','permanent_zipcode','permanent_municipality','permanent_province','permanent_region'],
        2: ['mother_firstname','mother_middlename','mother_lastname','mother_contact','mother_occupation',
            'father_firstname','father_middlename','father_lastname','father_contact','father_occupation',
            'guardian_firstname','guardian_middlename','guardian_lastname','guardian_contact','guardian_occupation','guardian_address',
            'parent_marital_status','monthly_family_income','number_of_siblings','household_members','dependents'],
        3: ['elementary_school','high_school','junior_school','senior_school','shs_track_strand','lrn','school_last_attended'],
        4: ['family_income_source','living_situation','working_student','has_scholarship','first_in_family_college',
            'internet_access','it_tools_access','devices','lms_used','lms_preferred','lms_reasons',
            'preferred_class_time','evening_classes']
    };

    function jumpToErrorStep() {
        for (var step = 1; step <= 4; step++) {
            for (var i = 0; i < errorKeys.length; i++) {
                var key = errorKeys[i].replace(/\[\]$/, '');
                if (stepMap[step] && stepMap[step].indexOf(key) !== -1) {
                    if (window.goToStep) { window.goToStep(step); }
                    return;
                }
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            jumpToErrorStep();
            highlightErrorFields();
        });
    } else {
        // DOMContentLoaded already fired (goToStep may not be ready yet)
        setTimeout(function () {
            jumpToErrorStep();
            highlightErrorFields();
        }, 50);
    }
})();
