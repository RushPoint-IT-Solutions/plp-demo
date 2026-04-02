@extends('layouts.registrar')

@section('title', 'PLP - Copy Of Grades (COG)')
@section('page-title', 'Copy Of Grades (COG)')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cog-copy-of-grades.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="container-fluid cog-copy-of-grades">
    <div class="d-flex justify-content-end mb-3 d-print-none">
        <button type="button" class="btn btn-success" id="cog-print-btn">Print</button>
    </div>

    <div class="row justify-content-center">
        <div class="cog-sheet-column">
            <div class="bg-white border border-dark p-3 cog-sheet">
                <div class="border-top border-bottom border-dark py-2 mb-2">
                    <div class="text-center fw-bold text-uppercase">Student Data</div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-3">
                        <tbody>
                            <tr>
                                <td class="text-nowrap">Student Number</td>
                                <td class="text-nowrap">:</td>
                                <td class="w-100">
                                    <input type="text" name="student_number" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Name</td>
                                <td class="text-nowrap">:</td>
                                <td>
                                    <input type="text" name="name" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Address</td>
                                <td class="text-nowrap">:</td>
                                <td>
                                    <textarea name="address" rows="2" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Sex</td>
                                <td class="text-nowrap">:</td>
                                <td>
                                    <input type="text" name="sex" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Date of Birth</td>
                                <td class="text-nowrap">:</td>
                                <td>
                                    <input type="date" name="date_of_birth" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Place of Birth</td>
                                <td class="text-nowrap">:</td>
                                <td>
                                    <input type="text" name="place_of_birth" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Date of Admission</td>
                                <td class="text-nowrap">:</td>
                                <td>
                                    <input type="date" name="date_of_admission" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Admission Credentials</td>
                                <td class="text-nowrap">:</td>
                                <td>
                                    <input type="text" name="admission_credentials" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Degree/Course/<br>Program</td>
                                <td class="text-nowrap">:</td>
                                <td>
                                    <input type="text" name="degree_course_program" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Date of Completion</td>
                                <td class="text-nowrap">:</td>
                                <td>
                                    <input type="date" name="date_of_completion" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Date of Graduation</td>
                                <td class="text-nowrap">:</td>
                                <td>
                                    <input type="date" name="date_of_graduation" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Resolution No.</td>
                                <td class="text-nowrap">:</td>
                                <td>
                                    <input type="text" name="resolution_no" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="border-top border-bottom border-dark py-2 mb-2">
                    <div class="text-center fw-bold text-uppercase">Scholastic Record</div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-3">
                        <tbody>
                            <tr>
                                <td class="text-nowrap">Junior High School</td>
                                <td class="text-nowrap">:</td>
                                <td class="w-100">
                                    <input type="text" name="junior_high_school" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Year Graduated</td>
                                <td class="text-nowrap">:</td>
                                <td>
                                    <input type="text" name="junior_high_school_year_graduated" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Senior High School</td>
                                <td class="text-nowrap">:</td>
                                <td>
                                    <input type="text" name="senior_high_school" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Year Graduated</td>
                                <td class="text-nowrap">:</td>
                                <td>
                                    <input type="text" name="senior_high_school_year_graduated" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="cog-grading-block mb-0">
                    <div class="cog-grading-block__header fw-bold text-uppercase">
                        <div class="cog-grading-block__title">Grading System</div>
                        <div class="cog-grading-block__remarks-title">Remarks</div>
                    </div>

                    <div class="cog-grading-block__body">
                        <div class="cog-grading-block__system">
                            <table class="table table-sm table-borderless mb-0 cog-grading-table">
                                <tbody>
                                    <tr>
                                        <td>1.00 = 97.5-100</td>
                                        <td>2.25 = 82.5-85.4</td>
                                        <td>INC &nbsp; Incomplete</td>
                                    </tr>
                                    <tr>
                                        <td>1.25 = 94.5-97.4</td>
                                        <td>2.50 = 79.5-82.4</td>
                                        <td>OD &nbsp; Officially Dropped</td>
                                    </tr>
                                    <tr>
                                        <td>1.50 = 91.5-94.4</td>
                                        <td>2.75 = 76.5-79.4</td>
                                        <td>UD &nbsp; Unofficially Dropped</td>
                                    </tr>
                                    <tr>
                                        <td>1.75 = 88.5-91.4</td>
                                        <td>3.00 = 74.5-76.4</td>
                                        <td>NC &nbsp; No Credit</td>
                                    </tr>
                                    <tr>
                                        <td>2.00 = 85.5-88.4</td>
                                        <td>5.00 = 74.4 &amp; below</td>
                                        <td>GNA &nbsp; Grade Not Available</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="cog-credits small">
                                <div class="fw-bold">Credits:</div>
                                <div>
                                    One unit of credit is one hour lecture or recitation or three hours of laboratory work each week for the period of a complete semester.
                                    The medium of instruction in this University is English except courses in Filipino and other foreign languages.
                                </div>
                            </div>
                        </div>

                        <div class="cog-grading-block__remarks fw-bold text-center text-uppercase">
                            FOR EVALUATION PURPOSES ONLY.
                        </div>
                    </div>
                </div>

                <div class="small mb-3 cog-note-block">
                    This copy is an exact reproduction of the original transcript on file with the Office of the University Registrar and should be considered as an original copy
                    when signed by the university registrar and impressed with the university seal. Any erasure or alteration on this transcript renders the whole document invalid
                    unless authenticated by the signature of the foregoing official.
                </div>

                <div class="cog-signatories mb-2">
                    <div class="cog-signatories__labels fw-bold">
                        <div class="cog-signatories__label">Prepared by:</div>
                        <div class="cog-signatories__label">Checked by:</div>
                    </div>

                    <div class="cog-signatories__row">
                        <div class="cog-signatories__item">
                            <input type="text" name="prepared_by" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent text-center cog-signature-input">
                            <div class="fst-italic mt-1">College Secretary</div>
                        </div>

                        <div class="cog-signatories__item">
                            <input type="text" name="checked_by" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent text-center cog-signature-input">
                            <div class="fst-italic mt-1">Assistant Registrar</div>
                        </div>
                    </div>

                    <div class="cog-signatories__registrar">
                        <input type="text" name="university_registrar" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent text-center cog-signature-input">
                        <div class="fst-italic mt-1">University Registrar</div>
                    </div>
                </div>

                <div class="row small align-items-end cog-form-footer">
                    <div class="col-6">
                        <div class="fst-italic">Not Valid Without University Seal</div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <span>Date Issued:</span>
                            <input type="date" name="date_issued" class="form-control form-control-sm border-0 border-bottom border-dark rounded-0 bg-transparent cog-date-input">
                        </div>
                        <div class="text-end">Page 1 of 2</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
