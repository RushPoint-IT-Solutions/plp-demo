<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlpMasterlistSeeder extends Seeder
{
    /**
     * Seed real PLP program, room, and TOR-signatory data sourced from the
     * registrar's masterlists.xlsx (PROGRAM LIST, ROOMS, and SIGNATORIES sheets).
     *
     * Run with: php artisan db:seed --class=PlpMasterlistSeeder
     *
     * @return void
     */
    public function run()
    {
        $this->seedPrograms();
        $this->seedRooms();
        $this->seedSignatories();
    }

    /**
     * PROGRAM LIST sheet. Program codes that repeated with different majors
     * (BSED, BEED, MAEd) are split into unique codes since courses.code is
     * unique; near-duplicate rows describing the same program are merged.
     */
    private function seedPrograms()
    {
        if (!Schema::hasTable('courses') || !Schema::hasTable('departments') || !Schema::hasTable('colleges')) {
            $this->command->warn('PlpMasterlistSeeder: courses/departments/colleges tables are missing.');
            return;
        }

        $now = now();
        $collegeIds = DB::table('colleges')->pluck('id', 'code');
        $collegeNames = DB::table('colleges')->pluck('name', 'code');

        // Three legacy departments already correspond to a real college; the
        // remaining four colleges need a fresh department row.
        $legacyDepartmentCodeByCollege = [
            'CCS' => 'CS',
            'CBA' => 'BUS',
            'COED' => 'EDU',
        ];

        $departmentIdByCollege = [];
        foreach ($collegeIds as $collegeCode => $collegeId) {
            $departmentCode = $legacyDepartmentCodeByCollege[$collegeCode] ?? $collegeCode;
            $existingId = (int) DB::table('departments')->where('code', $departmentCode)->value('id');

            if ($existingId) {
                if (Schema::hasColumn('departments', 'college_id')) {
                    DB::table('departments')->where('id', $existingId)->update([
                        'college_id' => $collegeId,
                        'updated_at' => $now,
                    ]);
                }
                $departmentIdByCollege[$collegeCode] = $existingId;
                continue;
            }

            $payload = [
                'code' => $departmentCode,
                'description' => $collegeNames[$collegeCode] ?? $departmentCode,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (Schema::hasColumn('departments', 'college_id')) {
                $payload['college_id'] = $collegeId;
            }

            $departmentIdByCollege[$collegeCode] = (int) DB::table('departments')->insertGetId($payload);
        }

        $programs = [
            ['code' => 'BSHM', 'name' => 'Bachelor of Science in Hospitality Management', 'college' => 'CIHM'],
            ['code' => 'BSN', 'name' => 'Bachelor of Science in Nursing', 'college' => 'CON'],
            ['code' => 'BSBA', 'name' => 'Bachelor of Science in Business Administration major in Marketing Management', 'college' => 'CBA'],
            ['code' => 'BEED', 'name' => 'Bachelor of Elementary Education', 'college' => 'COED'],
            ['code' => 'BSED-ENG', 'name' => 'Bachelor of Secondary Education major in English', 'college' => 'COED'],
            ['code' => 'BSED-FIL', 'name' => 'Bachelor of Secondary Education major in Filipino', 'college' => 'COED'],
            ['code' => 'BSED-MATH', 'name' => 'Bachelor of Secondary Education major in Mathematics', 'college' => 'COED'],
            ['code' => 'BSA', 'name' => 'Bachelor of Science in Accountancy', 'college' => 'CBA'],
            ['code' => 'BSENT', 'name' => 'Bachelor of Science in Entrepreneurship', 'college' => 'CBA'],
            ['code' => 'BSECE', 'name' => 'Bachelor of Science in Electronics and Communications Engineering', 'college' => 'COE'],
            ['code' => 'AB PSYCH', 'name' => 'Bachelor of Arts in Psychology', 'college' => 'CAS'],
            ['code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science', 'college' => 'CCS'],
            ['code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology', 'college' => 'CCS'],
            ['code' => 'ACT', 'name' => 'Associate in Computer Technology', 'college' => 'CCS'],
            ['code' => 'AHRM', 'name' => 'Associate in Hotel and Restaurant Management', 'college' => 'CIHM'],
            ['code' => 'BEED-ECED', 'name' => 'Bachelor of Elementary Education specialization in Early Childhood Education', 'college' => 'COED'],
            ['code' => 'BEED-PRESCHOOL', 'name' => 'Bachelor of Elementary Education with Certificate in Pre-School Education major in General Education', 'college' => 'COED'],
            ['code' => 'BSED-BIO', 'name' => 'Bachelor of Secondary Education major in Biology', 'college' => 'COED'],
            ['code' => 'BSED-COMPED', 'name' => 'Bachelor of Secondary Education major in Computer Education', 'college' => 'COED'],
            ['code' => 'BSIM', 'name' => 'Bachelor of Science in Information Management', 'college' => 'CCS'],
            ['code' => 'BSBA-MGT', 'name' => 'Bachelor of Science in Business Administration major in Management', 'college' => 'CBA'],
            ['code' => 'BSBA-SM', 'name' => 'Bachelor of Science in Business Administration major in Service Management', 'college' => 'CBA'],
            ['code' => 'BSBA-ENT', 'name' => 'Bachelor of Science in Business Administration major in Entrepreneurship', 'college' => 'CBA'],
            ['code' => 'BSHRM', 'name' => 'Bachelor of Science in Hotel and Restaurant Management', 'college' => 'CIHM'],
            ['code' => 'BSMCS', 'name' => 'Bachelor of Science in Mathematics with Computers', 'college' => 'CAS'],
            ['code' => 'CENTREP', 'name' => 'Certificate in Entrepreneurship', 'college' => 'CBA'],
            ['code' => 'CTP', 'name' => 'Certificate in Teaching Profession', 'college' => 'COED'],
            ['code' => 'MAED-LEAD', 'name' => 'Master of Arts in Education major in Educational Leadership', 'college' => 'COED'],
            ['code' => 'MAED-ADMIN', 'name' => 'Master of Arts in Education major in Education Administration', 'college' => 'COED'],
            ['code' => 'MAN', 'name' => 'Master of Arts in Nursing', 'college' => 'CON'],
        ];

        $created = 0;
        $updated = 0;

        foreach ($programs as $program) {
            $collegeCode = $program['college'];
            $departmentId = $departmentIdByCollege[$collegeCode] ?? null;
            if (!$departmentId) {
                continue;
            }

            $payload = [
                'name' => $program['name'],
                'program_type' => 'college',
                'department_id' => $departmentId,
                'description' => $program['name'],
                'non_filipino' => false,
                'program_file' => 'Pending Review',
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('courses', 'college_id')) {
                $payload['college_id'] = $collegeIds[$collegeCode] ?? null;
            }

            $existingId = (int) DB::table('courses')->where('code', $program['code'])->value('id');
            if ($existingId) {
                DB::table('courses')->where('id', $existingId)->update($payload);
                $updated++;
                continue;
            }

            $payload['code'] = $program['code'];
            $payload['slots'] = 50;
            $payload['created_at'] = $now;
            DB::table('courses')->insert($payload);
            $created++;
        }

        $this->command->info("PlpMasterlistSeeder: programs -> {$created} created, {$updated} updated.");
    }

    /**
     * ROOMS sheet (MAIN BUILDING ROOM NO. / LABORATORY ROOM NO. columns).
     * Floor number is derived from the room number's leading digit(s).
     */
    private function seedRooms()
    {
        if (!Schema::hasTable('room_buildings') || !Schema::hasTable('room_hallways') || !Schema::hasTable('rooms')) {
            $this->command->warn('PlpMasterlistSeeder: room tables are missing.');
            return;
        }

        $now = now();

        $buildingId = $this->firstOrCreateLookup('room_buildings', ['name' => 'Main Building']);
        $mainHallwayId = $this->firstOrCreateLookup('room_hallways', [
            'room_building_id' => $buildingId,
            'name' => 'Main Hallway',
        ]);
        $groundsHallwayId = $this->firstOrCreateLookup('room_hallways', [
            'room_building_id' => $buildingId,
            'name' => 'Grounds & Special Facilities',
        ]);

        // [room number, label or null, room type]
        $numberedRooms = [
            [301, 'DRAWING ROOM 1-5TH FLR', 'Laboratory'],
            [302, 'DRAWING ROOM 2-5TH FLR', 'Laboratory'],
            [303, 'LABORATORY 3-5TH FLR', 'Laboratory'],
            [304, 'LAB 1', 'Laboratory'],
            [305, 'LAB 2', 'Laboratory'],
            [306, 'LAB 3', 'Laboratory'],
            [307, 'LAB 4', 'Laboratory'],
            [308, null, 'Lecture Room'], [309, null, 'Lecture Room'], [310, null, 'Lecture Room'],
            [311, null, 'Lecture Room'], [312, null, 'Lecture Room'], [313, null, 'Lecture Room'],
            [314, null, 'Lecture Room'], [315, null, 'Lecture Room'], [317, null, 'Lecture Room'],
            [402, null, 'Lecture Room'], [403, null, 'Lecture Room'], [404, null, 'Lecture Room'],
            [406, null, 'Lecture Room'], [407, null, 'Lecture Room'], [411, null, 'Lecture Room'],
            [501, null, 'Lecture Room'], [502, null, 'Lecture Room'], [503, null, 'Lecture Room'],
            [505, null, 'Lecture Room'],
            [601, null, 'Lecture Room'], [602, null, 'Lecture Room'], [603, null, 'Lecture Room'],
            [604, null, 'Lecture Room'], [605, null, 'Lecture Room'], [606, null, 'Lecture Room'],
            [607, null, 'Lecture Room'], [608, null, 'Lecture Room'],
            [707, null, 'Lecture Room'], [708, null, 'Lecture Room'], [710, null, 'Lecture Room'],
            [711, null, 'Lecture Room'], [712, null, 'Lecture Room'], [713, null, 'Lecture Room'],
            [714, null, 'Lecture Room'], [715, null, 'Lecture Room'], [716, null, 'Lecture Room'],
            [717, null, 'Lecture Room'], [718, null, 'Lecture Room'], [719, null, 'Lecture Room'],
            [720, null, 'Lecture Room'], [721, null, 'Lecture Room'], [722, null, 'Lecture Room'],
        ];

        $created = 0;
        $updated = 0;

        foreach ($numberedRooms as [$number, $label, $type]) {
            $floor = intdiv($number, 100);
            $roomName = $label ?: ('Room ' . $number);
            $capacity = $type === 'Laboratory' ? 30 : 45;
            $roomCode = 'PLP-MB-' . $number;

            $wasCreated = $this->upsertRoom($mainHallwayId, $number, $floor, $capacity, $type, $roomCode, $roomName, $now);
            $wasCreated ? $created++ : $updated++;
        }

        // Named facilities with no numeric room number in the masterlist.
        $specialRooms = [
            [1, 'GYM', 'Gymnasium', 300],
            [2, 'FIELD', 'Field', 500],
            [3, 'INCUBATION ROOM', 'Special Facility', 20],
            [4, 'DEFENSE ROOM', 'Conference Room', 20],
            [5, 'FIELD / FACULTY RM.', 'Faculty Room', 30],
        ];

        foreach ($specialRooms as [$number, $label, $type, $capacity]) {
            $slug = trim((string) preg_replace('/[^A-Z0-9]+/', '-', strtoupper($label)), '-');
            $roomCode = 'PLP-MB-' . $slug;

            $wasCreated = $this->upsertRoom($groundsHallwayId, $number, 0, $capacity, $type, $roomCode, $label, $now);
            $wasCreated ? $created++ : $updated++;
        }

        $this->command->info("PlpMasterlistSeeder: rooms -> {$created} created, {$updated} updated.");
    }

    private function upsertRoom(int $hallwayId, int $roomNumber, int $floor, int $capacity, string $type, string $roomCode, string $roomName, $now): bool
    {
        $existingId = (int) DB::table('rooms')
            ->where('room_hallway_id', $hallwayId)
            ->where('floor_number', $floor)
            ->where('room_number', $roomNumber)
            ->value('id');

        $payload = [
            'room_hallway_id' => $hallwayId,
            'room_number' => $roomNumber,
            'floor_number' => $floor,
            'capacity' => $capacity,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('rooms', 'room_code')) {
            $payload['room_code'] = $roomCode;
        }
        if (Schema::hasColumn('rooms', 'room_name')) {
            $payload['room_name'] = $roomName;
        }
        if (Schema::hasColumn('rooms', 'room_type')) {
            $payload['room_type'] = $type;
        }
        if (Schema::hasColumn('rooms', 'status')) {
            $payload['status'] = 'Active';
        }

        if ($existingId) {
            DB::table('rooms')->where('id', $existingId)->update($payload);
            return false;
        }

        $payload['created_at'] = $now;
        DB::table('rooms')->insert($payload);
        return true;
    }

    /**
     * SIGNATORIES sheet (TOR signatories). University Registrar and
     * Assistant University Registrar map to designations tor.blade.php
     * already reads; the Academic Secretary rows (split by program group
     * in the masterlist) are added as their own designations so they show
     * up in Configuration > Signatories for future use.
     */
    private function seedSignatories()
    {
        if (!Schema::hasTable('system_config_signature_designations') || !Schema::hasTable('system_config_name_signatures')) {
            $this->command->warn('PlpMasterlistSeeder: signature designation tables are missing.');
            return;
        }

        $now = now();

        $designations = [
            ['code' => 'REGISTRAR', 'name' => 'University Registrar', 'signer' => 'MR. FEDERICO G. NUEVA', 'sort_order' => 1],
            ['code' => 'ASSISTANT_REGISTRAR', 'name' => 'Assistant University Registrar', 'signer' => 'MS. JAY ANNE I. SANTOS', 'sort_order' => 2],
            ['code' => 'ACAD_SEC_HEALTH_HOSP', 'name' => 'Academic Secretary - BSHM, BSN, BSMCS, BSHRM, AHRM, MAN', 'signer' => 'MS. JULIE RUTH C. MALABANAN', 'sort_order' => 3],
            ['code' => 'ACAD_SEC_BUSINESS', 'name' => 'Academic Secretary - BSBA', 'signer' => 'MS. ANNLYN A. BENITO', 'sort_order' => 4],
            ['code' => 'ACAD_SEC_EDUCATION', 'name' => 'Academic Secretary - BEED, BSED, CTP, MAEd', 'signer' => 'MS. AIVEE B. DE LA CRUZ', 'sort_order' => 5],
            ['code' => 'ACAD_SEC_ARTS_ACCT_ENG', 'name' => 'Academic Secretary - AB PSYCH, BSA, BSENT, BSECE', 'signer' => 'MS. ELAFLOR F. SILAYAN', 'sort_order' => 6],
            ['code' => 'ACAD_SEC_COMPUTING', 'name' => 'Academic Secretary - BSIT, BSCS, ACT', 'signer' => 'MS. MARILYN D. GARCIA', 'sort_order' => 7],
        ];

        $created = 0;
        $updated = 0;

        foreach ($designations as $row) {
            $designationId = (int) DB::table('system_config_signature_designations')->where('code', $row['code'])->value('id');

            $designationPayload = [
                'name' => $row['name'],
                'sort_order' => $row['sort_order'],
                'updated_at' => $now,
            ];

            if ($designationId) {
                DB::table('system_config_signature_designations')->where('id', $designationId)->update($designationPayload);
            } else {
                $designationPayload['code'] = $row['code'];
                $designationPayload['created_at'] = $now;
                $designationId = (int) DB::table('system_config_signature_designations')->insertGetId($designationPayload);
            }

            $signaturePayload = [
                'signer_name' => $row['signer'],
                'is_active' => true,
                'updated_at' => $now,
            ];

            $existingSignatureId = (int) DB::table('system_config_name_signatures')->where('designation_id', $designationId)->value('id');
            if ($existingSignatureId) {
                DB::table('system_config_name_signatures')->where('id', $existingSignatureId)->update($signaturePayload);
                $updated++;
                continue;
            }

            $signaturePayload['designation_id'] = $designationId;
            $signaturePayload['created_at'] = $now;
            DB::table('system_config_name_signatures')->insert($signaturePayload);
            $created++;
        }

        $this->command->info("PlpMasterlistSeeder: signatories -> {$created} created, {$updated} updated.");
    }

    private function firstOrCreateLookup(string $table, array $criteria): int
    {
        $existingId = (int) DB::table($table)->where($criteria)->value('id');
        if ($existingId) {
            return $existingId;
        }

        $now = now();
        return (int) DB::table($table)->insertGetId(array_merge($criteria, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }
}
