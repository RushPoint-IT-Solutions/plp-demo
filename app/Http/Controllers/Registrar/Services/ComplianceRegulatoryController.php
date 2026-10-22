<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Http\Controllers\Controller;
use App\Support\SystemConfigSchoolTermOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ComplianceRegulatoryController extends Controller
{
    /**
     * Display the institutional compliance data-readiness register.
     *
     * The module intentionally treats dates as internal planning targets. Agency
     * submission deadlines must be confirmed against the current official notice.
     */
    public function index(Request $request)
    {
        $options = SystemConfigSchoolTermOptions::resolveOptions();
        $schoolYears = collect($options['school_years'] ?? [])->values()->all();
        $semesterMap = (array) ($options['semester_map'] ?? []);

        $selectedSchoolYear = trim((string) $request->query(
            'school_year',
            $options['default_school_year'] ?? ($schoolYears[0] ?? '')
        ));
        if (!in_array($selectedSchoolYear, $schoolYears, true)) {
            $selectedSchoolYear = (string) ($options['default_school_year'] ?? ($schoolYears[0] ?? ''));
        }

        $termOptions = SystemConfigSchoolTermOptions::semesterOptionsForYear($semesterMap, $selectedSchoolYear);
        $selectedTerm = trim((string) $request->query(
            'term',
            $options['default_semester'] ?? ($termOptions[0] ?? 'First')
        ));
        if (!in_array($selectedTerm, $termOptions, true)) {
            $selectedTerm = (string) ($termOptions[0] ?? 'First');
        }

        $safeCount = function (string $table, callable $scope = null): int {
            try {
                if (!Schema::hasTable($table)) {
                    return 0;
                }

                $query = DB::table($table);
                if ($scope) {
                    $scope($query);
                }

                return (int) $query->count();
            } catch (\Throwable $exception) {
                report($exception);

                return 0;
            }
        };

        $dataCounts = [
            'students' => $safeCount('students'),
            'programs' => $safeCount('courses'),
            'faculty' => $safeCount('faculties'),
            'graduates' => $safeCount('graduate_taggings', function ($query) {
                $query->where('is_graduate', true);
            }),
            'unifast' => $safeCount('scholarship_student', function ($query) use ($selectedSchoolYear, $selectedTerm) {
                $query->where('is_unifast', true);
                if (Schema::hasColumn('scholarship_student', 'school_year')) {
                    $query->where('school_year', $selectedSchoolYear);
                }
                if (Schema::hasColumn('scholarship_student', 'semester')) {
                    $query->where('semester', $selectedTerm);
                }
            }),
            'audit_events' => $safeCount('audit_events'),
        ];

        $requirements = [
            [
                'code' => 'CHED-HEMIS-A',
                'agency' => 'CHED',
                'report' => 'HEMIS Form A Institutional Profile',
                'owner' => 'Registrar / Planning',
                'coverage' => number_format($dataCounts['programs']) . ' programs and institutional master data',
                'target_date' => '2026-10-15',
                'status' => 'Ready',
                'completeness' => 100,
                'evidence' => ['Institution profile', 'Program permits and recognition', 'Campus and contact details'],
                'validations' => ['Required institution fields are populated', 'Program codes are unique', 'Reporting period matches the selected school year'],
                'reference_url' => 'https://region1.ched.gov.ph/chedro-spearheads-2024-hemis-orientation/',
            ],
            [
                'code' => 'CHED-HEMIS-BC',
                'agency' => 'CHED',
                'report' => 'HEMIS Form BC Programs, Enrollment and Graduates',
                'owner' => 'Registrar',
                'coverage' => number_format($dataCounts['students']) . ' students; ' . number_format($dataCounts['graduates']) . ' tagged graduates',
                'target_date' => '2026-10-31',
                'status' => 'For review',
                'completeness' => 88,
                'evidence' => ['Enrollment extract', 'Program-level summary', 'Graduate reconciliation'],
                'validations' => ['Student totals reconcile to program subtotals', 'No duplicate student number in the period', 'Graduate records include program and graduation date'],
                'reference_url' => 'https://ro11.ched.gov.ph/2024/10/25/online-orientation-for-the-annual-data-collection-of-hemis-for-sy-2024-2025/9581/uncategorized/',
            ],
            [
                'code' => 'CHED-HEMIS-E5',
                'agency' => 'CHED',
                'report' => 'HEMIS Form E5 Faculty Profile',
                'owner' => 'HR / Registrar',
                'coverage' => number_format($dataCounts['faculty']) . ' faculty records',
                'target_date' => '2026-10-31',
                'status' => 'Ready',
                'completeness' => 94,
                'evidence' => ['Faculty master list', 'Highest degree evidence', 'Teaching load assignment'],
                'validations' => ['Faculty identifier is unique', 'Employment status and highest degree are populated', 'Teaching loads reconcile to active offerings'],
                'reference_url' => 'https://ro11.ched.gov.ph/2024/10/25/online-orientation-for-the-annual-data-collection-of-hemis-for-sy-2024-2025/9581/uncategorized/',
            ],
            [
                'code' => 'CHED-GRAD-LIST',
                'agency' => 'CHED',
                'report' => 'Graduate List and Supporting Records',
                'owner' => 'Records and Graduation',
                'coverage' => number_format($dataCounts['graduates']) . ' graduate records',
                'target_date' => '2026-11-15',
                'status' => 'Due soon',
                'completeness' => 78,
                'evidence' => ['Approved graduate list', 'Special Order details where applicable', 'Degree and award reconciliation'],
                'validations' => ['Graduate tagging is approved', 'Name and degree match the permanent record', 'Graduation and Special Order dates use valid formats'],
                'reference_url' => 'https://ro11.ched.gov.ph/2024/10/25/online-orientation-for-the-annual-data-collection-of-hemis-for-sy-2024-2025/9581/uncategorized/',
            ],
            [
                'code' => 'UNIFAST-FHE',
                'agency' => 'UniFAST',
                'report' => 'Free Higher Education Billing Support',
                'owner' => 'Scholarship / Finance',
                'coverage' => number_format($dataCounts['unifast']) . ' UniFAST-tagged beneficiaries for the selected term',
                'target_date' => '2026-11-30',
                'status' => 'Action required',
                'completeness' => 72,
                'evidence' => ['Validated beneficiary list', 'Enrollment and assessment support', 'Billing reconciliation and approval'],
                'validations' => ['Each beneficiary is enrolled in the reporting period', 'Student and program identifiers are complete', 'Billed totals reconcile to approved supporting schedules'],
                'reference_url' => 'https://unifast.gov.ph/assets/pdf/guidelines/FHE.pdf',
            ],
            [
                'code' => 'UNIFAST-TES',
                'agency' => 'UniFAST',
                'report' => 'Tertiary Education Subsidy Validation',
                'owner' => 'Scholarship Office',
                'coverage' => number_format($dataCounts['unifast']) . ' candidate beneficiary records',
                'target_date' => '2026-12-15',
                'status' => 'Due soon',
                'completeness' => 84,
                'evidence' => ['Eligibility documents', 'Student validation result', 'Disbursement or billing support'],
                'validations' => ['Required documentary evidence is attached', 'No duplicate beneficiary for the term', 'Validation and approval statuses follow the prescribed sequence'],
                'reference_url' => 'https://unifast.gov.ph/assets/pdf/infgrfx/Enhanced-tes-2024.pdf',
            ],
            [
                'code' => 'NPC-REG',
                'agency' => 'NPC',
                'report' => 'DPO and Data Processing System Registration',
                'owner' => 'Data Protection Officer',
                'coverage' => 'Institutional registration and processing-system inventory',
                'target_date' => '2027-01-10',
                'status' => 'For review',
                'completeness' => 92,
                'evidence' => ['DPO appointment and contact details', 'Data processing system inventory', 'Current registration certificate'],
                'validations' => ['Registration scope covers current processing systems', 'DPO details match the official appointment', 'Renewal target is tracked before certificate expiry'],
                'reference_url' => 'https://privacy.gov.ph/pips-and-pics/register/',
            ],
            [
                'code' => 'NPC-SECURITY',
                'agency' => 'NPC',
                'report' => 'Privacy Security Incident and Breach Monitoring',
                'owner' => 'DPO / Information Security',
                'coverage' => number_format($dataCounts['audit_events']) . ' available system audit events',
                'target_date' => '2027-03-31',
                'status' => 'Action required',
                'completeness' => 67,
                'evidence' => ['Incident register', 'Assessment and notification records', 'Corrective action evidence'],
                'validations' => ['Each incident has an owner, date, impact and disposition', 'Potential breaches are escalated to the DPO', 'Evidence access is restricted and all changes are auditable'],
                'reference_url' => 'https://privacy.gov.ph/appointing-a-data-protection-officer/',
            ],
        ];

        $statusCounts = array_count_values(array_map(function (array $requirement) {
            return $requirement['status'];
        }, $requirements));
        $summary = [
            'overall_readiness' => (int) round(collect($requirements)->avg('completeness')),
            'total' => count($requirements),
            'ready' => (int) ($statusCounts['Ready'] ?? 0),
            'due_soon' => (int) ($statusCounts['Due soon'] ?? 0),
            'action_required' => (int) ($statusCounts['Action required'] ?? 0),
            'for_review' => (int) ($statusCounts['For review'] ?? 0),
        ];

        return view('registrar.services.reports-admin.compliance-regulatory', compact(
            'requirements',
            'summary',
            'dataCounts',
            'schoolYears',
            'termOptions',
            'semesterMap',
            'selectedSchoolYear',
            'selectedTerm'
        ));
