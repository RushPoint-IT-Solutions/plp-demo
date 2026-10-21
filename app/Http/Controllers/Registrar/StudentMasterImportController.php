<?php

declare(strict_types=1);

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Services\StudentMasterImporter;
use App\Support\CourseScopeGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class StudentMasterImportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeImport($request);
        return view('registrar.registrar-menu.student-management.student-master-import');
    }

    public function preview(Request $request, StudentMasterImporter $importer)
    {
        $this->authorizeImport($request);
        $request->validate(['workbook' => 'required|file|max:16384']);
        $file = $request->file('workbook');
        if (strtolower($file->getClientOriginalExtension()) !== 'xlsx') {
            return back()->withErrors(['workbook' => 'Upload the original StudentMaster .xlsx file.']);
        }
        $token = bin2hex(random_bytes(24));
        $path = $file->storeAs('student-master-imports', $token . '.xlsx', 'local');
        try {
            $preview = $importer->preview(Storage::disk('local')->path($path));
        } catch (\Throwable $error) {
            Storage::disk('local')->delete($path);
            return back()->withErrors(['workbook' => get_class($error) === RuntimeException::class
                ? $error->getMessage() : 'Unable to read this workbook. Verify its format and try again.']);
        }
        $previous = $request->session()->get('student_master_import');
        if ($previous) {
            Storage::disk('local')->delete($previous['path']);
        }
        $request->session()->put('student_master_import', ['token' => $token, 'path' => $path,
            'user_id' => $request->user()->id, 'expires' => time() + 3600,
            'sha256' => hash_file('sha256', Storage::disk('local')->path($path))]);
        return view('registrar.registrar-menu.student-management.student-master-import', compact('preview', 'token'));
    }

    public function store(Request $request, StudentMasterImporter $importer)
    {
        $this->authorizeImport($request);
        $request->validate(['token' => 'required|string', 'skip_invalid' => 'nullable|in:1']);
        $draft = $request->session()->get('student_master_import');
        abort_unless($draft && hash_equals($draft['token'], $request->input('token'))
            && (int) $draft['user_id'] === (int) $request->user()->id && $draft['expires'] >= time(), 419);
        $path = Storage::disk('local')->path($draft['path']);
        abort_unless(is_file($path), 419);
        $lock = fopen($path, 'rb');
        abort_unless($lock && flock($lock, LOCK_EX | LOCK_NB), 409, 'This import is already running.');
        try {
            abort_unless(hash_equals($draft['sha256'], hash_file('sha256', $path)), 409);
            // This is a bounded, explicit import; allow enough time for its atomic transaction.
            set_time_limit(240);
            $result = $importer->import($path, $request->input('skip_invalid') === '1');
        } catch (\Throwable $error) {
            return redirect()->route('registrar.registrar-menu.student-mgmt.student-master-import')
                ->withErrors(['workbook' => 'No records from this attempt were committed. '
                    . (get_class($error) === RuntimeException::class ? $error->getMessage() : 'Check the database schema and retry the preview.')]);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
        Storage::disk('local')->delete($draft['path']);
        $request->session()->forget('student_master_import');
        return redirect()->route('registrar.registrar-menu.student-mgmt.student-master-import')
            ->with('student_master_result', $result);
    }

    private function authorizeImport(Request $request): void
    {
        abort_unless($request->user() && in_array($request->user()->module, ['registrar', 'admin'], true)
            && CourseScopeGate::allowedCourseIds($request->user()) === null, 403);
        abort_unless(Schema::hasTable('student_master_sources'), 503,
            'Apply the StudentMaster source-data migration before using this importer.');
    }
}
