<?php

namespace App\Http\Controllers;

use App\ChannelHotel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ExcelCodeAuroraController extends Controller
{
    /**
     * Procesa un archivo Excel y devuelve todos los datos de todas las pestañas
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function processExcel(Request $request): JsonResponse
    {
        try {
            // Validar que se haya enviado un archivo
            if (!$request->hasFile('excel_file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se ha enviado ningún archivo Excel',
                    'data' => null
                ], 400);
            }

            $file = $request->file('excel_file');

            // Validar que sea un archivo Excel válido
            $allowedMimes = [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/csv'
            ];

            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo debe ser un Excel (.xls, .xlsx) o CSV',
                    'data' => null
                ], 400);
            }

            // Obtener información del archivo
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $fileMimeType = $file->getMimeType();

            // Procesar el archivo Excel
            $excelData = $this->readExcelFile($file);

            // Reorganizar los datos según el formato solicitado
            $formattedSheets = [];
            foreach ($excelData as $sheetName => $sheetInfo) {
                $formattedSheets = array_merge($formattedSheets, $sheetInfo['data']);
            }

            // Procesar los datos de los canales
            $this->processChannelData($formattedSheets);

            return response()->json([
                'success' => true,
                'message' => 'Archivo Excel procesado exitosamente',
                'data' => [
                    'file_info' => [
                        'name' => $fileName,
                        'size' => $fileSize,
                        'mime_type' => $fileMimeType,
                        'sheets_count' => count($excelData)
                    ],
                    'sheets' => $formattedSheets
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo Excel: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Lee el archivo Excel y extrae todos los datos de todas las pestañas
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return array
     */
    private function readExcelFile($file): array
    {
        $filePath = $file->getPathname();
        $fileExtension = strtolower($file->getClientOriginalExtension());

        $spreadsheet = null;

        // Determinar el tipo de archivo y crear el reader apropiado
        switch ($fileExtension) {
            case 'xlsx':
                $reader = new Xlsx();
                break;
            case 'xls':
                $reader = new Xls();
                break;
            case 'csv':
                $reader = new Csv();
                break;
            default:
                throw new \Exception('Formato de archivo no soportado');
        }

        // Cargar el archivo
        $spreadsheet = $reader->load($filePath);

        $sheetsData = [];
        $sheetNames = $spreadsheet->getSheetNames();

        // Procesar cada pestaña
        foreach ($sheetNames as $sheetName) {
            $worksheet = $spreadsheet->getSheetByName($sheetName);
            $sheetData = $this->extractSheetData($worksheet, $sheetName);

            $sheetsData[$sheetName] = [
                'sheet_name' => $sheetName,
                'rows_count' => count($sheetData),
                'data' => $sheetData
            ];
        }

        return $sheetsData;
    }

    /**
     * Extrae los datos de una pestaña específica (solo columnas 1 y 12)
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet
     * @param string $sheetName
     * @return array
     */
    private function extractSheetData($worksheet, $sheetName): array
    {
        $data = [];
        $highestRow = $worksheet->getHighestRow();

        // Leer todas las filas, pero solo las columnas 1 y 12 (empezando desde la fila 2 para omitir cabecera)
        for ($row = 2; $row <= $highestRow; $row++) {
            // Obtener valor de la columna 1 (property_id)
            $propertyId = $worksheet->getCellByColumnAndRow(1, $row)->getCalculatedValue();

            // Obtener valor de la columna 12 (cod_aurora)
            $codAurora = $worksheet->getCellByColumnAndRow(12, $row)->getCalculatedValue();

            // Convertir fechas a formato legible si es necesario
            if ($propertyId instanceof \DateTime) {
                $propertyId = $propertyId->format('Y-m-d H:i:s');
            }
            if ($codAurora instanceof \DateTime) {
                $codAurora = $codAurora->format('Y-m-d H:i:s');
            }

            // Solo agregar filas que tengan cod_aurora válido (no null y no vacío)
            if ($codAurora !== null && $codAurora !== '') {
                $data[] = [
                    'property_id' => $propertyId,
                    'cod_aurora' => $codAurora,
                    'sheet_name' => $sheetName
                ];
            }
        }

        return $data;
    }

    /**
     * Endpoint alternativo que usa la librería Maatwebsite/Excel
     *
     * @param Request $request
     * @return JsonResponse
     */
    // public function processExcelWithMaatwebsite(Request $request): JsonResponse
    // {
    //     try {
    //         // Validar que se haya enviado un archivo
    //         if (!$request->hasFile('excel_file')) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'No se ha enviado ningún archivo Excel',
    //                 'data' => null
    //             ], 400);
    //         }

    //         $file = $request->file('excel_file');

    //         // Usar Maatwebsite/Excel para leer el archivo
    //         $data = Excel::toArray([], $file);

    //         $formattedSheets = [];

    //         // Procesar cada pestaña
    //         foreach ($data as $sheetIndex => $sheetData) {
    //             $sheetName = 'Sheet' . ($sheetIndex + 1);

    //             // Extraer solo columnas 1 y 12 de cada fila (omitiendo la primera fila que es cabecera)
    //             foreach ($sheetData as $rowIndex => $row) {
    //                 // Omitir la primera fila (índice 0) que es la cabecera
    //                 if ($rowIndex === 0) {
    //                     continue;
    //                 }

    //                 $propertyId = isset($row[0]) ? $row[0] : null; // Columna 1
    //                 $codAurora = isset($row[11]) ? $row[11] : null; // Columna 12 (índice 11)

    //                 // Solo agregar filas que tengan cod_aurora válido (no null y no vacío)
    //                 if ($codAurora !== null && $codAurora !== '') {
    //                     $formattedSheets[] = [
    //                         'property_id' => $propertyId,
    //                         'cod_aurora' => $codAurora,
    //                         'sheet_name' => $sheetName
    //                     ];
    //                 }
    //             }
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Archivo Excel procesado exitosamente con Maatwebsite/Excel',
    //             'data' => [
    //                 'file_info' => [
    //                     'name' => $file->getClientOriginalName(),
    //                     'size' => $file->getSize(),
    //                     'mime_type' => $file->getMimeType(),
    //                     'sheets_count' => count($data)
    //                 ],
    //                 'sheets' => $formattedSheets
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error al procesar el archivo Excel: ' . $e->getMessage(),
    //             'data' => null
    //         ], 500);
    //     }
    // }

    /**
     * Procesa los datos de los canales obtenidos del Excel
     *
     * @param array $formattedSheets
     * @return void
     */
    private function processChannelData(array $formattedSheets): void
    {
        foreach ($formattedSheets as $sheet) {
            $channel = ChannelHotel::where('code', $sheet['property_id'])
                ->where('channel_id', 6)
                ->where('type', 2)
                ->first();

            if ($channel) {
                $channelAurora = ChannelHotel::where('channel_id', 1)
                    ->where('hotel_id', $channel->hotel_id)
                    ->first();

                if ($channelAurora) {
                    $channelAurora->update([
                        'code' => $sheet['cod_aurora']
                    ]);
                } else {
                    $channelAurora = new ChannelHotel();
                    $channelAurora->channel_id = 1;
                    $channelAurora->hotel_id = $channel->hotel_id;
                    $channelAurora->code = $sheet['cod_aurora'];
                    $channelAurora->save();
                }
            }
        }
    }
}
