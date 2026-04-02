<?php

namespace App\Http\Middleware;

use App\PermissionBackend;
use App\PermissionRole;
use App\RoleUser;
use Closure;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  $permission
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        if (Auth::guest()) {
            return redirect('/login');
        }
        $id_user = Auth::user()->id;

        $role_user_id = RoleUser::where('user_id', $id_user)->get();

        $permission_id = PermissionBackend::where('slug', $permission)->first();

        if(is_object($permission_id )) {

            $permission = PermissionRole::where('permission_id', $permission_id->id)->where('role_id', $role_user_id[0]["role_id"])->get();

            if ($permission->count() > 0) {
                return $next($request);
            }

        }

        abort(403);
    }
}
