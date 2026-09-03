@props([
    'icon' => 'smile',
    'iconColor' => 'text-gray-400',
    'title' => 'Tidak ada data',
    'subtitle' => null,
    'padding' => 'py-8'
])

<div class="text-center {{ $padding }} text-gray-400 text-xs flex flex-col items-center justify-center">
    <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center mb-2.5 border border-gray-100 shadow-xs">
        <i data-lucide="{{ $icon }}" class="h-6 w-6 {{ $iconColor }}"></i>
    </div>
    <p class="font-semibold text-gray-600 text-xs">{{ $title }}</p>
    @if($subtitle)
        <p class="text-[11px] text-gray-400 mt-0.5 max-w-xs">{{ $subtitle }}</p>
    @endif
</div>
