<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BuildingRoomSampleSeeder extends Seeder
{
    private const BUILDING_COUNT = 2;
    private const FLOORS_PER_BUILDING = 3;
    private const ROOMS_PER_FLOOR = 10;
    private const SUBJECTS_PER_ROOM = 4;

    public function run()
    {
        if (!Schema::hasTable('room_buildings') || !Schema::hasTable('room_hallways') || !Schema::hasTable('rooms')) {
            $this->command->warn('BuildingRoomSampleSeeder: required room tables are missing.');
            return;
        }

        $actorUserId = (int) (User::query()->where('module', 'registrar')->orderBy('id')->value('id')
            ?: User::query()->orderBy('id')->value('id'));

        $courseIds = Schema::hasTable('courses')
            ? DB::table('courses')->orderBy('id')->pluck('id')->map(function ($id) {
                return (int) $id;
            })->filter()->values()->all()
            : [];

        $subjectIdsByCourse = $this->subjectIdsByCourse();
        $allSubjectIds = $this->allSubjectIds();

        $created = 0;
        $updated = 0;
        $roomIndex = 0;

        DB::beginTransaction();
        try {
            for ($buildingNo = 1; $buildingNo <= self::BUILDING_COUNT; $buildingNo++) {
                $buildingId = $this->firstOrCreateBuilding('Building ' . $buildingNo);
                $hallwayId = $this->firstOrCreateHallway($buildingId, 'Main Hallway');

                for ($floorNo = 1; $floorNo <= self::FLOORS_PER_BUILDING; $floorNo++) {
                    for ($roomNo = 1; $roomNo <= self::ROOMS_PER_FLOOR; $roomNo++) {
                        $roomNumber = ($floorNo * 100) + $roomNo;
                        $roomCode = 'B' . $buildingNo . '-' . $roomNumber;

                        $roomId = $this->upsertRoom($hallwayId, $buildingNo, $floorNo, $roomNumber, $roomCode, $actorUserId);
                        $wasCreated = $this->wasRecentlyCreatedRoom($hallwayId, $floorNo, $roomNumber);
                        $created += $wasCreated ? 1 : 0;
                        $updated += $wasCreated ? 0 : 1;

                        $courseId = count($courseIds) ? $courseIds[$roomIndex % count($courseIds)] : 0;
                        if ($courseId > 0 && Schema::hasTable('room_course_assignments')) {
                            $this->upsertRoomCourse($roomId, $courseId, $actorUserId);
                        }

                        if (Schema::hasTable('room_allowed_subjects')) {
                            $candidateSubjectIds = $courseId > 0 && isset($subjectIdsByCourse[$courseId])
                                ? $subjectIdsByCourse[$courseId]
                                : $allSubjectIds;

                            foreach ($this->rotatingSlice($candidateSubjectIds, $roomIndex, self::SUBJECTS_PER_ROOM) as $subjectId) {
                                $this->upsertRoomSubject($roomId, (int) $subjectId, $actorUserId);
                            }
                        }

                        $roomIndex++;
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        $this->command->info('BuildingRoomSampleSeeder: created ' . $created . ' rooms and refreshed ' . $updated . ' existing sample rooms.');
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

    private function upsertRoom(int $hallwayId, int $buildingNo, int $floorNo, int $roomNumber, string $roomCode, int $actorUserId): int
    {
        $existingId = (int) DB::table('rooms')
            ->where('room_hallway_id', $hallwayId)
            ->where('floor_number', $floorNo)
            ->where('room_number', $roomNumber)
            ->value('id');

        $payload = [
            'room_hallway_id' => $hallwayId,
            'room_number' => $roomNumber,
            'floor_number' => $floorNo,
            'capacity' => 40 + (($buildingNo + $floorNo + $roomNumber) % 11),
            'updated_by_user_id' => $actorUserId > 0 ? $actorUserId : null,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('rooms', 'room_code')) {
            $payload['room_code'] = $roomCode;
        }
        if (Schema::hasColumn('rooms', 'room_name')) {
            $payload['room_name'] = 'Room ' . $roomNumber;
        }
        if (Schema::hasColumn('rooms', 'room_type')) {
            if ($floorNo === 3) {
                $payload['room_type'] = $roomNumber % 2 === 0 ? 'Science Laboratory' : 'Computer Laboratory';
            } else {
                $payload['room_type'] = 'Lecture Room';
            }
        }
        if (Schema::hasColumn('rooms', 'available_days')) {
            $payload['available_days'] = 'MTWTHFS';
        }
        if (Schema::hasColumn('rooms', 'available_start_time')) {
            $payload['available_start_time'] = '07:00:00';
        }
        if (Schema::hasColumn('rooms', 'available_end_time')) {
            $payload['available_end_time'] = '21:00:00';
        }
        if (Schema::hasColumn('rooms', 'status')) {
            $payload['status'] = 'Active';
        }

        if ($existingId > 0) {
            DB::table('rooms')->where('id', $existingId)->update($payload);
            return $existingId;
        }

        $payload['created_at'] = now();
        return (int) DB::table('rooms')->insertGetId($payload);
    }

    private function wasRecentlyCreatedRoom(int $hallwayId, int $floorNo, int $roomNumber): bool
    {
        $room = DB::table('rooms')
            ->where('room_hallway_id', $hallwayId)
            ->where('floor_number', $floorNo)
            ->where('room_number', $roomNumber)
            ->first(['created_at', 'updated_at']);

        return $room && (string) $room->created_at === (string) $room->updated_at;
    }

    private function upsertRoomCourse(int $roomId, int $courseId, int $actorUserId): void
    {
        DB::table('room_course_assignments')->updateOrInsert(
            ['room_id' => $roomId, 'course_id' => $courseId],
            [
                'assigned_by_user_id' => $actorUserId > 0 ? $actorUserId : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function upsertRoomSubject(int $roomId, int $subjectId, int $actorUserId): void
    {
        DB::table('room_allowed_subjects')->updateOrInsert(
            ['room_id' => $roomId, 'subject_id' => $subjectId],
            [
                'assigned_by_user_id' => $actorUserId > 0 ? $actorUserId : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function subjectIdsByCourse(): array
    {
        if (!Schema::hasTable('subjects')) {
            return [];
        }

        return $this->baseSubjectQuery()
            ->whereNotNull('course_id')
            ->get()
            ->groupBy('course_id')
            ->map(function ($subjects) {
                return $subjects->pluck('id')->map(function ($id) {
                    return (int) $id;
                })->values()->all();
            })
            ->all();
    }

    private function allSubjectIds(): array
    {
        if (!Schema::hasTable('subjects')) {
            return [];
        }

        return $this->baseSubjectQuery()
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();
    }

    private function baseSubjectQuery()
    {
        $query = DB::table('subjects')
            ->select('id', 'course_id')
            ->orderBy('course_id')
            ->orderBy('code')
            ->orderBy('name')
            ->orderBy('id');

        if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
            $query->where(function ($builder) {
                $builder->whereNull('is_subject_file_record')
                    ->orWhere('is_subject_file_record', 1)
                    ->orWhere('is_subject_file_record', true);
            });
        }

        return $query;
    }

    private function rotatingSlice(array $ids, int $offset, int $limit): array
    {
        $ids = array_values(array_unique(array_filter($ids)));
        $count = count($ids);

        if ($count <= $limit) {
            return $ids;
        }

        $selected = [];
        $start = ($offset * $limit) % $count;

        for ($i = 0; $i < $limit; $i++) {
            $selected[] = $ids[($start + $i) % $count];
        }

        return $selected;
    }
}
