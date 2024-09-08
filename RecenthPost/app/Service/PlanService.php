<?php
namespace App\Service;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanService
{
    public static function index()
    {
        return response()->json(Plan::all(), 200);
    }

    public static function show($id)
    {
        $plan = Plan::find($id);
        if ($plan) {
            return response()->json($plan, 200);
        } else {
            return response()->json(["message" => "Plan not found"], 404);
        }
    }

    public static function store(Request $request)
    {
        $plan = Plan::create($request->all());
        return response()->json($plan, 201);
    }

    public static function update(Request $request, $id)
    {
        $plan = Plan::find($id);
        if ($plan) {
            $plan->update($request->all());
            return response()->json($plan, 200);
        } else {
            return response()->json(["message" => "Plan not found"], 404);
        }
    }

    public static function destroy($id)
    {
        $plan = Plan::find($id);
        if ($plan) {
            $plan->delete();
            return response()->json(["message" => "Plan deleted"], 200);
        } else {
            return response()->json(["message" => "Plan not found"], 404);
        }
    }
}
