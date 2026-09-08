@props([
    'action' => url()->current(),
    'resetUrl' => url()->current(),
    'activeFilters' => [],
    'hiddenInputs' => [],
    'title' => null,
    'badgeClass' => 'badge badge-info badge-sm text-xs font-medium',
])

<div class="bg-white shadow rounded-lg mb-6 filter-bar-wrapper">
    <div class="p-4 sm:p-5">
        @if($title)
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-1.5">
                    <i class="fas fa-filter text-blue-600 text-xs"></i>
                    <span>{{ $title }}</span>
                </h3>
            </div>
        @endif

        <form method="GET" action="{{ $action }}" id="filterForm" class="filter-form">
            {{-- Preserved hidden inputs --}}
            @foreach($hiddenInputs as $hKey => $hVal)
                @if($hVal !== null && $hVal !== '')
                    <input type="hidden" name="{{ $hKey }}" value="{{ $hVal }}">
                @endif
            @endforeach

            {{-- Main Filter Controls: Grid with responsive columns --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3 items-end">
                {{ $slot }}

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="inline-flex items-center justify-center gap-1.5 h-9 px-3.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors cursor-pointer flex-1 sm:flex-initial"
                            title="Terapkan filter">
                        <i class="fas fa-filter text-xs"></i>
                        <span>Filter</span>
                    </button>

                    @if(!empty($activeFilters) || request()->hasAny(array_keys(request()->except(array_keys($hiddenInputs)))))
                        <a href="{{ $resetUrl }}" 
                           class="inline-flex items-center justify-center gap-1.5 h-9 px-3 bg-gray-600 hover:bg-gray-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors cursor-pointer flex-1 sm:flex-initial"
                           title="Reset semua filter">
                            <i class="fas fa-undo text-xs"></i>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Active Filter Badges / Chips --}}
        @if(!empty($activeFilters))
            <div class="mt-3.5 pt-3 border-t border-gray-100 flex flex-wrap items-center gap-2">
                <span class="text-xs text-gray-500 font-medium mr-1 flex items-center gap-1">
                    <i class="fas fa-check-circle text-blue-500 text-xs"></i>
                    <span>Filter aktif:</span>
                </span>

                @foreach($activeFilters as $f)
                    <span class="{{ $badgeClass }} inline-flex items-center gap-1 py-1 px-2.5">
                        <span class="opacity-80">{{ $f['label'] }}:</span>
                        <span class="font-semibold">{{ $f['value'] }}</span>
                        @if(!empty($f['removeUrl']))
                            <a href="{{ $f['removeUrl'] }}" 
                               class="hover:opacity-75 cursor-pointer font-bold text-xs ml-1 inline-flex items-center"
                               title="Hapus filter {{ $f['label'] }}">
                                &times;
                            </a>
                        @endif
                    </span>
                @endforeach

                <a href="{{ $resetUrl }}" 
                   class="text-xs text-red-600 hover:text-red-800 font-semibold ml-2 transition-colors">
                    Hapus Semua
                </a>
            </div>
        @endif
    </div>
</div>

<style>
    /* Scoped styling to ensure table filter inputs match Presensia typography & compact styling */
    .filter-bar-wrapper .filter-form input[type="text"],
    .filter-bar-wrapper .filter-form input[type="date"],
    .filter-bar-wrapper .filter-form select {
        height: 2.25rem !important; /* 36px (h-9) */
        min-height: 2.25rem !important;
        padding: 0.375rem 0.625rem !important; /* py-1.5 px-2.5 */
        font-size: 0.75rem !important; /* 12px (text-xs) */
        line-height: 1.25rem !important;
        border-width: 1px !important;
        border-color: #D1D5DB !important; /* border-gray-300 */
        border-radius: 0.5rem !important; /* rounded-lg */
        background-color: #FFFFFF !important;
        font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        color: #1F2937 !important; /* text-gray-800 */
        box-shadow: none !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .filter-bar-wrapper .filter-form input[type="text"]:focus,
    .filter-bar-wrapper .filter-form input[type="date"]:focus,
    .filter-bar-wrapper .filter-form select:focus {
        border-color: #3B82F6 !important; /* blue-500 */
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
        outline: none !important;
    }

    .filter-bar-wrapper .filter-form label {
        font-size: 0.6875rem !important; /* 11px micro-label */
        font-weight: 600 !important;
        color: #6B7280 !important; /* text-gray-500 */
        margin-bottom: 0.25rem !important;
        display: block !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    }

    .filter-bar-wrapper .filter-form select option {
        font-size: 0.8125rem !important;
        padding: 4px 8px !important;
    }
</style>
