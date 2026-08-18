<?php

namespace App\Support;

use App\Student;

/**
 * Single source of truth for every printable document/certificate tied to a
 * specific student, so the profile page's Documents navbar and the
 * certificates list render from one list instead of duplicated markup.
 */
class StudentDocumentTypes
{
    public static function forStudent(Student $student, bool $isGraduated = false): array
    {
        $id = $student->id;

        return [
            [
                'key' => 'cor',
                'label' => 'Certificate of Registration',
                'short' => 'COR',
                'group' => 'Registration',
                'url' => route('registrar.registrar-menu.forms.cor.certificate-of-registration') . '?student_id=' . $id,
                'icon' => 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 0-1.756 1.077',
                'available' => true,
            ],
            [
                'key' => 'tor',
                'label' => 'Transcript of Records',
                'short' => 'TOR',
                'group' => 'Academic Records',
                'url' => route('registrar.registrar-menu.student-mgmt.student-records.print.tor', $id),
                'icon' => 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 0-1.756 1.077M12 12h4M12 16h4M8 12h.01M8 16h.01',
                'available' => true,
            ],
            [
                'key' => 'diploma',
                'label' => 'Diploma',
                'short' => 'DIPLOMA',
                'group' => 'Graduation',
                'url' => route('registrar.registrar-menu.forms.diploma') . '?student_id=' . $id,
                'icon' => 'M22 10v6M2 10l10-5 10 5-10 5z M6 12v5c3 3 9 3 12 0v-5',
                'available' => $isGraduated,
                'unavailable_reason' => 'Graduate status required',
            ],
            [
                'key' => 'clearance-2',
                'label' => 'Clearance 2',
                'short' => 'CLEARANCE',
                'group' => 'Clearance',
                'url' => route('registrar.registrar-menu.forms.clearance-2.show', ['student' => $id]),
                'icon' => 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11',
                'available' => true,
            ],
            [
                'key' => 'graduation-clearance',
                'label' => 'Graduation Clearance',
                'group' => 'Clearance',
                'url' => route('registrar.registrar-menu.forms.graduation-clearance.show', ['student' => $id]),
                'icon' => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z',
                'available' => true,
            ],
            [
                'key' => 'honorable-dismissal',
                'label' => 'Honorable Dismissal',
                'group' => 'Clearance',
                'url' => route('registrar.registrar-menu.forms.honorable-dismissal.show', ['student' => $id]),
                'icon' => 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11',
                'available' => true,
            ],
            [
                'key' => 'leave-of-absence',
                'label' => 'Leave of Absence',
                'group' => 'Enrollment',
                'url' => route('registrar.registrar-menu.forms.application-leave-of-absence-enrolled.show', ['student' => $id]),
                'icon' => 'M8 2v3M16 2v3M3.5 9.09H20.5M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5z',
                'available' => true,
            ],
            [
                'key' => 'f137a',
                'label' => 'Request Form F137A',
                'group' => 'Academic Records',
                'url' => route('registrar.registrar-menu.forms.request-form-f-137a.show', ['student' => $id]),
                'icon' => 'M4 4h16v16H4zM4 9h16M9 9v11',
                'available' => true,
            ],
            [
                'key' => 'gwa-certificate',
                'label' => 'Certificate of GWA',
                'group' => 'Academic Records',
                'url' => route('registrar.registrar-menu.forms.certificates.certificate-gwa.show', ['student' => $id]),
                'icon' => 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z',
                'available' => true,
            ],
            [
                'key' => 'deans-honors',
                'label' => "Dean's Honors",
                'group' => 'Honors',
                'url' => route('registrar.registrar-menu.forms.certificates.deans-honors.show', ['student' => $id]),
                'icon' => 'M12 2l2.4 4.86 5.36.78-3.88 3.78.92 5.34L12 14.94 7.2 17.46l.92-5.34-3.88-3.78 5.36-.78L12 2z',
                'available' => true,
            ],
            [
                'key' => 'presidents-honors',
                'label' => "President's Honors",
                'group' => 'Honors',
                'url' => route('registrar.registrar-menu.forms.certificates.presidents-honors.show', ['student' => $id]),
                'icon' => 'M12 2l2.4 4.86 5.36.78-3.88 3.78.92 5.34L12 14.94 7.2 17.46l.92-5.34-3.88-3.78 5.36-.78L12 2z',
                'available' => true,
            ],
            [
                'key' => 'form-8c2',
                'label' => 'Form 8C-2 (Graduation)',
                'group' => 'Graduation',
                'url' => route('registrar.registrar-menu.forms.certificates.certificate-graduation-8c2.show', ['student' => $id]),
                'icon' => 'M22 11.08V12a10 10 0 1 1-5.93-9.14M22 4 12 14.01l-3-3',
                'available' => $isGraduated,
                'unavailable_reason' => 'Graduate status required',
            ],
            [
                'key' => 'form-8d2',
                'label' => 'Form 8D-2 (Honor)',
                'group' => 'Honors',
                'url' => route('registrar.registrar-menu.forms.certificates.certificate-honor-8d2.show', ['student' => $id]),
                'icon' => 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z',
                'available' => true,
            ],
            [
                'key' => 'cog',
                'label' => 'Copy of Grades (COG)',
                'group' => 'Academic Records',
                'url' => route('registrar.registrar-menu.forms.cog.copy-of-grades'),
                'icon' => 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M12 12h4M12 16h4M8 12h.01M8 16h.01',
                'available' => true,
            ],
            [
                'key' => 'official-grade-report',
                'label' => 'Official Grade Report',
                'group' => 'Academic Records',
                'url' => route('registrar.registrar-menu.forms.official-grade-report'),
                'icon' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M16 13H8M16 17H8M10 9H8',
                'available' => true,
            ],
            [
                'key' => 'cross-enroll-permit',
                'label' => 'Cross-Enroll Permit',
                'group' => 'Enrollment',
                'url' => route('registrar.registrar-menu.forms.permission-cross-enroll'),
                'icon' => 'M8 7H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3m-1 4-3 3-3-3m3-3v11',
                'available' => true,
            ],
        ];
    }
}
