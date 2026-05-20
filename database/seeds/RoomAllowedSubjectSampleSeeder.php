<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoomAllowedSubjectSampleSeeder extends Seeder
{
    private const SUBJECTS_PER_ROOM = 4;

    public function run()
    {
        if (!Schema::hasTable('rooms') || !Schema::hasTable('subjects') || !Schema::hasTable('room_allowed_subjects')) {
            $this->command->warn('RoomAllowedSubjectSampleSeeder: required tables are missing.');
            return;
        }

        $rooms = DB::table('rooms')
            ->orderBy('floor_number')
            ->orderBy('room_number')
            ->orderBy('id')
            ->get(['id']);

        if ($rooms->isEmpty()) {
            $this->command->warn('RoomAllowedSubjectSampleSeeder: no rooms found.');
            return;
        }

        $subjectQuery = DB::table('subjects')
            ->select('id', 'course_id')
            ->orderBy('course_id')
            ->orderBy('code')
            ->orderBy('name')
            ->orderBy('id');

        if (Schema::hasColumn('subjects', 'is_subject_file_record')) {
            $subjectQuery->where(function ($query) {
                $query->whereNull('is_subject_file_record')
                    ->orWhere('is_subject_file_record', 1)
                    ->orWhere('is_subject_file_record', true);
            });
        }

        $subjects = $subjectQuery->get();

        if ($subjects->isEmpty()) {
            $this->command->warn('RoomAllowedSubjectSampleSeeder: no subjects found.');
            return;
        }

        $subjectsByCourse = $subjects
            ->filter(function ($subject) {
                return (int) ($subject->course_id ?? 0) > 0;
            })
            ->groupBy(function ($subject) {
                return (int) $subject->course_id;
            });

        $allSubjectIds = $subjects
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();

        $roomCourseIds = Schema::hasTable('room_course_assignments')
            ? DB::table('room_course_assignments')
                ->select('room_id', 'course_id')
                ->orderBy('room_id')
                ->orderBy('course_id')
                ->get()
                ->groupBy('room_id')
                ->map(function ($rows) {
                    return $rows->pluck('course_id')
                        ->map(function ($id) {
                            return (int) $id;
                        })
                        ->filter()
                        ->values()
                        ->all();
                })
            : collect();

        $actorUserId = (int) (User::query()->where('module', 'registrar')->orderBy('id')->value('id')
            ?: User::query()->orderBy('id')->value('id'));

        $created = 0;
        $timestamp = now();

        DB::beginTransaction();
        try {
            foreach ($rooms->values() as $roomIndex => $room) {
                $candidateIds = [];
                $courseIds = $roomCourseIds->get($room->id, []);

                foreach ($courseIds as $courseId) {
                    foreach (($subjectsByCourse->get($courseId) ?: collect()) as $subject) {
                        $candidateIds[] = (int) $subject->id;
                    }
                }

                if (!count($candidateIds)) {
                    $candidateIds = $allSubjectIds;
                }

                $candidateIds = array_values(array_unique($candidateIds));
                $selectedSubjectIds = $this->rotatingSlice($candidateIds, (int) $roomIndex, self::SUBJECTS_PER_ROOM);

                foreach ($selectedSubjectIds as $subjectId) {
                    $before = DB::table('room_allowed_subjects')
                        ->where('room_id', (int) $room->id)
                        ->where('subject_id', (int) $subjectId)
                        ->exists();

                    DB::table('room_allowed_subjects')->updateOrInsert(
                        [
                            'room_id' => (int) $room->id,
                            'subject_id' => (int) $subjectId,
                        ],
                        [
                            'assigned_by_user_id' => $actorUserId > 0 ? $actorUserId : null,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ]
                    );

                    if (!$before) {
                        $created++;
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        $this->command->info('RoomAllowedSubjectSampleSeeder: added ' . $created . ' allowed-subject sample links.');
    }

    private function rotatingSlice(array $ids, int $offset, int $limit): array
    {
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
