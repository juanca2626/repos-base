<?php

namespace App\Http\Controllers;

use App\ServiceSubCategory;
use App\Http\Traits\Translations;
use Illuminate\Http\Request;

class ServiceSubCategoryController extends Controller
{
    use Translations;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\ServiceSubCategory $subCategoryService
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceSubCategory $subCategoryService)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\ServiceSubCategory $subCategoryService
     * @return \Illuminate\Http\Response
     */
    public function edit(ServiceSubCategory $subCategoryService)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\ServiceSubCategory $subCategoryService
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ServiceSubCategory $subCategoryService)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\ServiceSubCategory $subCategoryService
     * @return \Illuminate\Http\Response
     */
    public function destroy(ServiceSubCategory $subCategoryService)
    {
        //
    }
}
