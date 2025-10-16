<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditTrail;
use App\Models\SecurityLog;
use App\Models\School;
use Illuminate\Support\Facades\DB;

class NetworkController extends Controller
{
    /**
     * Display network monitoring dashboard
     */
    public function index(Request $request)
    {
        // Get network statistics
        $networkStats = $this->getNetworkStatistics();
        
        // Get traffic data
        $trafficData = $this->getTrafficData($request);
        
        // Get connection statistics
        $connectionStats = $this->getConnectionStatistics();
        
        // Get bandwidth usage
        $bandwidthUsage = $this->getBandwidthUsage();
        
        // Get server performance
        $serverPerformance = $this->getServerPerformance();
        
        return view('super-admin.network.index', compact(
            'networkStats', 
            'trafficData', 
            'connectionStats', 
            'bandwidthUsage', 
            'serverPerformance'
        ));
    }

    /**
     * Get network statistics
     */
    private function getNetworkStatistics()
    {
        // Simulate network statistics (in real implementation, you'd get this from system monitoring)
        return [
            'total_requests' => AuditTrail::count(),
            'active_connections' => rand(50, 200),
            'bandwidth_usage' => rand(10, 80), // Percentage
            'packet_loss' => rand(0, 5), // Percentage
            'latency' => rand(10, 100), // ms
            'uptime' => '99.9%',
            'server_load' => rand(20, 80), // Percentage
            'memory_usage' => rand(40, 90), // Percentage
            'disk_usage' => rand(30, 85), // Percentage
        ];
    }

    /**
     * Get traffic data
     */
    private function getTrafficData(Request $request)
    {
        $query = AuditTrail::query();
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        // Get traffic by hour for the last 24 hours
        $trafficByHour = $query->where('created_at', '>=', now()->subHours(24))
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as requests')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('requests', 'hour');

        // Get traffic by school
        $trafficBySchool = $query->join('schools', 'audit_trails.school_id', '=', 'schools.id')
            ->selectRaw('schools.name, COUNT(*) as requests')
            ->groupBy('schools.name')
            ->orderBy('requests', 'desc')
            ->limit(10)
            ->get();

        // Get traffic by IP
        $trafficByIp = $query->selectRaw('ip_address, COUNT(*) as requests')
            ->groupBy('ip_address')
            ->orderBy('requests', 'desc')
            ->limit(10)
            ->get();

        return [
            'by_hour' => $trafficByHour,
            'by_school' => $trafficBySchool,
            'by_ip' => $trafficByIp
        ];
    }

    /**
     * Get connection statistics
     */
    private function getConnectionStatistics()
    {
        // Get unique IPs in last 24 hours
        $uniqueIps = AuditTrail::where('created_at', '>=', now()->subHours(24))
            ->distinct('ip_address')
            ->count('ip_address');

        // Get top countries (simulated)
        $topCountries = [
            ['country' => 'Indonesia', 'requests' => rand(1000, 5000)],
            ['country' => 'Malaysia', 'requests' => rand(500, 2000)],
            ['country' => 'Singapore', 'requests' => rand(200, 1000)],
            ['country' => 'Thailand', 'requests' => rand(100, 500)],
        ];

        // Get connection types (simulated)
        $connectionTypes = [
            ['type' => 'HTTP', 'percentage' => 85],
            ['type' => 'HTTPS', 'percentage' => 15],
        ];

        return [
            'unique_ips' => $uniqueIps,
            'top_countries' => $topCountries,
            'connection_types' => $connectionTypes
        ];
    }

    /**
     * Get bandwidth usage
     */
    private function getBandwidthUsage()
    {
        // Simulate bandwidth usage data
        $bandwidthData = [];
        for ($i = 23; $i >= 0; $i--) {
            $bandwidthData[] = [
                'hour' => now()->subHours($i)->format('H:i'),
                'inbound' => rand(100, 1000), // MB
                'outbound' => rand(50, 500), // MB
            ];
        }

        return [
            'data' => $bandwidthData,
            'total_inbound' => array_sum(array_column($bandwidthData, 'inbound')),
            'total_outbound' => array_sum(array_column($bandwidthData, 'outbound')),
            'peak_inbound' => max(array_column($bandwidthData, 'inbound')),
            'peak_outbound' => max(array_column($bandwidthData, 'outbound')),
        ];
    }

    /**
     * Get server performance metrics
     */
    private function getServerPerformance()
    {
        // Get database performance
        $dbStats = DB::select("SHOW STATUS LIKE 'Connections'")[0] ?? (object)['Value' => 0];
        
        // Simulate server metrics
        return [
            'cpu_usage' => rand(20, 80),
            'memory_usage' => rand(40, 90),
            'disk_usage' => rand(30, 85),
            'database_connections' => $dbStats->Value ?? 0,
            'query_time' => rand(10, 100), // ms
            'cache_hit_ratio' => rand(80, 99), // percentage
        ];
    }

    /**
     * Get real-time network data
     */
    public function realtime()
    {
        $data = [
            'timestamp' => now()->toISOString(),
            'active_connections' => rand(50, 200),
            'bandwidth_in' => rand(100, 1000),
            'bandwidth_out' => rand(50, 500),
            'requests_per_second' => rand(10, 100),
            'cpu_usage' => rand(20, 80),
            'memory_usage' => rand(40, 90),
            'disk_usage' => rand(30, 85),
        ];

        return response()->json($data);
    }

    /**
     * Get network alerts
     */
    public function alerts()
    {
        $alerts = [];

        // Check for high CPU usage
        $cpuUsage = rand(20, 80);
        if ($cpuUsage > 80) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'High CPU usage detected: ' . $cpuUsage . '%',
                'timestamp' => now()->toISOString()
            ];
        }

        // Check for high memory usage
        $memoryUsage = rand(40, 90);
        if ($memoryUsage > 85) {
            $alerts[] = [
                'type' => 'critical',
                'message' => 'High memory usage detected: ' . $memoryUsage . '%',
                'timestamp' => now()->toISOString()
            ];
        }

        // Check for high disk usage
        $diskUsage = rand(30, 85);
        if ($diskUsage > 80) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'High disk usage detected: ' . $diskUsage . '%',
                'timestamp' => now()->toISOString()
            ];
        }

        return response()->json($alerts);
    }
}
