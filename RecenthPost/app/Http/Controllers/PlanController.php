<?php

namespace App\Http\Controllers;

use App\Service\PlanService;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        return PlanService::index();
    }

    public function show($id)
    {
        return PlanService::show($id);
    }

    public function store(Request $request)
    {
        return PlanService::store($request);
    }

    public function update(Request $request, $id)
    {
        return PlanService::update($request, $id);
    }

    public function destroy($id)
    {
        return PlanService::destroy($id);
    }
}
