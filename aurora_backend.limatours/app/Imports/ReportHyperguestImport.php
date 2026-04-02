<?php

namespace App\Imports;

use App\ReportHyperguests;
use App\ReportHyperguestDetails;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Validators\Failure;
use App\ReservationsHotelsRatesPlansRooms;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\RegistersEventListeners; 

class ReportHyperguestImport implements ToModel, WithStartRow, WithEvents, SkipsOnError, WithCalculatedFormulas, WithChunkReading
{
    use Importable, RegistersEventListeners, SkipsErrors, SkipsFailures;

    private static $month = null;
    private static $year = null;
    private static $fee = null;
    private static $report_hyperguest_id = null;

    public function __construct($month, $year, $fee, $report_hyperguest_id = null)
    {
        self::$month = $month;
        self::$year = $year;
        self::$fee = $fee;
        self::$report_hyperguest_id = $report_hyperguest_id;
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    public static function beforeImport(BeforeImport $event)
    {
        $totalRows = $event->getReader()->getTotalRows();
        $sheetName = array_keys($totalRows)[0];
        $totalCount = $totalRows[$sheetName] - 1; // Assuming 1 header row

        if (!self::$report_hyperguest_id) {
            $reportHyperguests = ReportHyperguests::create([
                'month' => self::$month,
                'year' => self::$year,
                'fee' => self::$fee,
                'status' => ReportHyperguests::STATUS_PROCESSING,
                'total_rows' => $totalCount,
                'processed_rows' => 0
            ]);
            self::$report_hyperguest_id = $reportHyperguests->id;
        } else {
            ReportHyperguests::where('id', self::$report_hyperguest_id)->update([
                'total_rows' => $totalCount,
                'processed_rows' => 0
            ]);
        }
    }

    public static function afterImport(AfterImport $event)
    {
        $reportHyperguests = ReportHyperguests::find(self::$report_hyperguest_id);

        if ($reportHyperguests) {
            $results = ReportHyperguestDetails::where('report_hyperguest_id', self::$report_hyperguest_id)->get();

            $fee = self::$fee;
            $totals = ['hyperguest' => 0, 'aurora' => 0];
            $fees = ['hyperguest' => 0, 'aurora' => 0];

            foreach ($results as $result) {
                $totals['hyperguest'] += $result->price_amount;
                $fees['hyperguest'] += $result->fees_hyperguest;
                if ($result->file_code && $result->status_aurora == 1) {
                    $totals['aurora'] += $result->price_aurora;
                    $fees['aurora'] += $result->fees_aurora;
                }
            }

            $reportHyperguests->update([
                'total_hyperguest' => $totals['hyperguest'],
                'fees_hyperguest' => $fees['hyperguest'],
                'total_aurora' => $totals['aurora'],
                'fees_aurora' => $fees['aurora'],
                'status' => ReportHyperguests::STATUS_COMPLETED,
                'processed_rows' => $reportHyperguests->total_rows // Ensure it's 100%
            ]);
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if ($row['0'] != '') {
            // Echo for debugging if needed (will show in worker log)
            // echo "Fila procesada: {$row[0]}\n";

            ReportHyperguestDetails::withTrashed()->where('booking_id', $row['0'])->forceDelete();

            $fee = self::$fee;
            $reservations = ReservationsHotelsRatesPlansRooms::with('reservation_hotel.reservation')
                ->where('channel_reservation_code_master', $row['0'])->get();

            $price_aurora = 0;
            $status_aurora = 0;
            $reservations_hotels_rates_plans_room_ids = [];
            $file_code = '';
            $fees_aurora = 0;

            if ($reservations->isNotEmpty()) {
                foreach ($reservations as $reservation) {
                    $file_code = $reservation->reservation_hotel->reservation->file_code;
                    $price_aurora += $reservation->total_amount_base;
                    $status_aurora = $reservation->status;
                    $reservations_hotels_rates_plans_room_ids[] = $reservation->id;
                }
                $fees_aurora = $price_aurora * ($fee / 100);
            }

            // Increment processed rows
            ReportHyperguests::where('id', self::$report_hyperguest_id)->increment('processed_rows');

            return new ReportHyperguestDetails([
                'report_hyperguest_id' => self::$report_hyperguest_id,
                'booking_id' => $row['0'],
                'property_name' => $row['1'],
                'property_id' => $row['2'],
                'property_city' => $row['3'],
                'property_country' => $row['4'],
                'booking_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['5']),
                'start_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['6']),
                'end_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['7']),
                'lead_guest_name' => $row['8'],
                'price_amount' => $row['9'],
                'fees_hyperguest' => $row['10'],
                'price_currency' => $row['11'],
                'agency_reference' => $row['12'],
                'file_code' => $file_code,
                'status_aurora' => $status_aurora,
                'price_aurora' => $row['9'],
                'reservations_hotels_rates_plans_room_ids' => implode(",", $reservations_hotels_rates_plans_room_ids),
                'fees_aurora' => $row['10'],
                'status' => 'Confirmed',
            ]);
        }
        return null;
    }

    /**
     * Inserta en bloque lo acumulado en buffer.
     * Llamado en dos momentos:
     *  - Cuando el buffer llega al tamaño de chunk
     *  - Al final del import (via destructor)
     */
    private function flushBuffer(): void
    {
        if (empty($this->buffer)) {
            return;
        }

        // Insert por bloques para no exceder el límite de parámetros
        DB::transaction(function () {
            foreach (array_chunk($this->buffer, self::BULK_CHUNK_SIZE) as $chunk) {
                ReportHyperguestDetails::insert($chunk);
            }
        });

        // Limpiar buffer después de insertar
        $this->buffer = [];
    }

    /**
     * Asegura que si el objeto se destruye (fin del import),
     * cualquier resto en buffer se inserte.
     */
    public function __destruct()
    {
        // Último flush para asegurar insert masivo final
        $this->flushBuffer();
    }

    public function onError(\Throwable $e)
    {
        $reportHyperguests = ReportHyperguests::find(self::$report_hyperguest_id);
        if ($reportHyperguests) {
            $reportHyperguests->update([
                'status' => ReportHyperguests::STATUS_FAILED,
                'error_message' => $e->getMessage()
            ]);
        }
        $this->failures[] = new Failure(0, '', [$e->getMessage()], []);
    }
}
