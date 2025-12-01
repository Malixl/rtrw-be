<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Resources\DashboardResources;
use App\Http\Services\DashboardService;

class DashboardController
{
    protected $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $counts = $this->service->getCounts();

        return response()->json([
            'code' => 200,
            'status' => true,
            'message' => 'Dashboard count data fetched successfully',
            'data' => new DashboardResources($counts)
        ]);
    }
}
