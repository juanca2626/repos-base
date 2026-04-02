<?php

namespace App\Http\Controllers;

use App\Exports\ReportsExport;
use App\Imports\OperabilityImport;
use App\Notification;
use App\Reminder;
use App\Role;
use App\User;
use App\UserMarket;
use App\UserNotification;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $galeries = DB::table('galeries')->where(function ($query) {
            $query->orWhere('url', 'like', '%http:%')->orWhere('url', 'like', '%https:%');
        })->get();

        foreach ($galeries as $key => $value) {
            $url = str_replace(['http://', 'https://'], '//', $value->url);
            DB::table('galeries')
                ->where('id', $value->id)
                ->update(['url' => $url]);
        }

        $packages = DB::table('packages')->where(function ($query) {
            $query->orWhere('map_link', 'like', 'http:%')->orWhere('map_link', 'like', 'https:%');
        })->orWhere(function ($query) {
            $query->orWhere('image_link', 'like', 'http:%')->orWhere('image_link', 'like', 'https:%');
        })->get();

        foreach ($packages as $key => $value) {
            $map_link = str_replace(['http://', 'https://'], '//', $value->map_link);
            $image_link = str_replace(['http://', 'https://'], '//', $value->image_link);

            DB::table('packages')
                ->where('id', $value->id)
                ->update(['map_link' => $map_link, 'image_link' => $image_link]);
        }

        $user = Auth::user();
        $rol = Role::find($user->rol)->first();
        $rol = strtolower($rol->name);

        switch ($user->user_type_id) {
            case 3:
                if ($this->hasPermission('board.boss') && (!strpos($rol, 'jefe') === false or !strpos(
                    $rol,
                    'supervisor'
                ) === false)) {
                    return redirect()->to('/report_orders');
                } else {
                    return redirect()->to('/board');
                }
                break;
            case 1:
            case 4:
                //                if ($this->hasPermission('mfpackages.read')) {
                //                    return redirect()->to('/packages');
                //                } elseif ($this->hasPermission('mfservices.read')) {
                //                    return redirect()->to('/services');
                //                }elseif ($this->hasPermission('mfhotels.read')) {
                //                    return redirect()->to('/hotels');
                //                }elseif ($this->hasPermission('mfquotationboard.read')) {
                //                    return redirect()->to('/packages/cotizacion');
                //                }else{
                //                    return view('home');
                //                }
                return redirect()->to('/packages');
                break;
            default:
                return redirect()->to('/packages');
                break;
        }
    }

    public function search_markets(Request $request)
    {
        $markets = UserMarket::where('user_id', '=', Auth::user()->id)->with('markets')->get();
        return response()->json(['markets' => $markets]);
    }

    public function account(Request $request)
    {
        return view('account');
    }

    public function change_password(Request $request)
    {
        $password = $request->__get('password');
        $new_password = $request->__get('new_password');
        $confirm_password = $request->__get('confirm_password');

        if (Hash::check($password, Auth::user()->password)) {
            if ($new_password != '' and $new_password != null) {
                if ($new_password == $confirm_password) {
                    $user = Auth::user();
                    $user->password = bcrypt($new_password);
                    $user->save();

                    $message = ['type' => 'success', 'content' => 'Contraseña actualizada correctamente.'];
                } else {
                    $message = [
                        'type' => 'warning',
                        'content' => 'La confirmación de contraseña no coincide con la nueva contraseña. Por favor, revise los datos e intente nuevamente.'
                    ];
                }
            } else {
                $message = ['type' => 'warning', 'content' => 'Ingrese una nueva contraseña.'];
            }
        } else {
            $message = [
                'type' => 'warning',
                'content' => 'La contraseña actual no coincide. Por favor, revise los datos e intente nuevamente.'
            ];
        }

        return response()->json($message);
    }

    public function find_photo(Request $request)
    {
        $photo = Auth::user()->photo;
        $path = '';

        if ($photo != '') {
            $path = '/images/users/' . $photo;
        }

        return response()->json(['photo' => $path]);
    }

    public function change_photo(Request $request)
    {
        $photo = 'profile-' . Auth::user()->code . '-' . time() . '.' . $request->file->getClientOriginalExtension();

        $upload = $request->file->move(public_path('/images/users/'), $photo);

        if ($upload) {
            $user = Auth::user();
            // Eliminando la imagen anterior..
            if ($user->photo != '') {
                @unlink('images/users/' . $user->photo);
            }

            $user->photo = $photo;
            $user->save();

            $message = [
                'path' => '/images/users/' . $photo,
                'type' => 'success',
                'content' => 'Foto actualizada correctamente.'
            ];
        } else {
            $message = [
                'path' => '',
                'type' => 'warning',
                'content' => 'No se pudo actualizar la imagen. Por favor, intente nuevamente.'
            ];
        }

        return $message;
    }

    public function upload_file(Request $request)
    {
        $file = 'upload-' . Auth::user()->code . '-' . time() . '.' . $request->file->getClientOriginalExtension();

        $upload = $request->file->move(public_path('/uploads/'), $file);

        if ($upload) {
            $message = [
                'path' => '/uploads/' . $file,
                'name' => $file,
                'success' => true
            ];
        } else {
            $message = [
                'success' => false
            ];
        }

        return $message;
    }

    public function delete_photo(Request $request)
    {
        $photo = '';

        $user = Auth::user();
        // Eliminando la imagen anterior..
        if ($user->photo != '') {
            @unlink('images/users/' . $user->photo);
        }

        $user->photo = $photo;
        $user->save();

        $message = ['path' => '', 'type' => 'success', 'content' => 'Foto eliminada correctamente.'];

        return $message;
    }

    public function search_users(Request $request)
    {
        $filter = $request->__get('filter');
        $users = User::where('status', '=', 1)->whereIn('user_type_id', [1, 3]);

        if ($filter != '') {
            $filter = '%' . $filter . '%';
            $users = $users->where(function ($query) use ($filter) {
                $query->orWhere('code', 'like', $filter)->orWhere('name', 'like', $filter);
            });
        }

        $users = $users->take(10)->get();

        return response()->json(['users' => $users]);
    }

    public function count_reminders(Request $request)
    {
        $all_reminders = Reminder::where('created_by', '=', Auth::user()->id)->count();

        return response()->json(['all_reminders' => $all_reminders]);
    }

    public function search_reminders(Request $request)
    {
        $reminders = Reminder::where('created_by', '=', Auth::user()->id)->get();
        $all_reminders = $reminders->count();

        return response()->json(['reminders' => $reminders, 'all_reminders' => $all_reminders]);
    }

    public function save_reminder(Request $request)
    {
        $fecini = explode("T", $request->__get('fecini'));
        $fecfin = explode("T", $request->__get('fecfin'));
        $fecini = explode("/", $fecini[0]);
        $fecfin = explode("/", $fecfin[0]);

        $fecini = $fecini[2] . '-' . $fecini[1] . '-' . $fecini[0];
        $fecfin = $fecfin[2] . '-' . $fecfin[1] . '-' . $fecfin[0];

        try {
            $reminder = new Reminder;
            $reminder->title = $request->__get('title');
            $reminder->fecini = $fecini;
            $reminder->fecfin = $fecfin;
            $reminder->users = json_encode($request->__get('users'));
            $reminder->content = $request->__get('message');
            $reminder->type = $request->__get('type');
            $reminder->priority = $request->__get('priority');
            $reminder->time = $request->__get('hour');
            $reminder->status = 1;
            $reminder->created_by = Auth::user()->id;
            $reminder->save();

            $all_reminders = Reminder::all()->count();

            return response()->json(['reminder' => $reminder, 'type' => 'success', 'all_reminders' => $all_reminders]);
        } catch (Exception $ex) {
            return response()->json(['type' => 'error', 'message' => $ex->getMessage()]);
        }
    }

    public function reminder(Request $request, $type)
    {
        try {
            $reminder = Reminder::find($request->__get('reminder'));

            if ($type == 'play') {
                $reminder->status = 1;
            }

            if ($type == 'pause') {
                $reminder->status = 2;
            }

            if ($type == 'delete') {
                $reminder->delete();
            } else {
                $reminder->save();
            }

            return response()->json(['type' => 'success', 'message' => 'Recordatorio actualizado correctamente.']);
        } catch (Exception $ex) {
            return response()->json(['type' => 'error', 'message' => $ex->getMessage()]);
        }
    }

    public function search_notifications(Request $request)
    {
        $time = $request->__get('time');
        $module = $request->__get('module');

        $notifications = Notification::where('user', '=', Auth::user()->code);

        if ($time != "") {
            // Pedir notificationes por tiempo..
            $notifications = $notifications->where('created_at', '>', $time);
        }

        $notifications = $notifications->orderBy('id', 'desc');
        $news = $notifications;

        $notifications = $notifications->where('status', '>', 0)->limit(10);
        $all = $notifications->count(); // Todas..
        $notifications = $notifications->get();

        foreach ($notifications as $key => $value) {
            $notifications[$key]['photo'] = @$value->user->photo;
        }

        $news = $news->where('status', '=', 1)->count(); // Pendientes..

        $time = date("Y-m-d H:i:s");

        return response()->json([
            'notifications' => [
                'items' => $notifications,
                'all' => $all,
                'news' => $news,
                'time' => $time
            ]
        ]);
    }

    public function update_notification(Request $request)
    {
        $notification = Notification::find($request->__get('id'));
        $notification->status = 2; // Leído..
        $notification->save();

        return response()->json(['notification' => $notification]);
    }

    // PUSH NOTIFICATION..
    public function register_push(Request $request)
    {
        $token = $request->__get('token');

        $user_noti = new UserNotification;
        $user_noti->user_id = Auth::user()->id;
        $user_noti->token = $token;
        $user_noti->status = 1;

        if ($user_noti->save()) {
            $response = ['type' => 'success', 'object_id' => $user_noti->id];
        } else {
            $response = ['type' => 'error'];
        }

        return response()->json($response);
    }

    public function operability(Request $request)
    {
        if ($request->isMethod('post')) {
            if (!$request->hasFile('file')) {
                return view('operability');
            }

            $data = Excel::toArray(new OperabilityImport, $request->file);

            echo "<pre>";
            foreach ($data as $key => $value) {
                print_r($value);
            }
        } else {
            return view('operability');
        }
    }
}
