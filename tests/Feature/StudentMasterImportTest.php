<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\StudentMasterImporter;
use App\Services\StudentMasterReader;
use App\Student;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use ZipArchive;

class StudentMasterImportTest extends TestCase
{
    private $files = [];
    private $importer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.student_master_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::setDefaultConnection('student_master_test');
        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('student_no')->unique();
            $table->string('name');
            $table->string('sex')->nullable();
            $table->string('school_year');
            $table->string('semester');
            $table->string('student_type')->default('New');
            $table->boolean('is_withdrawn')->default(false);
            $table->timestamps();
        });
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('student_no')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('student_email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->timestamps();
        });
        require_once database_path('migrations/2026_10_22_000001_create_student_master_sources_table.php');
        (new \CreateStudentMasterSourcesTable())->up();
        $this->importer = new StudentMasterImporter(new StudentMasterReader());
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
        DB::disconnect('student_master_test');
        parent::tearDown();
    }

    public function test_full_source_is_preserved_without_creating_accounts_or_enrollment()
    {
        $path = $this->workbook([
            ['23-00001', 'DOE', 'JANE', '1', '36526', 'first@example.test', 'second@example.test', 'Emergency contact'],
            ['23-00002', 'SMITH', 'JOHN', '0', '', '', '', ''],
        ]);
        $preview = $this->importer->preview($path);
        $this->assertSame(2, $preview['new']);
        $this->assertSame(0, $preview['error_count']);
        $this->assertSame(1, $preview['inactive']);
        $result = $this->importer->import($path);
        $this->assertSame(2, $result['created']);
        $profile = DB::table('student_profiles')->where('student_no', '23-00001')->first();
        $this->assertSame('2000-01-01', $profile->date_of_birth);
        $this->assertSame('first@example.test', $profile->student_email);
        $source = json_decode(DB::table('student_master_sources')->where('student_id', $profile->student_id)->value('source_data'), true);
        $this->assertSame('second@example.test', $source[6]['value']);
        $this->assertSame('Emergency contact', $source[7]['value']);
        $student = DB::table('students')->where('student_no', '23-00001')->first();
        $this->assertSame('', $student->school_year);
        $this->assertSame('', $student->semester);
        $this->assertSame('', $student->student_type);
        $this->assertSame(1, Student::active()->count());
        $this->assertSame(0, DB::table('students')->where('is_withdrawn', true)->count());
        $inactive = Student::with('masterSource')->where('student_no', '23-00002')->first();
        $this->assertTrue($inactive->import_inactive);
    }

    public function test_reimport_and_existing_profile_collisions_are_skipped_without_overwrites()
    {
        $path = $this->workbook([['23-00001', 'DOE', 'JANE', '1']]);
        DB::table('student_profiles')->insert(['student_no' => '23-00001', 'first_name' => 'Existing']);
        $this->assertSame(1, $this->importer->preview($path)['existing']);
        $this->assertSame(0, $this->importer->import($path)['created']);
        $this->assertSame('Existing', DB::table('student_profiles')->value('first_name'));
        $path = $this->workbook([['23-00002', 'SMITH', 'JOHN', '1']]);
        $this->assertSame(1, $this->importer->import($path)['created']);
        $this->assertSame(1, $this->importer->import($path)['skipped']);
        $this->assertSame(1, DB::table('students')->count());
    }

    public function test_duplicate_numbers_and_invalid_status_block_the_whole_import()
    {
        $path = $this->workbook([
            ['23-00001', 'DOE', 'JANE', '1'], ['23-00001', 'SMITH', 'JOHN', 'unknown'],
        ]);
        $this->assertSame(2, $this->importer->preview($path)['error_count']);
        try {
            $this->importer->import($path);
            $this->fail('Invalid source should block the import.');
        } catch (\RuntimeException $expected) {
            $this->assertSame(0, DB::table('students')->count());
        }
    }

    public function test_a_database_failure_rolls_back_previously_inserted_students_and_profiles()
    {
        $path = $this->workbook([
            ['23-00001', 'DOE', 'JANE', '1'], ['23-00002', 'SMITH', 'JOHN', '0'],
        ]);
        DB::statement("CREATE TRIGGER fail_source BEFORE INSERT ON student_master_sources WHEN NEW.source_row = 3 BEGIN SELECT RAISE(ABORT, 'fixture failure'); END");
        try {
            $this->importer->import($path);
            $this->fail('Expected the simulated database failure.');
        } catch (\Illuminate\Database\QueryException $expected) {
            $this->assertSame(0, DB::table('students')->count());
            $this->assertSame(0, DB::table('student_profiles')->count());
            $this->assertSame(0, DB::table('student_master_sources')->count());
        }
    }

    public function test_invalid_birth_dates_are_flagged_and_remain_in_source_instead_of_being_guessed()
    {
        $path = $this->workbook([['23-00001', 'DOE', 'JANE', '1', '02/31/2000']]);
        $this->assertSame(1, $this->importer->preview($path)['warning_count']);
        $this->importer->import($path);
        $this->assertNull(DB::table('student_profiles')->value('date_of_birth'));
        $source = json_decode(DB::table('student_master_sources')->value('source_data'), true);
        $this->assertSame('02/31/2000', $source[4]['value']);
    }

    public function test_placeholder_names_require_explicit_exclusion_and_are_not_created()
    {
        $path = $this->workbook([
            ['23-00001', '-', '-', '1'], ['23-00002', 'SMITH', 'JOHN', '0'],
        ]);
        $preview = $this->importer->preview($path);
        $this->assertSame(1, $preview['invalid_rows']);
        $this->assertSame(1, $preview['new']);
        $result = $this->importer->import($path, true);
        $this->assertSame(1, $result['created']);
        $this->assertSame(1, $result['skipped_invalid']);
        $this->assertSame(0, DB::table('students')->where('student_no', '23-00001')->count());
    }

    public function test_duplicate_numbers_cannot_be_bypassed_by_excluding_invalid_rows()
    {
        $path = $this->workbook([['23-00001', 'DOE', 'JANE', '1'], ['23-00001', 'DOE', 'JANE', '1']]);
        $this->expectException(\RuntimeException::class);
        $this->importer->import($path, true);
    }

    public function test_import_is_restricted_to_unscoped_registrars()
    {
        $request = \Illuminate\Http\Request::create('/student-master-import', 'GET');
        $user = new \App\User(['module' => 'student']);
        $request->setUserResolver(function () use ($user) { return $user; });
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        (new \App\Http\Controllers\Registrar\StudentMasterImportController())->index($request);
    }

    public function test_import_routes_use_existing_student_list_permissions()
    {
        foreach (['', '.preview', '.store'] as $suffix) {
            $name = 'registrar.registrar-menu.student-mgmt.student-master-import' . $suffix;
            $this->assertNotNull(app('router')->getRoutes()->getByName($name));
            $this->assertSame('student_records_student_list', \App\Support\UserAccessGate::routeModuleCode($name));
        }
    }

    public function test_source_details_escape_personal_values_and_keep_duplicate_headings_visible()
    {
        $student = new Student();
        $student->setRelation('masterSource', new \App\StudentMasterSource([
            'is_active' => false, 'source_row' => 2,
            'source_data' => [
                ['column' => 14, 'heading' => 'fldEmailAdd', 'value' => '<script>example</script>'],
                ['column' => 51, 'heading' => 'fldEmailAdd', 'value' => 'second@example.test'],
            ],
        ]));
        $html = view('registrar.registrar-menu.student-management.partials.student-master-source', compact('student'))->render();
        $this->assertStringContainsString('Inactive', $html);
        $this->assertStringContainsString('&lt;script&gt;example&lt;/script&gt;', $html);
        $this->assertSame(2, substr_count($html, 'fldEmailAdd'));
        $this->assertStringNotContainsString('<script>example</script>', $html);
    }

    private function workbook(array $rows): string
    {
        $headers = ['fldStudNo', 'fldLastName', 'fldFirstName', 'fldActive', 'fldDateofBirth',
            'fldEmailAdd', 'fldEmailAdd', 'fldERPersonToNotify'];
        $xml = '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        foreach (array_merge([$headers], $rows) as $index => $row) {
            $xml .= '<row r="' . ($index + 1) . '">';
            foreach ($row as $column => $value) {
                $xml .= '<c r="' . chr(65 + $column) . ($index + 1) . '" t="inlineStr"><is><t>'
                    . htmlspecialchars($value, ENT_XML1, 'UTF-8') . '</t></is></c>';
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData></worksheet>';
        $path = tempnam(sys_get_temp_dir(), 'student-master-test-');
        $this->files[] = $path;
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('xl/worksheets/sheet1.xml', $xml);
        $zip->close();
        return $path;
    }
}
