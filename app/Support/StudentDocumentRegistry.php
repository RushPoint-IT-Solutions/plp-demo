<?php

namespace App\Support;

/**
 * Canonical list of every document type tied to a student, used to drive the
 * dynamic "Document Templates" editor navbar under Admin Tools > System
 * Config. Each entry says whether it currently has an editable template and,
 * if so, which editor engine renders it — either one of the three bespoke
 * editors (COR/TOR/Honorable Dismissal, edited in place on their own live
 * page) or the shared DocumentTemplateController engine (editor => 'generic').
 *
 * To make a new document type editable: build its editable header/canvas in
 * its own blade view (or adopt DocumentTemplateTokens for a generic one),
 * then flip has_editor to true here.
 */
class StudentDocumentRegistry
{
    public static function all(): array
    {
        return [
            [
                'slug' => 'cor', 'label' => 'Certificate of Registration', 'group' => 'Registration',
                'has_editor' => true, 'editor' => 'dedicated',
                'live_url' => route('registrar.registrar-menu.forms.cor.certificate-of-registration'),
            ],
            [
                'slug' => 'tor', 'label' => 'Transcript of Records', 'group' => 'Academic Records',
                'has_editor' => true, 'editor' => 'dedicated',
                'live_url' => null,
            ],
            [
                'slug' => 'honorable-dismissal', 'label' => 'Honorable Dismissal', 'group' => 'Clearance',
                'has_editor' => true, 'editor' => 'dedicated',
                'live_url' => route('registrar.registrar-menu.forms.honorable-dismissal'),
            ],
            [
                'slug' => 'f137a', 'label' => 'Request Form F137A', 'group' => 'Academic Records',
                'has_editor' => true, 'editor' => 'generic',
                'live_url' => route('registrar.registrar-menu.forms.request-form-f-137a'),
            ],
            [
                'slug' => 'certificate-gwa', 'label' => 'Certificate of GWA', 'group' => 'Academic Records',
                'has_editor' => true, 'editor' => 'generic',
                'live_url' => route('registrar.registrar-menu.forms.certificates.certificate-gwa'),
            ],
            [
                'slug' => 'diploma', 'label' => 'Diploma', 'group' => 'Graduation',
                'has_editor' => false, 'editor' => null, 'live_url' => null,
            ],
            [
                'slug' => 'clearance-2', 'label' => 'Clearance 2', 'group' => 'Clearance',
                'has_editor' => false, 'editor' => null, 'live_url' => null,
            ],
            [
                'slug' => 'graduation-clearance', 'label' => 'Graduation Clearance', 'group' => 'Clearance',
                'has_editor' => false, 'editor' => null, 'live_url' => null,
            ],
            [
                'slug' => 'leave-of-absence', 'label' => 'Leave of Absence', 'group' => 'Enrollment',
                'has_editor' => false, 'editor' => null, 'live_url' => null,
            ],
            [
                'slug' => 'deans-honors', 'label' => "Dean's Honors", 'group' => 'Honors',
                'has_editor' => false, 'editor' => null, 'live_url' => null,
            ],
            [
                'slug' => 'presidents-honors', 'label' => "President's Honors", 'group' => 'Honors',
                'has_editor' => false, 'editor' => null, 'live_url' => null,
            ],
            [
                'slug' => 'form-8c2', 'label' => 'Form 8C-2 (Graduation)', 'group' => 'Graduation',
                'has_editor' => false, 'editor' => null, 'live_url' => null,
            ],
            [
                'slug' => 'form-8d2', 'label' => 'Form 8D-2 (Honor)', 'group' => 'Honors',
                'has_editor' => false, 'editor' => null, 'live_url' => null,
            ],
            [
                'slug' => 'cog', 'label' => 'Copy of Grades (COG)', 'group' => 'Academic Records',
                'has_editor' => false, 'editor' => null, 'live_url' => null,
            ],
            [
                'slug' => 'official-grade-report', 'label' => 'Official Grade Report', 'group' => 'Academic Records',
                'has_editor' => false, 'editor' => null, 'live_url' => null,
            ],
            [
                'slug' => 'cross-enroll-permit', 'label' => 'Cross-Enroll Permit', 'group' => 'Enrollment',
                'has_editor' => false, 'editor' => null, 'live_url' => null,
            ],
        ];
    }
}
