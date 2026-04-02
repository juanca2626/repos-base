<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LitoExtensionFileType;
use App\LitoExtensionFilePassenger;
use App\LitoExtensionFile;
use App\LitoExtensionFileLog;
use App\User;
use Illuminate\Support\Facades\DB;

class LitoExtensionController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:aurorax.storage');
    }

    public function search_passenger_files(Request $request, $nroref)
    {
        try
        {
            $type_id = (int) @$request->__get('type_id'); $passenger_id = (int) @$request->__get('passenger_id');

            $files = LitoExtensionFile::with(['passengers', 'type', 'log' => function ($query) {
                    $query->with(['user']);
                    $query->whereIn('action', ['create', 'recover']);
                }, 'logs' => function ($query) {
                    $query->with(['user']);
                    $query->orderBy('id', 'DESC');
                    $query->take(5);
                }])
                ->where(function ($query) {
                    $query->orWhere('entity', '=', 'file');
                    $query->orWhereNull('entity');
                })
                ->where('file', '=', $nroref);

            if($passenger_id > 0)
            {
                $files = $files->whereHas('passengers', function ($query) use ($passenger_id) {
                    $query->where('nrosec', '=', $passenger_id);
                });
            }

            if($type_id > 0)
            {
                $files = $files->where('lito_extension_file_type_id', '=', $type_id);
            }

            $files = $files->get();

            return response()->json([
                'type' => 'success',
                'files' => $files,
                'quantity' => count($files),
            ]);
        }
        catch(\Exception $ex)
        {
            return response()->json($this->throwError($ex));
        }
    }

    public function search_types(Request $request)
    {
        try
        {
            $types = LitoExtensionFileType::all();

            return response()->json([
                'type' => 'success',
                'types' => $types,
            ]);
        }
        catch(\Exception $ex)
        {
            return response()->json($this->throwError($ex));
        }
    }

    public function save_passenger_files(Request $request)
    {
        try
        {
            $passengers = $this->toArray($request->__get('passengers'));
            $nroref = $request->__get('nroref'); $type_id = $request->__get('type_id');
            $information_aditional = $request->__get('information_aditional');
            $flag_hide = (int) @$request->__get('flag_hide');
            $entity = $request->__get('entity');
            $entity = (!empty($entity)) ? $entity : 'file';
            $quote = $request->__get('quote');

            if($request->has('files'))
            {
                $files = $this->toArray($request->__get('files'));

                foreach($files as $_file)
                {
                    $file = new LitoExtensionFile;
                    $file->entity = $entity;
                    $file->quote = (!empty($quote)) ? $quote : '';
                    $file->file = (!empty($nroref)) ? $nroref : 0;
                    $file->link = $_file['file_url']; // Validación para el tema del S3
                    $file->original_name = $_file['name'];
                    $file->flag_hide = $flag_hide;
                    $file->lito_extension_file_type_id = $type_id;

                    if(!empty($information_aditional))
                    {
                        $file->information_aditional = $information_aditional;
                    }

                    $file->save();

                    foreach($passengers as $_passenger)
                    {
                        $passenger = new LitoExtensionFilePassenger;
                        $passenger->lito_extension_file_id = $file->id;
                        $passenger->nrosec = $_passenger;
                        $passenger->save();
                    }

                    $code = @$request->__get('user'); $user = User::where('code', '=', $code)->first();

                    // LOG
                    if($user)
                    {
                        $log = new LitoExtensionFileLog;
                        $log->user_id = $user->id;
                        $log->lito_extension_file_id = $file->id;
                        $log->action = 'create';
                        $log->save();
                    }
                }
            }
            else
            {
                $file_url = $request->__get('file_url'); $filename = $request->__get('file_name');

                $file = new LitoExtensionFile;
                $file->entity = $entity;
                $file->quote = (!empty($quote)) ? $quote : '';
                $file->file = (!empty($nroref)) ? $nroref : 0;
                $file->link = $file_url; // Validación para el tema del S3
                $file->original_name = $filename;
                $file->flag_hide = $flag_hide;
                $file->lito_extension_file_type_id = $type_id;

                if(!empty($information_aditional))
                {
                    $file->information_aditional = $information_aditional;
                }

                $file->save();

                foreach($passengers as $_passenger)
                {
                    $passenger = new LitoExtensionFilePassenger;
                    $passenger->lito_extension_file_id = $file->id;
                    $passenger->nrosec = $_passenger;
                    $passenger->save();
                }

                $code = @$request->__get('user'); $user = User::where('code', '=', $code)->first();

                // LOG
                if($user)
                {
                    $log = new LitoExtensionFileLog;
                    $log->user_id = $user->id;
                    $log->lito_extension_file_id = $file->id;
                    $log->action = 'create';
                    $log->save();
                }
            }


            return response()->json([
                'type' => 'success',
                'message' => 'Archivo guardado correctamente.'
            ]);
        }
        catch(\Exception $ex)
        {
            return response()->json($this->throwError($ex));
        }
    }

    public function delete_passenger_files(Request $request, $file_passenger_id)
    {
        try
        {
            LitoExtensionFile::where('id', '=', $file_passenger_id)->delete();
            $code = @$request->__get('user'); $user = User::where('code', '=', $code)->first();

            // LOG
            if($user)
            {
                $log = new LitoExtensionFileLog;
                $log->user_id = $user->id;
                $log->lito_extension_file_id = $file_passenger_id;
                $log->action = 'trash';
                $log->save();
            }

            return response()->json([
                'type' => 'success',
                'message' => 'Archivo movido a la papelera correctamente.'
            ]);
        }
        catch(\Exception $ex)
        {
            return response()->json($this->throwError($ex));
        }
    }

    public function search_passenger_files_trash(Request $request, $nroref)
    {
        try
        {
            $type_id = (int) @$request->__get('type_id'); $passenger_id = (int) @$request->__get('passenger_id');
            $files = LitoExtensionFile::with(['passengers', 'type', 'log' => function ($query) {
                    $query->with(['user']);
                    $query->where('action', '=', 'trash');
                }, 'logs' => function ($query) {
                        $query->with(['user']);
                        $query->orderBy('id', 'DESC');
                        $query->take(5);
                }])
                ->where(function ($query) {
                    $query->orWhere('entity', '=', 'file');
                    $query->orWhereNull('entity');
                })
                ->where('file', '=', $nroref);

            if($passenger_id > 0)
            {
                $files = $files->whereHas('passengers', function ($query) use ($passenger_id) {
                    $query->where('nrosec', '=', $passenger_id);
                });
            }

            if($type_id > 0)
            {
                $files = $files->where('lito_extension_file_type_id', '=', $type_id);
            }

            $files = $files->withTrashed()
                ->whereNotNull('deleted_at')
                ->whereNull('permanently_deleted_at')
                ->get();

            return response()->json([
                'type' => 'success',
                'files' => $files,
                'quantity' => count($files),
            ]);
        }
        catch(\Exception $ex)
        {
            return response()->json($this->throwError($ex));
        }
    }

    public function delete_passenger_files_trash(Request $request, $file_passenger_id)
    {
        try
        {
            DB::table('lito_extension_files')->where('id', '=', $file_passenger_id)->update([
                'permanently_deleted_at' => date("Y-m-d H:i:s"),
            ]);
            $code = @$request->__get('user'); $user = User::where('code', '=', $code)->first();

            // LOG
            if($user)
            {
                $log = new LitoExtensionFileLog;
                $log->user_id = $user->id;
                $log->lito_extension_file_id = $file_passenger_id;
                $log->action = 'delete';
                $log->save();
            }

            return response()->json([
                'type' => 'success',
                'message' => 'Archivo eliminado correctamente.'
            ]);
        }
        catch(\Exception $ex)
        {
            return response()->json($this->throwError($ex));
        }
    }

    public function recover_passenger_file(Request $request, $file_passenger_id)
    {
        try
        {
            DB::table('lito_extension_files')->where('id', '=', $file_passenger_id)->update([
                'deleted_at' => null
            ]);
            $code = @$request->__get('user'); $user = User::where('code', '=', $code)->first();

            // LOG
            if($user)
            {
                $log = new LitoExtensionFileLog;
                $log->user_id = $user->id;
                $log->lito_extension_file_id = $file_passenger_id;
                $log->action = 'recover';
                $log->save();
            }

            return response()->json([
                'type' => 'success',
                'message' => 'Archivo recuperado correctamente.'
            ]);
        }
        catch(\Exception $ex)
        {
            return response()->json($this->throwError($ex));
        }
    }
}
