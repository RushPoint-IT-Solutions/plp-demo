<?php

declare(strict_types=1);

use App\Services\LegacyDataImporter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MigrateLegacyPlpData extends Migration
{
    public function up()
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('The bundled PLP data migration requires MySQL.');
        }

        $directory = database_path('data/plp');
        $files = [
            'StudentMaster (1).xlsx' => '96bc6381f0a31640f2be6d638f69edad27607edc16747daa461bec63c1ca97d9',
            'Overall Faculties and Employees (1).xlsx' => 'de84f27615b50fcf0f654ed1ed3d88ba76e9f07758a68e0c49b3af75cbf1c01f',
            'StudentAcademicRecords (3).xlsx' => '19d3fba3527c3a1db9f50c9bbfa7170340a59fe015e0481b564586246bc407f3',
        ];
        foreach ($files as $name => $hash) {
            $path = $directory . DIRECTORY_SEPARATOR . $name;
            if (!is_file($path) || hash_file('sha256', $path) !== $hash) {
                throw new RuntimeException('Missing or changed migration data: database/data/plp/' . $name);
            }
        }

        // The importer owns the transaction and preserves existing business records.
        $report = storage_path('app/import-reports/migration-2026-11-06-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)));
        $result = (new LegacyDataImporter())->run($directory, $report, true);
        if (app()->runningInConsole()) {
            echo 'PLP data migration: ' . json_encode($result['counts']) . PHP_EOL;
            echo 'Private validation reports: ' . $report . PHP_EOL;
        }
    }

    public function down()
    {
        throw new RuntimeException('This data migration cannot be automatically reversed. Existing and imported student, employee, and academic records must be preserved.');
    }
}
