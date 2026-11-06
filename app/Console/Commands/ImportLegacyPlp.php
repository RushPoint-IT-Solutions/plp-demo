<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\LegacyDataImporter;
use Illuminate\Console\Command;

class ImportLegacyPlp extends Command
{
    protected $signature = 'plp:import-legacy {directory? : XLSX directory (defaults to storage/app/imports/plp)} {--commit : Write validated records in one transaction} {--report= : Private report directory}';
    protected $description = 'Validate and migrate legacy students, employees and historical academic records (preview by default)';

    public function handle(): int
    {
        $report = $this->option('report') ?: storage_path('app/import-reports/' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)));
        try {
            if (\Illuminate\Support\Facades\DB::connection()->getDriverName() !== 'mysql') {
                throw new \RuntimeException('This import requires the configured MySQL connection (DB_CONNECTION=mysql).');
            }
            $result = (new LegacyDataImporter())->run($this->argument('directory') ?: storage_path('app/imports/plp'), $report, (bool) $this->option('commit'));
            $this->info($result['mode'] === 'commit' ? 'Import committed.' : 'Validation complete. No database records written.');
            $this->line(json_encode($result['counts'], JSON_PRETTY_PRINT));
            $this->line('Private reports: ' . $report);
            return 0;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
