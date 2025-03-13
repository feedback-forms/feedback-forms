{{-- Rating Distribution Component --}}
{{-- Parameters:
    $ratingCounts - array of rating counts
    $compactView (optional) - boolean to show a more compact view
    $maxValue (optional) - the maximum value to use for scaling bars consistently across charts
--}}
<div class="flex {{ isset($compactView) && $compactView ? 'space-x-1 items-end' : 'items-end space-x-2 h-20' }}">
    @for ($rating = 1; $rating <= 5; $rating++)
        @php
            $count = $ratingCounts[$rating] ?? 0;
            $total = array_sum($ratingCounts);

            // Use maxValue if provided, otherwise use the total
            $scale = isset($maxValue) && $maxValue > 0 ? $maxValue : max(1, $total);

            $barHeight = isset($compactView) && $compactView
                ? max(4, min(20, ($count / $scale) * 20))
                : max(4, ($count / $scale) * 80);
        @endphp
        <div class="flex flex-col items-center">
            <div class="text-xs {{ $count > 0 ? 'text-blue-500 font-medium' : 'text-gray-400' }}">
                {{ $count }}
            </div>
            <div class="w-6 {{ $count > 0 ? 'bg-blue-500' : 'bg-gray-300 dark:bg-gray-600' }} rounded-t"
                 style="height: {{ $barHeight }}px; opacity: {{ 0.5 + (intval($rating) * 0.1) }};">
            </div>
            <div class="text-xs text-gray-500 mt-1">{{ $rating }}</div>
        </div>
    @endfor
</div>