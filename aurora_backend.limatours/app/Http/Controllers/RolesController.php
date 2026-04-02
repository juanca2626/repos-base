<?php

namespace App\Http\Controllers;

use App\Exports\UsersByAllRolesExport;
use App\RoleAdmin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Exports\UsersByRoleExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;
use jeremykenedy\LaravelRoles\Models\Permission;
use App\Exports\MultiRolePermissionsExport;
class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:roles.read')->only('index');
        $this->middleware('permission:roles.create')->only('store');
        $this->middleware('permission:roles.update')->only('update');
        $this->middleware('permission:roles.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $roles = RoleAdmin::all();

        return Response::json(['success' => true, 'data' => $roles]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        RoleAdmin::create($request->all());

        return Response::json(['success' => true]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return Response::json(['success' => true, 'data' => RoleAdmin::find($id)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $role = RoleAdmin::findOrFail($id);
        $role->update($request->all());

        return Response::json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {

        $role = config('roles.models.role')::find($id);

        $role->detachAllPermissions();

        $role->delete();

        return Response::json(['success' => true]);
    }

    public function search(Request $request)
    {
        $roles = RoleAdmin::search(request()->search)->get();

        return Response::json([
            'success' => true,
            'data' => $roles,
            'count' => $roles->count()
        ]);
    }

    public function updateStatus($id, Request $request)
    {
        $role = RoleAdmin::find($id);
        if ($request->input("status")) {
            $role->status = false;
        } else {
            $role->status = true;
        }
        $role->save();
        return Response::json(['success' => true]);
    }


    /**
     * @return JsonResponse
     */
    public function selectBox()
    {
        $roles = RoleAdmin::all();
        $result = [];

        foreach ($roles as $rol) {
            array_push($result, ['text' => $rol->name, 'value' => $rol->id]);
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    /**
     * Asigna permisos a los roles
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function permissions(Request $request, $id)
    {

        // 1) Validación mínima
        $data = $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'nullable', // no lo fuerces a boolean aquí
        ]);

        // 2) Rol
        $role = config('roles.models.role')::findOrFail($id);

        // 3) Tomamos solo los IDs marcados "verdaderos" (true, "true", 1, "1", "on")
        $isTruthy = function ($v) {
            return $v === true || $v === 1 || $v === '1' || $v === 'true' || $v === 'on';
        };

        $selectedIds = array();
        foreach ($data['permissions'] as $key => $val) {
            if ($isTruthy($val)) {
                $selectedIds[] = (int) $key;
            }
        }

        // 4) Normalizamos (únicos)
        $selectedIds = array_values(array_unique($selectedIds));

        // 5) Un solo batch: validamos IDs existentes y sincronizamos la pivote
        return DB::transaction(function () use ($role, $selectedIds) {
            $validIds = Permission::whereIn('id', $selectedIds)->pluck('id')->all();

            // sync() reemplaza: quita los que no estén y agrega los nuevos
            $role->permissions()->sync($validIds);

            return Response::json([
                'success'  => true,
                'assigned' => count($validIds),
            ]);
        });
    }

    public function getUsers($id)
    {
        $role = RoleAdmin::find($id);
        $roleName = str_replace(' ', '_', strtolower($role->name));
        return Excel::download(new UsersByRoleExport($id, $role->name), "users_{$roleName}.xlsx");
    }

    /**
     * Genera un Excel con una pestaña por rol y devuelve URL pública.
     */
    public function exportAllRolesPermissions()
    {
        // Traemos todos los roles desde tu modelo actual (RoleAdmin)
        $roles = RoleAdmin::orderBy('name')->get(['id','name']);
        if ($roles->isEmpty()) {
            return Response::json(['success' => false, 'message' => 'No hay roles para exportar'], 404);
        }

        // Nombre y ruta
        $filename = 'roles_permissions_' . now()->format('Ymd_His') . '.xlsx';
        $relative = 'exports/' . $filename; // storage/app/public/exports/...
        $disk = 'public';

        // Guardar el archivo en disco público
        Excel::store(new MultiRolePermissionsExport($roles), $relative, $disk);

        // URL pública (requiere `php artisan storage:link`)
        $url = url(Storage::disk($disk)->url($relative));

        return Response::json(['success' => true, 'url' => $url]);
    }

    /**
     * Genera un Excel con una sola pestaña para el rol indicado y devuelve URL pública.
     */
    public function exportRolePermissions($id)
    {
        $role = RoleAdmin::find($id, ['id','name']);
        if (!$role) {
            return Response::json(['success' => false, 'message' => 'Rol no encontrado'], 404);
        }

        // Puedes reutilizar el export múltiple pasando una colección con 1 rol
        $roles = collect([$role]);

        $slug = str_replace(' ', '_', strtolower($role->name));
        $filename = "role_{$slug}_" . now()->format('Ymd_His') . '.xlsx';
        $relative = 'exports/' . $filename;
        $disk = 'public';

        Excel::store(new MultiRolePermissionsExport($roles), $relative, $disk);

        $url = url(Storage::disk($disk)->url($relative));

        return Response::json(['success' => true, 'url' => $url]);
    }

    public function exportAllRolesUsers()
    {
        $roles = RoleAdmin::orderBy('name')->get(['id','name']);
        if ($roles->isEmpty()) {
            return \Illuminate\Support\Facades\Response::json([
                'success' => false,
                'message' => 'No hay roles para exportar'
            ], 404);
        }

        $filename = 'roles_users_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new UsersByAllRolesExport($roles), $filename);
    }
}
