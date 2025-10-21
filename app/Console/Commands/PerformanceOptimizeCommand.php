<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PerformanceService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class PerformanceOptimizeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:optimize {--clear-cache : Clear all caches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize application performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting performance optimization...');

        // Clear caches if requested
        if ($this->option('clear-cache')) {
            $this->info('🧹 Clearing caches...');
            Cache::flush();
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            $this->info('✅ Caches cleared');
        }

        // Optimize database
        $this->info('🗄️ Optimizing database...');
        PerformanceService::optimizeQueries();
        $this->info('✅ Database optimized');

        // Get performance stats
        $this->info('📊 Getting performance statistics...');
        $stats = PerformanceService::getDatabaseStats();
        
        $this->table(
            ['Table', 'Records'],
            collect($stats)->map(function ($count, $table) {
                return [$table, number_format($count)];
            })->toArray()
        );

        // Cache configuration
        $this->info('⚙️ Caching configuration...');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        $this->info('✅ Configuration cached');

        $this->info('🎉 Performance optimization completed!');
        
        return Command::SUCCESS;
    }
}