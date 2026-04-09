<?php

use App\Course;
use App\RoomBuilding;
use App\RoomHallway;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HighVolumeRoomFileSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('rooms') || !Schema::hasTable('room_hallways') || !Schema::hasTable('room_course_assignments')) {
            $this->command->warn('HighVolumeRoomFileSeeder: required room tables are missing. Run migrations first.');
            return;
        }

        $this->ensureRoomDimensions();

        $target = $this->resolveTargetCount();
        $existingCount = (int) DB::table('rooms')->count();

        if ($existingCount >= $target) {
            $this->command->info('HighVolumeRoomFileSeeder: target already satisfied (' . $existingCount . '/' . $target . ').');
            return;
        }

        $toCreate = $target - $existingCount;

        $hallwayIds = RoomHallway::query()
            ->orderBy('id')
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();

        if (!count($hallwayIds)) {
            $this->command->warn('HighVolumeRoomFileSeeder: no hallways available.');
            return;
        }

        $courseIds = Course::query()
            ->orderBy('id')
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();

        if (!count($courseIds)) {
            $this->command->warn('HighVolumeRoomFileSeeder: no courses found for room-program assignments.');
            return;
        }

        $actorUserId = (int) (User::query()->where('module', 'registrar')->orderBy('id')->value('id') ?: 0);
        if ($actorUserId <= 0) {
            $actorUserId = (int) (User::query()->orderBy('id')->value('id') ?: 0);
        }

        $existingKeys = [];
        DB::table('rooms')
            ->select('room_hallway_id', 'floor_number', 'room_number')
            ->orderBy('id')
            ->chunk(1000, function ($rows) use (&$existingKeys) {
                foreach ($rows as $row) {
                    $key = (int) $row->room_hallway_id . '|' . (int) $row->floor_number . '|' . (int) $row->room_number;
                    $existingKeys[$key] = true;
                }
            });

        $this->command->info('HighVolumeRoomFileSeeder: creating ' . $toCreate . ' rooms to reach ' . $target . ' total.');
        $this->command->getOutput()->progressStart($toCreate);

        $hallwayCount = count($hallwayIds);
        $courseCount = count($courseIds);
        $created = 0;
        $sequence = 0;

        DB::beginTransaction();
        try {
            while ($created < $toCreate) {
                $hallwayId = $hallwayIds[$sequence % $hallwayCount];
                $floorNumber = (int) (floor($sequence / $hallwayCount) % 20) + 1;
                $roomNumber = (int) floor($sequence / ($hallwayCount * 20)) + 1;
                $sequence++;

                if ($roomNumber > 9999) {
                    break;
                }

                $locationKey = $hallwayId . '|' . $floorNumber . '|' . $roomNumber;
                if (isset($existingKeys[$locationKey])) {
                    continue;
                }

                $timestamp = now();
                $roomId = (int) DB::table('rooms')->insertGetId([
                    'room_hallway_id' => $hallwayId,
                    'room_number' => $roomNumber,
                    'floor_number' => $floorNumber,
                    'capacity' => 30 + (($created + $roomNumber) % 31),
                    'updated_by_user_id' => $actorUserId > 0 ? $actorUserId : null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                $courseId = $courseIds[$created % $courseCount];

                DB::table('room_course_assignments')->insert([
                    'room_id' => $roomId,
                    'course_id' => $courseId,
                    'assigned_by_user_id' => $actorUserId > 0 ? $actorUserId : null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                $existingKeys[$locationKey] = true;
                $created++;
                $this->command->getOutput()->progressAdvance();
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        $this->command->getOutput()->progressFinish();

        if ($created < $toCreate) {
            $this->command->warn('HighVolumeRoomFileSeeder: created ' . $created . ' of ' . $toCreate . ' requested due to uniqueness-space constraints.');
            return;
        }

        $this->command->info('HighVolumeRoomFileSeeder: done. Total room records = ' . ((int) DB::table('rooms')->count()) . '.');
    }

    private function ensureRoomDimensions()
    {
        if (!Schema::hasTable('room_buildings') || !Schema::hasTable('room_hallways')) {
            return;
        }

        $defaultBuildings = ['Campus 1', 'Campus 2', 'Campus 3', 'Campus 4'];

        DB::beginTransaction();
        try {
            foreach ($defaultBuildings as $buildingName) {
                $building = RoomBuilding::query()->firstOrCreate([
                    'name' => $buildingName,
                ]);

                $hasHallway = RoomHallway::query()
                    ->where('room_building_id', $building->id)
                    ->exists();

                if (!$hasHallway) {
                    RoomHallway::query()->create([
                        'room_building_id' => $building->id,
                        'name' => 'Main Hallway',
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    private function resolveTargetCount()
    {
        $defaultTarget = 5000;
        $configPath = base_path('.ultimate-architect.json');

        if (!file_exists($configPath)) {
            return $defaultTarget;
        }

        $raw = file_get_contents($configPath);
        if ($raw === false || trim($raw) === '') {
            return $defaultTarget;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $defaultTarget;
        }

        if (isset($decoded['seeder']) && is_array($decoded['seeder']) && isset($decoded['seeder']['rooms'])) {
            $value = (int) $decoded['seeder']['rooms'];
            if ($value > 0) {
                return $value;
            }
        }

        return $defaultTarget;
    }
}
