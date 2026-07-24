<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, AdminDashboardService $dashboard)
    {
        return inertia('Dashboard', $dashboard->page($request->user()));
    }
}
