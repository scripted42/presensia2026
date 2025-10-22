<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PerformanceService;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    /**
     * Display performance dashboard
     */
    public function index()
    {
        $stats = PerformanceService::getDatabaseStats();
        
        // Get memory usage
        $memoryUsage = [
            'current' => round(memory_get_usage() / 1024 / 1024, 2),
            'peak' => round(memory_get_peak_usage() / 1024 / 1024, 2),
            'limit' => ini_get('memory_limit'),
        ];

        // Get cache statistics
        $cacheStats = [
            'config_cached' => file_exists(base_path('bootstrap/cache/config.php')),
            'routes_cached' => file_exists(base_path('bootstrap/cache/routes-v7.php')),
            'views_cached' => is_dir(base_path('storage/framework/views')),
        ];

        return view('performance.index', compact('stats', 'memoryUsage', 'cacheStats'));
    }

    /**
     * Optimize performance
     */
    public function optimize()
    {
        PerformanceService::optimizeQueries();
        
        return redirect()->back()->with('success', 'Performance optimization completed!');
    }

    /**
     * Clear caches
     */
    public function clearCache()
    {
        PerformanceService::clearCaches();
        
        return redirect()->back()->with('success', 'All caches cleared!');
    }
}

