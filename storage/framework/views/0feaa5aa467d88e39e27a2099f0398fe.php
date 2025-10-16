<?php $__env->startSection('title', 'Absensi - Presensia'); ?>

<?php $__env->startSection('content'); ?>
<!-- Header -->
<div class="bg-white overflow-hidden shadow rounded-lg mb-6">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Absensi Hari Ini</h1>
                <p class="text-gray-600 mt-1"><?php echo e($todayCarbon->format('d F Y')); ?></p>
            </div>
            <div class="flex space-x-3">
                <?php if(!$attendances || !$attendances->check_in): ?>
                <a href="<?php echo e(route('attendance.check-in')); ?>" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors font-medium">
                    <i class="fas fa-sign-in-alt mr-2"></i>Absensi Masuk
                </a>
                <?php elseif(!$attendances->check_out): ?>
                <a href="<?php echo e(route('attendance.check-out')); ?>" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors font-medium">
                    <i class="fas fa-sign-out-alt mr-2"></i>Absensi Keluar
                </a>
                <?php else: ?>
                <span class="bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                    <i class="fas fa-check mr-2"></i>Sudah Absensi Lengkap
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Success Message -->
<?php if(session('success')): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
    <i class="fas fa-check-circle mr-2"></i><?php echo e(session('success')); ?>

</div>
<?php endif; ?>

<?php if(session('info')): ?>
<div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
    <i class="fas fa-info-circle mr-2"></i><?php echo e(session('info')); ?>

</div>
<?php endif; ?>

<!-- Attendance Status -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Check In Status -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-sign-in-alt text-2xl <?php echo e($attendances && $attendances->check_in ? 'text-green-600' : 'text-gray-400'); ?>"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Absensi Masuk</dt>
                        <dd class="text-lg font-medium text-gray-900">
                            <?php if($attendances && $attendances->check_in): ?>
                                <?php echo e($attendances->check_in->format('H:i:s')); ?>

                            <?php else: ?>
                                Belum Absensi
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Check Out Status -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-sign-out-alt text-2xl <?php echo e($attendances && $attendances->check_out ? 'text-red-600' : 'text-gray-400'); ?>"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Absensi Keluar</dt>
                        <dd class="text-lg font-medium text-gray-900">
                            <?php if($attendances && $attendances->check_out): ?>
                                <?php echo e($attendances->check_out->format('H:i:s')); ?>

                            <?php else: ?>
                                Belum Absensi
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Status -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-calendar-check text-2xl <?php echo e($attendances ? 'text-blue-600' : 'text-gray-400'); ?>"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Status</dt>
                        <dd class="text-lg font-medium text-gray-900">
                            <?php if($attendances): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <?php echo e($attendances->status_label); ?>

                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Alpha
                                </span>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <a href="<?php echo e(route('attendance.check-in')); ?>" class="flex flex-col items-center p-6 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
        <i class="fas fa-sign-in-alt text-green-600 text-3xl mb-3"></i>
        <span class="text-lg font-medium text-green-900">Absensi Masuk</span>
        <span class="text-sm text-green-700">Scan QR Code untuk absensi masuk</span>
    </a>

    <a href="<?php echo e(route('attendance.check-out')); ?>" class="flex flex-col items-center p-6 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
        <i class="fas fa-sign-out-alt text-red-600 text-3xl mb-3"></i>
        <span class="text-lg font-medium text-red-900">Absensi Keluar</span>
        <span class="text-sm text-red-700">Scan QR Code untuk absensi keluar</span>
    </a>

    <?php if(auth()->user()->hasRole(['teacher','admin'])): ?>
    <a href="<?php echo e(route('attendance.student-scan')); ?>" class="flex flex-col items-center p-6 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
        <i class="fas fa-qrcode text-purple-600 text-3xl mb-3"></i>
        <span class="text-lg font-medium text-purple-900">Scan Siswa</span>
        <span class="text-sm text-purple-700">Absensi siswa secara massal</span>
    </a>
    <?php endif; ?>

    <a href="<?php echo e(route('attendance.reports')); ?>" class="flex flex-col items-center p-6 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
        <i class="fas fa-chart-bar text-blue-600 text-3xl mb-3"></i>
        <span class="text-lg font-medium text-blue-900">Laporan</span>
        <span class="text-sm text-blue-700">Lihat laporan absensi</span>
    </a>

    <!-- Leave Request Cards -->
    <a href="<?php echo e(route('leave-requests.create')); ?>" class="flex flex-col items-center p-6 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
        <i class="fas fa-calendar-times text-orange-600 text-3xl mb-3"></i>
        <span class="text-lg font-medium text-orange-900">Izin Pribadi</span>
        <span class="text-sm text-orange-700">Ajukan izin, cuti, sakit, dinas</span>
    </a>

    <?php if(auth()->user()->hasRole(['teacher', 'admin'])): ?>
    <a href="<?php echo e(route('leave-requests.create', ['user_id' => 'student'])); ?>" class="flex flex-col items-center p-6 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
        <i class="fas fa-user-times text-yellow-600 text-3xl mb-3"></i>
        <span class="text-lg font-medium text-yellow-900">Izin Siswa</span>
        <span class="text-sm text-yellow-700">Ajukan izin untuk siswa</span>
    </a>
    <?php endif; ?>
