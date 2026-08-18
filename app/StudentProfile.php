<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class StudentProfile extends Model
{
    protected $table = 'student_profiles';

    protected $fillable = [
        'student_no', 'first_name', 'last_name', 'middle_name', 'suffix', 'nickname',
        'gender', 'nationality', 'nationality_other', 'religion', 'religion_other',
        'date_of_birth', 'place_of_birth', 'civil_status', 'mobile_number',
        'student_email', 'profile_photo_path',

        'present_street', 'present_barangay', 'present_zipcode',
        'present_municipality', 'present_province', 'present_region',
        'present_region_id', 'present_province_id', 'present_municipality_id',
        'present_location_address_id',
        'permanent_street', 'permanent_barangay', 'permanent_zipcode',
        'permanent_municipality', 'permanent_province', 'permanent_region',
        'permanent_region_id', 'permanent_province_id', 'permanent_municipality_id',
        'permanent_location_address_id',
        'same_as_present', 'is_orphan', 'is_first_gen', 'is_4ps',
        'has_disability', 'is_foreign',

        'mother_firstname', 'mother_middlename', 'mother_lastname',
        'mother_contact', 'mother_occupation', 'mother_pensioner',
        'father_firstname', 'father_middlename', 'father_lastname',
        'father_contact', 'father_occupation', 'father_pensioner',
        'guardian_firstname', 'guardian_middlename', 'guardian_lastname',
        'guardian_contact', 'guardian_occupation', 'guardian_address',
        'parent_marital_status', 'monthly_family_income',
        'number_of_siblings', 'household_members', 'dependents',

        'junior_school', 'senior_school', 'shs_track_strand', 'no_k12', 'lrn',
        'elementary_school', 'high_school', 'school_last_attended',
        'elementary_year_graduated', 'high_school_year_graduated',
        'junior_school_year_graduated', 'senior_school_year_graduated',
        'school_last_attended_year_graduated',

        'family_income_source', 'family_income_source_other',
        'living_situation', 'living_situation_other',
        'working_student', 'has_scholarship', 'first_in_family_college',
        'evening_classes',
        'profile_complete',
    ];

    protected $casts = [
        'date_of_birth'    => 'date',
        'present_region_id' => 'integer',
        'present_province_id' => 'integer',
        'present_municipality_id' => 'integer',
        'permanent_region_id' => 'integer',
        'permanent_province_id' => 'integer',
        'permanent_municipality_id' => 'integer',
        'same_as_present'  => 'boolean',
        'is_orphan'        => 'boolean',
        'is_first_gen'     => 'boolean',
        'is_4ps'           => 'boolean',
        'has_disability'   => 'boolean',
        'is_foreign'       => 'boolean',
        'mother_pensioner' => 'boolean',
        'father_pensioner' => 'boolean',
        'no_k12'           => 'boolean',
        'profile_complete' => 'boolean',
    ];

    public function profileImage()
    {
        return $this->hasOne(StudentProfileImage::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function presentRegion()
    {
        return $this->belongsTo(PhRegion::class, 'present_region_id');
    }

    public function presentProvince()
    {
        return $this->belongsTo(PhProvince::class, 'present_province_id');
    }

    public function presentMunicipality()
    {
        return $this->belongsTo(PhMunicipality::class, 'present_municipality_id');
    }

    public function permanentRegion()
    {
        return $this->belongsTo(PhRegion::class, 'permanent_region_id');
    }

    public function permanentProvince()
    {
        return $this->belongsTo(PhProvince::class, 'permanent_province_id');
    }

    public function permanentMunicipality()
    {
        return $this->belongsTo(PhMunicipality::class, 'permanent_municipality_id');
    }

    public function optionValues()
    {
        return $this->hasMany(StudentProfileOptionValue::class)
            ->orderBy('sort_order')
            ->with('option');
    }

    public function syncWaveBLearningPreferences(array $data)
    {
        if (!$this->exists
            || !Schema::hasTable('student_profile_option_lookups')
            || !Schema::hasTable('student_profile_option_values')) {
            return;
        }

        $this->syncDomainValues('internet_access', [$data['internet_access'] ?? null], null);
        $this->syncDomainValues('it_tools_access', [$data['it_tools_access'] ?? null], null);
        $this->syncDomainValues('devices', (array) ($data['devices'] ?? []), $data['devices_other'] ?? null);
        $this->syncDomainValues('lms_used', [$data['lms_used'] ?? null], $data['lms_used_other'] ?? null);
        $this->syncDomainValues('lms_preferred', [$data['lms_preferred'] ?? null], $data['lms_preferred_other'] ?? null);
        $this->syncDomainValues('lms_reasons', (array) ($data['lms_reasons'] ?? []), $data['lms_reasons_other'] ?? null);
        $this->syncDomainValues('preferred_class_time', [$data['preferred_class_time'] ?? null], null);

        unset($this->relations['optionValues']);
    }

    public function getInternetAccessAttribute($legacyValue)
    {
        if ($this->hasLegacyValue($legacyValue)) {
            return $legacyValue;
        }

        $row = $this->domainRows('internet_access')->first();
        return $this->rowLabel($row);
    }

    public function getItToolsAccessAttribute($legacyValue)
    {
        if ($this->hasLegacyValue($legacyValue)) {
            return $legacyValue;
        }

        $row = $this->domainRows('it_tools_access')->first();
        return $this->rowLabel($row);
    }

    public function getDevicesAttribute($legacyValue)
    {
        if ($this->hasLegacyValue($legacyValue)) {
            return $legacyValue;
        }

        $labels = $this->domainLabels('devices');
        return count($labels) ? json_encode($labels) : null;
    }

    public function getDevicesOtherAttribute($legacyValue)
    {
        if ($this->hasLegacyValue($legacyValue)) {
            return $legacyValue;
        }

        return $this->domainOtherValue('devices');
    }

    public function getLmsUsedAttribute($legacyValue)
    {
        if ($this->hasLegacyValue($legacyValue)) {
            return $legacyValue;
        }

        $row = $this->domainRows('lms_used')->first();
        return $this->rowLabel($row);
    }

    public function getLmsUsedOtherAttribute($legacyValue)
    {
        if ($this->hasLegacyValue($legacyValue)) {
            return $legacyValue;
        }

        return $this->domainOtherValue('lms_used');
    }

    public function getLmsPreferredAttribute($legacyValue)
    {
        if ($this->hasLegacyValue($legacyValue)) {
            return $legacyValue;
        }

        $row = $this->domainRows('lms_preferred')->first();
        return $this->rowLabel($row);
    }

    public function getLmsPreferredOtherAttribute($legacyValue)
    {
        if ($this->hasLegacyValue($legacyValue)) {
            return $legacyValue;
        }

        return $this->domainOtherValue('lms_preferred');
    }

    public function getLmsReasonsAttribute($legacyValue)
    {
        if ($this->hasLegacyValue($legacyValue)) {
            return $legacyValue;
        }

        $labels = $this->domainLabels('lms_reasons');
        return count($labels) ? json_encode($labels) : null;
    }

    public function getLmsReasonsOtherAttribute($legacyValue)
    {
        if ($this->hasLegacyValue($legacyValue)) {
            return $legacyValue;
        }

        return $this->domainOtherValue('lms_reasons');
    }

    public function getPreferredClassTimeAttribute($legacyValue)
    {
        if ($this->hasLegacyValue($legacyValue)) {
            return $legacyValue;
        }

        $row = $this->domainRows('preferred_class_time')->first();
        return $this->rowLabel($row);
    }

    private function syncDomainValues($domain, array $values, $otherValue)
    {
        StudentProfileOptionValue::query()
            ->where('student_profile_id', $this->id)
            ->where('domain', $domain)
            ->delete();

        $sortOrder = 0;
        foreach ($values as $value) {
            $label = trim((string) $value);
            if ($label === '') {
                continue;
            }

            $isOther = strcasecmp($label, 'Others') === 0;
            $lookup = StudentProfileOptionLookup::query()->firstOrCreate(
                [
                    'domain' => $domain,
                    'code' => $this->normalizeCode($label),
                ],
                [
                    'label' => $label,
                    'is_other' => $isOther,
                ]
            );

            StudentProfileOptionValue::query()->create([
                'student_profile_id' => $this->id,
                'domain' => $domain,
                'option_lookup_id' => $lookup->id,
                'value_text' => $isOther ? $this->nullableTrim($otherValue) : null,
                'sort_order' => $sortOrder,
            ]);

            $sortOrder++;
        }
    }

    private function domainRows($domain)
    {
        if (!$this->exists || !Schema::hasTable('student_profile_option_values')) {
            return collect();
        }

        if ($this->relationLoaded('optionValues')) {
            return $this->getRelation('optionValues')->where('domain', $domain)->values();
        }

        return StudentProfileOptionValue::query()
            ->where('student_profile_id', $this->id)
            ->where('domain', $domain)
            ->orderBy('sort_order')
            ->with('option')
            ->get();
    }

    private function domainLabels($domain)
    {
        $labels = [];
        foreach ($this->domainRows($domain) as $row) {
            $labels[] = $this->rowLabel($row);
        }

        return array_values(array_filter($labels, function ($label) {
            return trim((string) $label) !== '';
        }));
    }

    private function domainOtherValue($domain)
    {
        foreach ($this->domainRows($domain) as $row) {
            if ($this->rowIsOther($row)) {
                return $this->nullableTrim($row->value_text);
            }
        }

        return null;
    }

    private function rowLabel($row)
    {
        if (!$row) {
            return null;
        }

        $label = optional($row->option)->label;
        if ($this->rowIsOther($row)) {
            return 'Others';
        }

        return $this->nullableTrim($label);
    }

    private function rowIsOther($row)
    {
        return (bool) optional($row->option)->is_other;
    }

    private function hasLegacyValue($value)
    {
        return trim((string) $value) !== '';
    }

    private function normalizeCode($label)
    {
        $text = strtolower(trim((string) $label));
        $text = preg_replace('/[^a-z0-9]+/', '_', $text);
        $text = trim((string) $text, '_');

        return $text !== '' ? $text : 'value';
    }

    private function nullableTrim($value)
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }
}
