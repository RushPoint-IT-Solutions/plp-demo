<?php

namespace App\Http\Controllers\Registrar\Services;

use App\StudentProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentAccountController extends Controller
{
    public function studentDiscipline()
    {
        return view('registrar.services.student-account.student-discipline');
    }

    public function family(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $parent = trim((string) $request->get('parent_name', ''));
        $yearLevel = trim((string) $request->get('year_level', ''));
        $withSiblings = (bool) $request->get('with_siblings', false);

        $familyRows = StudentProfile::query()
            ->leftJoin('students', 'student_profiles.student_no', '=', 'students.student_no')
            ->leftJoin('year_blocks', 'students.year_block_id', '=', 'year_blocks.id')
            ->select(
                'student_profiles.id',
                'student_profiles.student_no',
                'student_profiles.first_name',
                'student_profiles.middle_name',
                'student_profiles.last_name',
                'student_profiles.number_of_siblings',
                'student_profiles.first_in_family_college',
                'student_profiles.profile_complete',
                'student_profiles.mother_firstname',
                'student_profiles.mother_lastname',
                'student_profiles.father_firstname',
                'student_profiles.father_lastname',
                'student_profiles.guardian_firstname',
                'student_profiles.guardian_lastname',
                'year_blocks.label as year_level'
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('student_profiles.student_no', 'like', '%' . $search . '%')
                        ->orWhere('student_profiles.first_name', 'like', '%' . $search . '%')
                        ->orWhere('student_profiles.last_name', 'like', '%' . $search . '%');
                });
            })
            ->when($parent !== '', function ($query) use ($parent) {
                $query->where(function ($inner) use ($parent) {
                    $inner->where('student_profiles.mother_firstname', 'like', '%' . $parent . '%')
                        ->orWhere('student_profiles.mother_lastname', 'like', '%' . $parent . '%')
                        ->orWhere('student_profiles.father_firstname', 'like', '%' . $parent . '%')
                        ->orWhere('student_profiles.father_lastname', 'like', '%' . $parent . '%')
                        ->orWhere('student_profiles.guardian_firstname', 'like', '%' . $parent . '%')
                        ->orWhere('student_profiles.guardian_lastname', 'like', '%' . $parent . '%');
                });
            })
            ->when($yearLevel !== '', function ($query) use ($yearLevel) {
                $query->where('year_blocks.label', $yearLevel);
            })
            ->when($withSiblings, function ($query) {
                $query->where('student_profiles.number_of_siblings', '>', 0);
            })
            ->orderBy('student_profiles.last_name')
            ->orderBy('student_profiles.first_name')
            ->paginate(10)
            ->appends($request->except('page'));

        foreach ($familyRows->items() as $row) {
            $row->display_name = trim(implode(', ', array_filter(array(
                trim((string) $row->last_name),
                trim(implode(' ', array_filter(array(
                    trim((string) $row->first_name),
                    trim((string) $row->middle_name),
                )))),
            ))));

            if ($row->display_name === '') {
                $row->display_name = 'N/A';
            }

            $familyKey = trim(implode('|', array_filter(array(
                trim((string) $row->mother_lastname),
                trim((string) $row->mother_firstname),
                trim((string) $row->father_lastname),
                trim((string) $row->father_firstname),
                trim((string) $row->guardian_lastname),
                trim((string) $row->guardian_firstname),
            ))));

            if ($familyKey === '') {
                $familyKey = (string) $row->student_no;
            }

            $row->family_code = 'FAM-' . strtoupper(substr(md5($familyKey), 0, 8));
            $row->eldest_label = (int) $row->first_in_family_college === 1 ? 'Yes' : 'No';
            $row->status_label = (int) $row->profile_complete === 1 ? 'Active' : 'Inactive';
        }

        return view('registrar.services.student-account.family', compact('familyRows', 'search', 'parent', 'yearLevel', 'withSiblings'));
    }

    public function changePassword()
    {
        return view('registrar.services.student-account.change-password');
    }
}
