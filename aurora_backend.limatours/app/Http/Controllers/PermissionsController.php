<?php

namespace App\Http\Controllers;

use App\PermissionModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use jeremykenedy\LaravelRoles\Models\Permission;
use App\Models\Auth\App;
use App\Classes\Permission as PermissionMenu;
use Illuminate\Support\Facades\App as AppEnv;

class PermissionsController extends Controller
{
    protected $permissions = array();
    protected $menuWithPermission = array();


    public function __construct()
    {
        $this->middleware('permission:permissions.read')->only('index');
        $this->middleware('permission:permissions.create')->only('store');
        $this->middleware('permission:permissions.update')->only('update');
        $this->middleware('permission:permissions.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $permissions = Permission::all();

        return Response::json(['success' => true, 'data' => $permissions]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        Permission::create($request->all());

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
        return Response::json(['success' => true, 'data' => Permission::find($id)]);
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
        $permission = Permission::findOrFail($id);
        $permission->update($request->all());

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
        $result = ['success' => true];
        $permissions = DB::table('permission_role')
            ->join('roles', 'permission_role.role_id', '=', 'roles.id')
            ->select('roles.name')
            ->where('permission_role.permission_id', $id)
            ->get();

        if ($permissions->isEmpty()) {
            $permission = Permission::findOrFail($id);
            $permission->delete();
        } else {
            $rolesName = $permissions->implode('name', ' ');
            $result['success'] = false;
            $result['message'] = 'Existen roles que tienen asignado este permiso: ' . $rolesName;
        }

        return Response::json($result);
    }

    /**
     * Display Permissions to be use in select boxes
     *
     * @return JsonResponse
     */
    public function selectBox()
    {
        $permissions = Permission::select('id', 'name')->orderBy('name')->get();
        $result = [];

        foreach ($permissions as $permission) {
            $result[] = ['text' => $permission->name, 'value' => $permission->id];
        }

        return Response::json(['success' => true, 'data' => $result]);
    }

    /**
     * Display Permissions to be use in select boxes
     *
     * @return JsonResponse
     */
    public function treeView()
    {
        // 1) Módulos (ya sin parent_id) indexados por id
        $modules = PermissionModule::select('id','name','slug','kind','sort_order')
            ->orderBy('sort_order')->orderBy('name')
            ->get()->keyBy('id');

        // 2) Permisos con su módulo asignado
        $rows = DB::table('permissions as p')
            ->join('permission_details as d', 'd.permission_id', '=', 'p.id')
            ->select(
                'p.id as perm_id',
                'p.slug as perm_slug',
                'p.description as perm_desc',
                'd.permission_module_id as module_id'
            )
            ->orderBy('p.slug')
            ->get();

        // Helpers
        $splitSlug = function (string $slug) {
            if (strpos($slug, '.') === false) {
                return [$slug, $slug]; // [base, action]
            }
            $parts = explode('.', $slug, 2);
            $base  = trim($parts[0]);
            $rest  = trim($parts[1]);
            $seg   = explode('.', $rest);
            $action = trim($seg[count($seg)-1]);
            return [$base, $action];
        };

        $actionWeight = function ($action) {
            static $order = ['create'=>1,'read'=>2,'update'=>3,'delete'=>4];
            $a = strtolower($action);
            return $order[$a] ?? (100 + ord(substr($a, 0, 1)));
        };

        // 3) Buckets por módulo
        $tree = []; // module_id => ['module'=>..., 'kind'=>..., 'permissions'=>[ base => group ]]

        foreach ($rows as $r) {
            $module = $modules->get((int)$r->module_id);
            if (!$module) continue; // detalle huérfano

            if (!isset($tree[$module->id])) {
                $tree[$module->id] = [
                    'module'      => $module->name,
                    'kind'        => $module->kind,   // "primary" | "auxiliary"
                    'permissions' => [],
                ];
            }

            [$base, $action] = $splitSlug($r->perm_slug);
            $baseKey = strtolower($base);

            if (!isset($tree[$module->id]['permissions'][$baseKey])) {
                $tree[$module->id]['permissions'][$baseKey] = [
                    'id'          => $baseKey,
                    'name'        => $baseKey,
                    'description' => null,
                    'data'        => [],
                ];
            }

            $item = [
                'id'   => (int)$r->perm_id,
                'name' => $action,
            ];
            if (!empty($r->perm_desc)) {
                $item['description'] = $r->perm_desc;
                if (empty($tree[$module->id]['permissions'][$baseKey]['description'])) {
                    $tree[$module->id]['permissions'][$baseKey]['description'] = $r->perm_desc;
                }
            }

            $tree[$module->id]['permissions'][$baseKey]['data'][] = $item;
        }

        // 4) Orden: módulos por sort_order y nombre; grupos por nombre; acciones por prioridad
        $orderedModuleIds = $modules->keys()->all();
        usort($orderedModuleIds, function ($a, $b) use ($modules) {
            $ma = $modules->get($a);
            $mb = $modules->get($b);

            if (!$ma || !$mb) {
                return 0;
            }

            if ((int)$ma->sort_order === (int)$mb->sort_order) {
                return strcasecmp($ma->name, $mb->name);
            }

            return ((int)$ma->sort_order < (int)$mb->sort_order) ? -1 : 1;
        });

        $final = [];
        foreach ($orderedModuleIds as $mid) {
            if (!isset($tree[$mid])) continue; // omitir módulos sin permisos

            $payload = $tree[$mid];
            $groups = $payload['permissions'];

            // ordenar grupos
            ksort($groups, SORT_NATURAL | SORT_FLAG_CASE);

            // ordenar acciones dentro de cada grupo
            foreach ($groups as &$g) {
                usort($g['data'], function ($x, $y) use ($actionWeight) {
                    $wx = $actionWeight($x['name']);
                    $wy = $actionWeight($y['name']);

                    if ($wx === $wy) {
                        return strcasecmp($x['name'], $y['name']);
                    }

                    return ($wx < $wy) ? -1 : 1;
                });
            }

            $final[] = [
                'module'      => $payload['module'],
                'kind'        => $payload['kind'],     // <- aquí va el kind que pedías
                'permissions' => array_values($groups),
            ];
        }

        return Response::json(['success' => true, 'data' => $final]);
    }

    public function treeViewBackup()
    {
        $permissions = Permission::select('id', 'slug', 'description')->orderBy('slug')->get();
        $tmpResult = ['global' => ['id' => 'global', 'name' => 'Global', 'description' => 'Global', 'data' => []]];
        $result = [];
        foreach ($permissions as $permission) {
            if (strpos($permission->slug, '.')) {
                $tmp = (array)explode('.', $permission->slug);
                if (array_key_exists(strtolower(trim($tmp[0])), $tmpResult)) {
                    $tmpResult[strtolower(trim($tmp[0]))]['data'][] = [
                        'id' => $permission->id,
                        'name' => trim($tmp[1]),
                        'description' => $permission->description
                    ];
                } else {
                    $tmpResult[strtolower(trim($tmp[0]))] = [
                        'id' => trim($tmp[0]),
                        'name' => trim($tmp[0]),
                        'description' => $permission->description,
                        'data' => [['id' => $permission->id, 'name' => trim($tmp[1])]]
                    ];
                }
            } else {
                $tmpResult['global']['data'][] = [
                    'id' => $permission->id,
                    'name' => trim($permission->slug),
                    'description' => $permission->description,
                ];
            }
        }

        foreach ($tmpResult as $tmpItem) {
            $result[] = $tmpItem;
        }

        return Response::json(['success' => true, 'data' => $result]);
    }

    /**
     * Return permissions from Role
     *
     * @param $id
     * @return JsonResponse
     */
    public function fromRole($id)
    {
        $permissions = DB::table('permission_role')->select('permission_id')->where('role_id', $id)->get();
        $result = [];

        foreach ($permissions as $permission) {
            $result[$permission->permission_id] = true;
        }
        return Response::json(['success' => true, 'data' => $result]);
    }

    private function setPermissions()
    {
        $user = auth()->user();
        $permissions = [];
        foreach ($user->roles as $user_role) {
            foreach ($user_role->permissions as $permission) {
                $tmp = explode(".", $permission->slug);
                $subject = $tmp[0];
                $action = $tmp[1];
                if (!in_array($subject, array_column($permissions, 'subject'))) {
                    $permissions[] = array('subject' => $subject, 'actions' => array($action));
                } else {
                    $subject_index = array_search($subject, array_column($permissions, 'subject'));
                    $permissions[$subject_index]['actions'][] = $action;
                }
            }
        }

        $transformPermissions = [];
        foreach ($permissions as $permission) {
            $transformPermissions[] = new PermissionMenu($permission['subject'], $permission['actions']);
        }

        $this->permissions = collect($transformPermissions);
    }

    private function getPermissions()
    {
        return $this->permissions;
    }

    private function getMenuWithPermission()
    {
        return $this->menuWithPermission;
    }

    private function addMenuWithPermission($record, $app, $actions)
    {
        $appExistKey = collect($this->menuWithPermission)->search(function ($item) use ($app) {
            return $item->name == $app->name;
        });

        $record->actions = $actions;
        if ($appExistKey > -1) {
            $appForPush = $this->menuWithPermission[$appExistKey];
            $childrens = $appForPush->children;
            $childrens[] = $record;
            $appForPush->children = $childrens;
            $this->menuWithPermission[$appExistKey] = $appForPush;
        } else {
            $appForPush = $app;
            $appForPush->children = [$record];
            $this->menuWithPermission[] = $appForPush;
        }
    }

    private function addChildMenuWithPermission($menu,$record, $app, $actions)
    {
        $appExistKey = collect($this->menuWithPermission)->search(function ($item) use ($app) {
            return $item->name == $app->name;
        });

        $record['actions'] = $actions;
        if ($appExistKey > -1) {
            $appForPush = $this->menuWithPermission[$appExistKey];
            $childrens = $appForPush->children;
            $childrens[] = $record;
            $appForPush->children = $childrens;
            $this->menuWithPermission[$appExistKey] = $appForPush;
        } else {
            $appForPush = $app;
            $appForPush->children = [$record];
            $this->menuWithPermission[] = $appForPush;
        }
    }

    private function cleanMenuWithPermission()
    {
        $this->menuWithPermission = array();
    }

    private function getMenuTransform()
    {

        $lang = AppEnv::getLocale();

        return App::with('menus.sub_menus')->get()->transform(function ($row) use ($lang) {
            return (object)[
                'id' => $row->id,
                'lang' => $lang,
                'name' => trans("menu.{$row->name}"),
                'icon' => $row->icon,
                'children' => $row->menus->transform(function ($menu) {
                    return (object)[
                        'id' => $menu->id,
                        'name' => trans("menu.{$menu->name}"),
                        'slug' => $menu->slug,
                        'icon' => $menu->icon,
                        'app_id' => $menu->app_id,
                        'path' => $menu->path,
                        'target_site' => $menu->target_site,
                        'actions' => [],
                        'children' => $menu->sub_menus->transform(function ($subMenu) {
                            return [
                                'id' => $subMenu->id,
                                'name' => $subMenu->name,
                                'slug' => $subMenu->slug,
                                'target_site' => $subMenu->target_site,
                                'path' => $subMenu->path,
                                'icon' => $subMenu->icon,
                                'actions' => [],
                            ];
                        })
                    ];
                }),

            ];
        });
    }

    private function searchInPermissions($key, $value)
    {
        $collection = $this->getPermissions();
        return $collection->firstWhere($key, $value);
    }

    private function getActionPermission($value)
    {
        $collection = $this->getPermissions();
        $actions = $collection->firstWhere('name', $value);
        if ($actions) {
            return $actions->actions;
        }
        return [];
    }

    public function menu(Request $request)
    {
        $first_lang = app()->getLocale();
        $lang = $request->get('lang') ?? '';
        if ($lang) {
            app()->setLocale($lang);
        }
        $this->setPermissions();
        $this->cleanMenuWithPermission();
        $records = $this->getMenuTransform();
        foreach ($records as $app) {
            $appHasChildren = false;
            $appObject = clone $app;
            $appObject->children = [];

            foreach ($app->children as $menu) {
                $menuHasPermission = $this->searchInPermissions('name', $menu->slug);
                $menuObject = clone $menu;
                $menuObject->children = [];

                if ($menuHasPermission || !empty($menu->children)) {
                    foreach ($menu->children as $child) {
                        $childHasPermission = $this->searchInPermissions('name', $child['slug'] ?? '');
                        if ($childHasPermission) {
                            $childObject = (object)$child;
                            $childObject->actions = $this->getActionPermission($child['slug'] ?? '');
                            $menuObject->children[] = $childObject;
                        }
                    }

                    if ($menuHasPermission || !empty($menuObject->children)) {
                        $menuObject->actions = $this->getActionPermission($menu->slug);
                        $appObject->children[] = $menuObject;
                        $appHasChildren = true;
                    }
                }
            }

            if ($appHasChildren) {
                $this->menuWithPermission[] = $appObject;
            }
        }

        app()->setLocale($first_lang);

        return ['data' => $this->getMenuWithPermission(), 'success' => true];
    }
    
    /**
     * @param  array $menusWithPermissions
     * @return void
     */
    private function addHelpDeskMenu(array &$menusWithPermissions): void
    {
        $helpDeskAppMenu = $this->getHelpDeskAppMenu();
        if (!$helpDeskAppMenu) return;

        foreach ($menusWithPermissions as $key => $appMenu)
        {
            if ($appMenu->id === $helpDeskAppMenu->id)
            {
                $menusWithPermissions[$key]->children = array_merge(
                    $appMenu->children,
                    $helpDeskAppMenu->children->toArray()
                );

                return;
            }
        }

        $menusWithPermissions[] = $helpDeskAppMenu;
    }

    /**
     * @return ?object
     */
    private function getHelpDeskAppMenu(): ?object
    {
        $appMenu = App::whereHas(
                'menus', 
                function ($query) {
                    return $query->filterHelpDesk();
                }
            )
            ->with([
                'menus' => function ($query) {
                    return $query->filterHelpDesk();
                }
            ])
            ->first();

        if (!$appMenu) return null;
        
        return (object) [
            'id' => $appMenu->id,
            'lang' => AppEnv::getLocale(),
            'name' => trans("menu.{$appMenu->name}"),
            'icon' => $appMenu->icon,
            'children' => $appMenu->menus->transform(function ($menu) {
                return (object) [
                    'id' => $menu->id,
                    'name' => trans("menu.{$menu->name}"),
                    'slug' => $menu->slug,
                    'icon' => $menu->icon,
                    'app_id' => $menu->app_id,
                    'path' => $menu->path,
                    'target_site' => $menu->target_site,
                    'actions' => [],
                    'children' => [],
                ];
            })
        ];
    }

}
