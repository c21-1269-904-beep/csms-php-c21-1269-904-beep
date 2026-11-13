<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private DashboardService $dashboardService;

    public function __construct(?DashboardService $dashboardService = null)
    {
        $this->dashboardService = $dashboardService
            ?? new DashboardService();
    }

    /**
     * Display the CSMS dashboard.
     */
    public function index(): View
    {
        $summary = $this->dashboardService->getSummary();

        return view('dashboard.index', $summary);
    }
}
