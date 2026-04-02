<?php

namespace App\Http\Controllers;


use App\Http\Traits\Translations;
use App\Imports\ReportHyperguestImport;
use App\IntegrationHyperguest;
use App\ReportHyperguest;
use App\ReportHyperguestDetails;
use App\ReportHyperguests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;

class ReportsHyperguestController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:cities.read')->only('index');
        $this->middleware('permission:cities.create')->only('store');
        $this->middleware('permission:cities.update')->only('update');
        $this->middleware('permission:cities.delete')->only('delete');
    }


    public function index(Request $request)
    {

    }

    public function getSearch(Request $request)
    {

        $month = $request->input('month');
        $year = $request->input('year');
        $totals = [
            'hyperguest' => 0,
            'aurora' => 0,
        ];
        $fees = [
            'hyperguest' => 0,
            'aurora' => 0,
        ];
        $fee = 0;
        $reportHyperguest_id = '';

        $reportHyperguest = ReportHyperguests::where('month', $month)->where('year',$year)->first();
        $reportHyperguestDetaills = [];
        if($reportHyperguest){

            $reportHyperguestDetaills = ReportHyperguestDetails::where('report_hyperguest_id', $reportHyperguest->id)->get();

            $totals = [
                'hyperguest' => number_format($reportHyperguest->total_hyperguest,2, '.', ','),
                'aurora' => number_format($reportHyperguest->total_aurora,2, '.', ','),
            ];
            $fees = [
                'hyperguest' => number_format($reportHyperguest->fees_hyperguest,2, '.', ','),
                'aurora' => number_format($reportHyperguest->fees_aurora,2, '.', ','),
            ];

            $fee = $reportHyperguest->fee;
            $reportHyperguest_id = $reportHyperguest->id;
        }

        $inequality = $totals['hyperguest'] != $totals['aurora'] ? true : false;

        return response()->json([
            'reportHyperguest_id' => $reportHyperguest_id,
            'results' => $reportHyperguestDetaills,
            'totals' => $totals,
            'fees' => $fees,
            'fee' => $fee,
            'inequality' =>$inequality

        ],200);


    }

    public function upload(Request $request)
    {
        try {
            // Check if there is a pending or processing import for this period
            $existingProgress = ReportHyperguests::where('month', $request->month)
                ->where('year', $request->year)
                ->whereIn('status', [ReportHyperguests::STATUS_PENDING, ReportHyperguests::STATUS_PROCESSING])
                ->first();

            if ($existingProgress) {
                return response()->json([
                    'type' => 'error',
                    'message' => "Ya hay una importación en proceso para $request->month/$request->year"
                ]);
            }

            // Si ya existe información completada para el mes/año, eliminarla (report + detalles)
            $existingReport = ReportHyperguests::where('month', $request->month)->where('year', $request->year)->first();
            if ($existingReport) {
                ReportHyperguestDetails::where('report_hyperguest_id', $existingReport->id)->forceDelete();
                $existingReport->forceDelete();
            }

            if (!$request->hasFile('file')) {
                throw new \Exception("Debe seleccionar un archivo.");
            }

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $fileName = 'hyperguest_' . $request->month . '_' . $request->year . '_' . time() . '.' . $extension;
            $filePath = $file->storeAs('imports/hyperguest', $fileName);

            // Create report record
            $report = ReportHyperguests::create([
                'month' => $request->month,
                'year' => $request->year,
                'fee' => $request->fee,
                'status' => ReportHyperguests::STATUS_PENDING,
                'file_path' => $filePath
            ]);

            // Dispatch Job
            \App\Jobs\ImportHyperguestJob::dispatch(
                $report->id,
                $filePath,
                $request->month,
                $request->year,
                $request->fee
            );

            return response()->json([
                'type' => 'success',
                'message' => 'Importación iniciada en segundo plano.',
                'report_id' => $report->id
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'type' => 'error',
                'message' => $ex->getMessage()
            ]);
        }
    }

    public function checkStatus(Request $request)
    {
        $report = ReportHyperguests::where('month', $request->month)
            ->where('year', $request->year)
            ->orderBy('id', 'desc')
            ->first();

        if (!$report) {
            return response()->json(['status' => 'NOT_FOUND']);
        }

        return response()->json([
            'id' => $report->id,
            'status' => $report->status,
            'total_rows' => $report->total_rows,
            'processed_rows' => $report->processed_rows,
            'error_message' => $report->error_message,
            'percentage' => $report->total_rows > 0 ? round(($report->processed_rows / $report->total_rows) * 100) : 0
        ]);
    }

    public function recentReports()
    {
        $reports = ReportHyperguests::orderBy('id', 'desc')
            ->take(3)
            ->get()
            ->map(function ($r) {
                $pct = 0;
                if ($r->status === ReportHyperguests::STATUS_COMPLETED) {
                    $pct = 100;
                } elseif ($r->total_rows > 0) {
                    $pct = round(($r->processed_rows / $r->total_rows) * 100);
                }
                return [
                    'id'             => $r->id,
                    'month'          => $r->month,
                    'year'           => $r->year,
                    'status'         => $r->status,
                    'total_rows'     => $r->total_rows,
                    'processed_rows' => $r->processed_rows,
                    'error_message'  => $r->error_message,
                    'percentage'     => $pct,
                    'created_at'     => $r->created_at ? $r->created_at->format('d/m/Y H:i') : null,
                ];
            });

        return response()->json($reports);
    }

    public function deleteAll(Request $request){
        ReportHyperguestDetails::where('report_hyperguest_id', $request->id)->forceDelete();
        ReportHyperguests::find($request->id)->forceDelete();
        return response()->json(['success' => true]);
    }

    public function send_Mail(Request $request){

        try {

            $data = $request->post('dataMail');

            $result=[];
            foreach ($data['destinatarioMail'] as $email) {
               array_push($result,$email['label']);
            }

            $mail = mail::to($result);


            $mail->send(new \App\Mail\SenMailReportHyperguest($data, 'report'));

           return Response::json(['success' => true, $data]);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function listEmail(){

        $hyperguest = IntegrationHyperguest::all();
        $emails=  explode(',', $hyperguest[0]['email_contact']);
        $result=[];
        foreach ($emails as $i => $email) {
            array_push($result,['code'=>$i, 'label'=>$email,'selected'=> false]);

        }

        return response()->json(['success' => true,'result' => $result, 'id'=> $hyperguest[0]['id'],'fee' => $hyperguest[0]['commission_amount']],200);

    }

    public function updateIntegrationsHyperguest(Request $request){


        $id = $request->get('id');

        $hyperguest = IntegrationHyperguest::find($id);

        $result = [];
        foreach ($request->get('emails') as $mail) {
            array_push($result,$mail['label']);
        }

        $emails = implode(',', $result);
        $hyperguest['email_contact'] = $emails;
        $hyperguest->save();

        return response()->json(['success' => true, 'Resgistrado correctamente'],200);
    }

    public function updateFee(Request $request){
        $hyperguest = IntegrationHyperguest::find(1);

        $hyperguest['commission_amount'] = $request->get('fee');
        $hyperguest->save();

        return response()->json(['success' => true, 'Resgistrado correctamente'],200);
    }




}