</div>

<!-- Recent Attendance Records -->
<div class="mt-8">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Riwayat Absensi Terakhir</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Masuk</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Keluar</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $colorMap = ['ontime'=>'bg-green-100 text-green-800','late'=>'bg-orange-100 text-orange-800','sick'=>'bg-yellow-100 text-yellow-800','permit'=>'bg-yellow-100 text-yellow-800','duty'=>'bg-yellow-100 text-yellow-800','leave'=>'bg-yellow-100 text-yellow-800','alpha'=>'bg-red-100 text-red-800'];
                                $labelMap = ['ontime'=>'Ontime','late'=>'Terlambat','sick'=>'Sakit','permit'=>'Izin','duty'=>'Dinas','leave'=>'Cuti','alpha'=>'Alpha'];
                            ?>
                            <tr>
                                <td class="px-3 py-2"><?php echo e(\Carbon\Carbon::parse($r->date)->format('d-m-Y')); ?></td>
                                <td class="px-3 py-2"><?php echo e($r->check_in ? \Carbon\Carbon::parse($r->check_in)->format('H:i') : '-'); ?></td>
                                <td class="px-3 py-2"><?php echo e($r->check_out ? \Carbon\Carbon::parse($r->check_out)->format('H:i') : '-'); ?></td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?php echo e($colorMap[$r->status] ?? 'bg-gray-100 text-gray-800'); ?>"><?php echo e($labelMap[$r->status] ?? $r->status); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="px-3 py-2 text-center text-gray-500">Belum ada data absensi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if($recent->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <?php if($recent->onFirstPage()): ?>
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-not-allowed">
                                Sebelumnya
                            </span>
                        <?php else: ?>
                            <a href="<?php echo e($recent->previousPageUrl()); ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Sebelumnya
                            </a>
                        <?php endif; ?>
                        
                        <?php if($recent->hasMorePages()): ?>
                            <a href="<?php echo e($recent->nextPageUrl()); ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Selanjutnya
                            </a>
                        <?php else: ?>
                            <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-not-allowed">
                                Selanjutnya
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Menampilkan
                                <span class="font-medium"><?php echo e($recent->firstItem()); ?></span>
                                sampai
                                <span class="font-medium"><?php echo e($recent->lastItem()); ?></span>
                                dari
                                <span class="font-medium"><?php echo e($recent->total()); ?></span>
                                hasil
                            </p>
                        </div>
                        <div>
                            <?php echo e($recent->links()); ?>

                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\FHL\.cursor\presensia-v2\starter-kit\resources\views/attendance/index.blade.php ENDPATH**/ ?>