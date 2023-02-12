<?php namespace Wpjscc\Health\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as ControllerBase;

class HealthController extends ControllerBase
{
    public function index(Request $request)
    {
        return response()->json([
            'is_healthy' =>true
        ]);
    }
}
