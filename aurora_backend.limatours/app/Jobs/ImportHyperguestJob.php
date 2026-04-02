<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\ReportHyperguests;
use App\Imports\ReportHyperguestImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImportHyperguestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportId;
    protected $filePath;
    protected $month;
    protected $year;
    protected $fee;

    /**
     * Create a new job instance.
     *
     * @param int $reportId
     * @param string $filePath
     * @param string $month
     * @param string $year
     * @param float $fee
     * @return void
     */
    public function __construct($reportId, $filePath, $month, $year, $fee)
    {
        $this->reportId = $reportId;
        $this->filePath = $filePath;
        $this->month = $month;
        $this->year = $year;
        $this->fee = $fee;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $report = ReportHyperguests::find($this->reportId);

        if (!$report) {
            return;
        }

        $report->update(['status' => ReportHyperguests::STATUS_PROCESSING]);

        try {
            $path = storage_path('app/' . $this->filePath);

            if (!file_exists($path)) {
                throw new \Exception("Archivo no encontrado en: " . $path);
            }

            $import = new ReportHyperguestImport($this->month, $this->year, $this->fee, $this->reportId);
            $import->import($path);

            $report->update([
                'status' => ReportHyperguests::STATUS_COMPLETED,
                'error_message' => null
            ]);

            // Optional: delete file after import
            // Storage::delete($this->filePath);

        } catch (\Exception $e) {
            $report->update([
                'status' => ReportHyperguests::STATUS_FAILED,
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
