<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class PermissionRolesController extends Controller
{
    /**
     * @param $name | Manage_Services
     * @return JsonResponse
     */
    public function like_name($name)
    {
        // exclusive  |  generals
        $permissions_ids = DB::table('permission_role')
            ->where('role_id', Auth::user()->roles()->first()->id)
            ->pluck('permission_id');
        $permissions = DB::table('permissions')
            ->where('name', 'like', '%'.$name.'%')
            ->whereIn('id', $permissions_ids)->get();
        
        return Response::json(['success' => true, 'data' => $permissions]);
    }
}
