<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaboratoryRoomSampleSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('room_buildings') || !Schema::hasTable('room_hallways') || !Schema::hasTable('rooms') || !Schema::hasTable('subjects')) {
            $this->command->warn('LaboratoryRoomSampleSeeder: required room or subject tables are missing.');
            return;
        }

        $labSubjects = DB::table('subjects')
            ->where(function ($query) {
                $query->where('lab', '>', 0)
                    ->orWhereRaw("TRIM(COALESCE(required_laboratory_room_type, '')) <> ''");
            })
            ->orderBy('course_id')
            ->orderBy('code')
            ->get(['id', 'course_id', 'code', 'name', 'required_laboratory_room_type']);

        if ($labSubjects->isEmpty()) {
            $this->command->warn('LaboratoryRoomSampleSeeder: no laboratory subjects found.');
            return;
        }

        $actorUserId = (int) (User::query()->where('module', 'registrar')->orderBy('id')->value('id')
            ?: User::query()->orderBy('id')->value('id'));

        DB::beginTransaction();
        try {
            $buildingId = $this->firstOrCreateBuilding('Laboratory Building');
            $hallwayId = $this->firstOrCreateHallway($buildingId, 'Laboratory Floor');

            $groups = [
                'Computer Laboratory' => $labSubjects->filter(function ($subject) {
                    return stripos((string) $subject->required_laboratory_room_type, 'science') === false;
                })->values(),
                'Science Laboratory' => $labSubjects->filter(function ($subject) {
                    return stripos((string) $subject->required_laboratory_room_type, 'science') !== false;
                })->values(),
            ];

            $roomCount = 0;
            foreach ($groups as $roomType => $subjects) {
                if ($subjects->isEmpty()) {
                    continue;
                }

                $roomId = $this->upsertRoom($hallwayId, $roomType, $actorUserId);
                $roomCount++;

                if (Schema::hasTable('room_course_assignments')) {
                    foreach ($subjects->pluck('course_id')->map(function ($id) {
                        return (int) $id;
                    })->filter()->unique()->values() as $courseId) {
                        DB::table('room_course_assignments')->updateOrInsert(
                            ['room_id' => $roomId, 'course_id' => $courseId],
                            [
                                'assigned_by_user_id' => $actorUserId > 0 ? $actorUserId : null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }

                if (Schema::hasTable('room_allowed_subjects')) {
                    foreach ($subjects as $subject) {
                        DB::table('room_allowed_subjects')->updateOrInsert(
                            ['room_id' => $roomId, 'subject_id' => (int) $subject->id],
                            [
                                'assigned_by_user_id' => $actorUserId > 0 ? $actorUserId : null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        $this->command->info('LaboratoryRoomSampleSeeder: prepared ' . $roomCount . ' laboratory room(s).');
    }

    private function firstOrCreateBuilding(string $name): int
    {
        $existingId = (int) DB::table('room_buildings')->where('name', $name)->value('id');
        if ($existingId > 0) {
            return $existingId;
        }

        return (int) DB::table('room_buildings')->insertGetId([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function firstOrCreateHallway(int $buildingId, string $name): int
    {
        $existingId = (int) DB::table('room_hallways')
            ->where('room_building_id', $buildingId)
            ->where('name', $name)
            ->value('id');

        if ($existingId > 0) {
            return $existingId;
        }

        return (int) DB::table('room_hallways')->insertGetId([
            'room_building_id' => $buildingId,
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function upsertRoom(int $hallwayId, string $roomType, int $actorUserId): int
    {
        $isScience = $roomType === 'Science Laboratory';
        $roomCode = $isScience ? 'SCI-LAB-AUTO' : 'COMP-LAB-AUTO';
        $roomNumber = $isScience ? 802 : 801;

        $existingId = (int) DB::table('rooms')->where('room_code', $roomCode)->value('id');

        $payload = [
            'room_hallway_id' => $hallwayId,
            'room_number' => $roomNumber,
            'floor_number' => 1,
            'capacity' => 50,
            'updated_by_user_id' => $actorUserId > 0 ? $actorUserId : null,
            'updated_at' => now(),
        ];

        $optional = [
            'room_code' => $roomCode,
            'room_name' => $isScience ? 'Science Laboratory Auto' : 'Computer Laboratory Auto',
            'room_type' => $roomType,
            'available_days' => 'MTWTHFS',
            'available_start_time' => '07:00:00',
            'available_end_time' => '21:00:00',
            'status' => 'Active',
        ];

        foreach ($optional as $column => $value) {
            if (Schema::hasColumn('rooms', $column)) {
                $payload[$column] = $value;
            }
        }

        if ($existingId > 0) {
            DB::table('rooms')->where('id', $existingId)->update($payload);
            return $existingId;
        }

        $payload['created_at'] = now();
        return (int) DB::table('rooms')->insertGetId($payload);
    }
}
