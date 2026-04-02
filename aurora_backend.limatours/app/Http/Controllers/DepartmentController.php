<?php

namespace App\Http\Controllers;

use App\Department;
use App\DepartmentTeam;
use App\Http\Resources\DepartmentTeamCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

/**
 * Class DepartmentController
 */
class DepartmentController extends Controller
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $departments = Department::with(['teams' => function ($query) {
            $query->select(['id', 'name', 'department_id']);
        }])->get(['id', 'name']);

        return Response::json(['data' => $departments, 'count' => $departments->count()]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $newDept = new Department();
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
     * @param \App\Department $department
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $find_dept = Department::find($id);
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $find_dept = Department::find($id);
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
        $dept_used = DepartmentTeam::where('department_id', $id)->take(1)->get();
        if ($dept_used->count() == 0) {
            $find_dept = Department::find($id);
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
     * @return JsonResponse
     */
    public function getTeamsByDepartments()
    {
        $departments = DepartmentTeam::with(['department' => function ($query) {
            $query->select(['id', 'name']);
        }])->get(['id', 'name', 'department_id']);
        return Response::json(['success' => true, 'data' => new DepartmentTeamCollection($departments)]);
    }

    public function getTeamsSheetsAmor()
    {
        $departments = DepartmentTeam::whereIn('id', [9, 11, 13, 14])
            ->orderBy('name')
            ->get(['id', 'name']);
        return Response::json(['success' => true, 'data' => $departments]);
    }
}
