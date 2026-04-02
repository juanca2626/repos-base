<?php

namespace App\Http\Controllers;

use App\DepartmentTeam;
use App\Employee;
use App\Http\Resources\DepartmentTeamCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

/**
 * Class DepartmentTeamController
 */
class DepartmentTeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:departments.create')->only('store');
        $this->middleware('permission:departments.update')->only('update');
        $this->middleware('permission:departments.delete')->only('delete');
    }
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index($department_id)
    {
        $departments = DepartmentTeam::get(['id', 'name']);
        return Response::json(['success' => true, 'data' => new DepartmentTeamCollection($departments)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $newDept = new DepartmentTeam();
            $newDept->department_id = $request->post('department_id');
            $newDept->name = $request->post('name');
            $newDept->save();
            return Response::json(['success' => true]);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'error' => $exception->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $find_dept = DepartmentTeam::find($id);
        if ($find_dept) {
            return Response::json(['success' => true, 'data' => $find_dept]);
        }
        return Response::json(['success' => false]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $find_dept = DepartmentTeam::find($id);
        if ($find_dept) {
            $find_dept->name = $request->input('name');
            $find_dept->save();
            return Response::json(['success' => true]);
        }
        return Response::json(['success' => false]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $dept_used = Employee::where('department_team_id', $id)->take(1)->get();
        if ($dept_used->count() == 0) {
            $find_dept = DepartmentTeam::find($id);
            $find_dept->delete();
            $used = false;
            $success = true;
        } else {
            $used = true;
            $success = false;
        }
        return Response::json(['success' => $success, 'used' => $used]);
    }

    /**
     * @param $department_id
     * @return JsonResponse
     */
    public function getTeamsByIdDepartment($department_id)
    {
        $teams = DepartmentTeam::where('department_id', $department_id)->get(['id', 'name']);
        return Response::json(['success' => true, 'data' => $teams]);
    }
}
