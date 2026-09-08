@props(['cards' => []])

@if(!empty($cards))
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach($cards as $card)
        @php
            $color = $card['color'] ?? 'blue';
            $iconStyle = match($color) {
                'green', 'emerald' => 'bg-emerald-50 text-emerald-600',
                'yellow', 'amber'  => 'bg-amber-50 text-amber-600',
                'red', 'rose'      => 'bg-rose-50 text-rose-600',
                'purple', 'indigo' => 'bg-indigo-50 text-indigo-600',
                default            => 'bg-blue-50 text-blue-600',
            };
        @endphp
        <div class="bg-white shadow rounded-lg p-4 flex items-center justify-between transition-all hover:shadow-md">
            <div class="min-w-0 flex-1 pr-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider truncate">{{ $card['title'] }}</p>
                <p class="text-xl md:text-2xl font-bold text-gray-900 mt-1 truncate">{{ $card['value'] }}</p>
                @if(!empty($card['subtext']))
                    <p class="text-xs text-gray-500 mt-0.5 truncate">{!! $card['subtext'] !!}</p>
                @endif
            </div>
            <div class="h-10 w-10 sm:h-11 sm:w-11 {{ $iconStyle }} rounded-xl flex items-center justify-center text-base sm:text-lg flex-shrink-0">
                <i class="{{ $card['icon'] ?? 'fas fa-chart-bar' }}"></i>
            </div>
        </div>
    @endforeach
</div>
@endif
