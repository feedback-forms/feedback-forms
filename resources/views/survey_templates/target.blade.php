<x-app-layout>
    <style>
        path:hover {
            fill: rgba(59, 130, 246, var(--hover-opacity)) !important;
        }

        path.selected {
            fill: rgba(59, 130, 246, var(--hover-opacity)) !important;
        }

        .dark path:hover,
        .dark path.selected {
            fill: rgba(96, 165, 250, var(--hover-opacity)) !important;
        }

        .tooltip {
            position: absolute;
            background-color: rgba(0, 0, 0, 0.75);
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            pointer-events: none;
            font-size: 0.875rem;
            z-index: 50;
        }
    </style>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" x-data="targetDiagram()">
                    <!-- Title and Instructions -->
                    <h1 class="text-2xl font-bold text-center mb-8">Zielscheibe</h1>
                    <p class="text-center mb-10 max-w-3xl mx-auto">
                        Bitte bewerten Sie die angegebenen Teilbereiche und setzen Sie innerhalb der einzelnen Segmente an der nach Ihrer Meinung richtigen Stelle ein Kreuzchen. Je näher das Kreuzchen in der Mitte der Zielscheibe gesetzt wird, desto positiver ist die Bewertung.
                    </p>
                    <p class="text-center mb-36 text-sm text-gray-600 dark:text-gray-400">
                        Die Auswertung der Fragebögen erfolgt anonym.
                    </p>

                    <!-- Target Diagram -->
                    <div class="mb-40 relative mt-20">
                        <div class="aspect-square max-w-2xl mx-auto relative">
                            <svg viewBox="-20 -20 440 440" class="w-full h-full">
                                <!-- Rings -->
                                <circle cx="200" cy="200" r="200" class="fill-gray-100 dark:fill-gray-700 stroke-gray-300 dark:stroke-gray-600" />
                                <circle cx="200" cy="200" r="160" class="fill-none stroke-gray-300 dark:stroke-gray-600" />
                                <circle cx="200" cy="200" r="120" class="fill-none stroke-gray-300 dark:stroke-gray-600" />
                                <circle cx="200" cy="200" r="80" class="fill-none stroke-gray-300 dark:stroke-gray-600" />
                                <circle cx="200" cy="200" r="40" class="fill-none stroke-gray-300 dark:stroke-gray-600" />

                                <!-- Ring Labels -->
                                <text x="205" y="25" class="text-xs fill-gray-500 dark:fill-gray-400">1</text>
                                <text x="205" y="65" class="text-xs fill-gray-500 dark:fill-gray-400">2</text>
                                <text x="205" y="105" class="text-xs fill-gray-500 dark:fill-gray-400">3</text>
                                <text x="205" y="145" class="text-xs fill-gray-500 dark:fill-gray-400">4</text>
                                <text x="205" y="185" class="text-xs fill-gray-500 dark:fill-gray-400">5</text>

                                <!-- Segment Lines -->
                                @for ($i = 0; $i < 8; $i++)
                                    <line
                                        x1="200"
                                        y1="200"
                                        x2="{{ 200 + 200 * cos($i * pi() / 4) }}"
                                        y2="{{ 200 + 200 * sin($i * pi() / 4) }}"
                                        class="stroke-gray-300 dark:stroke-gray-600"
                                    />
                                @endfor

                                <!-- Click Areas -->
                                @foreach(['Ich lerne im Unterricht viel.',
                                        'Die Lehrkraft hat ein großes Hintergrundwissen.',
                                        'Die Lehrkraft ist immer gut vorbereitet.',
                                        'Die Lehrkraft zeigt Interesse an ihren Schülern.',
                                        'Die Lehrkraft sorgt für ein gutes Lernklima in der Klasse.',
                                        'Die Notengebung ist fair und nachvollziehbar.',
                                        'Ich konnte dem Unterricht immer gut folgen.',
                                        'Der Unterricht wird vielfältig gestaltet.'] as $index => $statement)
                                    @foreach([200, 160, 120, 80, 40] as $ringIndex => $outerRadius)
                                        @php
                                            $innerRadius = $ringIndex < 4 ? [160, 120, 80, 40][$ringIndex] : 0;
                                            $hoverOpacity = ($ringIndex + 1) * 0.15;
                                        @endphp
                                        <path
                                            d="M 200 200
                                               L {{ 200 + $outerRadius * cos($index * pi() / 4) }} {{ 200 + $outerRadius * sin($index * pi() / 4) }}
                                               A {{ $outerRadius }} {{ $outerRadius }} 0 0 1 {{ 200 + $outerRadius * cos(($index + 1) * pi() / 4) }} {{ 200 + $outerRadius * sin(($index + 1) * pi() / 4) }}
                                               L {{ 200 + $innerRadius * cos(($index + 1) * pi() / 4) }} {{ 200 + $innerRadius * sin(($index + 1) * pi() / 4) }}
                                               A {{ $innerRadius }} {{ $innerRadius }} 0 0 0 {{ 200 + $innerRadius * cos($index * pi() / 4) }} {{ 200 + $innerRadius * sin($index * pi() / 4) }}
                                               Z"
                                            class="fill-transparent cursor-pointer transition-colors"
                                            :class="{ 'selected': isSelected({{ $index }}, {{ $ringIndex + 1 }}) }"
                                            style="--hover-opacity: {{ $hoverOpacity }};"
                                            @click="toggleRating($event, {{ $index }}, {{ $ringIndex + 1 }})"
                                            @mouseenter="showTooltip($event, '{{ $statement }} - Bewertung: {{ $ringIndex + 1 }}')"
                                            @mouseleave="hideTooltip()"
                                        />
                                    @endforeach
                                @endforeach

                                <!-- Rating Markers -->
                                <g x-html="marks.map(mark => `
                                    <circle
                                        cx='${getMarkPosition(mark).cx}'
                                        cy='${getMarkPosition(mark).cy}'
                                        r='3'
                                        class='fill-blue-500'
                                        style='opacity: 0;'
                                        transform='translate(0,0)'
                                    />`).join('')">
                                </g>

                            </svg>

                            <!-- Tooltip -->
                            <div
                                x-show="tooltip.show"
                                x-cloak
                                class="tooltip"
                                :style="`left: ${tooltip.x}px; top: ${tooltip.y}px;`"
                                x-text="tooltip.text">
                            </div>

                            <!-- Labels -->
                            @foreach(['Ich lerne im Unterricht viel.',
                                    'Die Lehrkraft hat ein großes Hintergrundwissen.',
                                    'Die Lehrkraft ist immer gut vorbereitet.',
                                    'Die Lehrkraft zeigt Interesse an ihren Schülern.',
                                    'Die Lehrkraft sorgt für ein gutes Lernklima in der Klasse.',
                                    'Die Notengebung ist fair und nachvollziehbar.',
                                    'Ich konnte dem Unterricht immer gut folgen.',
                                    'Der Unterricht wird vielfältig gestaltet.'] as $index => $statement)
                                @php
                                    $angle = ($index * pi() / 4) + (pi() / 8);
                                    $labelRadius = 410;

                                    $offsetX = $labelRadius * cos($angle);
                                    $offsetY = $labelRadius * sin($angle);

                                    // Text alignment based on position
                                    if ($index >= 6) {
                                        $textAlign = 'right';
                                        $maxWidth = '180px';
                                    } elseif ($index <= 1) {
                                        $textAlign = 'left';
                                        $maxWidth = '180px';
                                    } else {
                                        $textAlign = 'center';
                                        $maxWidth = '180px';
                                    }
                                @endphp
                                <div class="absolute text-sm leading-tight"
                                     style="
                                        left: calc(50% + {{ $offsetX }}px);
                                        top: calc(50% + {{ $offsetY }}px);
                                        transform: translate(-50%, -50%);
                                        max-width: {{ $maxWidth }};
                                        width: {{ $maxWidth }};
                                        text-align: {{ $textAlign }};
                                     ">
                                    {{ $statement }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Open Feedback Section -->
                    <div class="max-w-3xl mx-auto mt-32">
                        <label class="block font-medium mb-4">
                            Was ich sonst noch anmerken möchte:
                        </label>
                        <textarea
                            x-ref="feedbackText"
                            x-model="feedback"
                            rows="4"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        ></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end mt-8">
                        <x-primary-button @click="handleSubmit">
                            Absenden <x-fas-arrow-right class="w-6 h-6 ml-2" />
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('targetDiagram', () => ({
                marks: [],
                feedback: '',
                tooltip: {
                    show: false,
                    x: 0,
                    y: 0,
                    text: ''
                },
                init() {
                    // Initialize with an empty array
                    this.marks = [];
                },
                isSelected(segment, rating) {
                    return this.marks.some(m => m.segment === segment && m.rating === rating);
                },
                toggleRating(event, segment, rating) {
                    event.preventDefault();
                    const existingMarkIndex = this.marks.findIndex(m => m.segment === segment);

                    if (existingMarkIndex !== -1) {
                        if (this.marks[existingMarkIndex].rating === rating) {
                            // Remove the mark if clicking the same rating
                            this.marks.splice(existingMarkIndex, 1);
                        } else {
                            // Update the rating if clicking a different rating
                            this.marks[existingMarkIndex].rating = rating;
                        }
                    } else {
                        // Add new mark
                        this.marks.push({ segment, rating });
                    }

                    // Create a new array reference to trigger reactivity
                    this.marks = [...this.marks];
                },
                getMarkPosition(mark) {
                    if (!mark || typeof mark.segment === 'undefined' || typeof mark.rating === 'undefined') {
                        return { cx: 200, cy: 200 };
                    }
                    const radius = 200 - (mark.rating * 40) + 20; // Adjust the position to be in the middle of each ring
                    const angle = (mark.segment * Math.PI / 4) + (Math.PI / 8); // Center in segment
                    return {
                        cx: 200 + radius * Math.cos(angle),
                        cy: 200 + radius * Math.sin(angle)
                    };
                },
                showTooltip(event, text) {
                    const rect = event.target.closest('svg').getBoundingClientRect();
                    this.tooltip = {
                        show: true,
                        x: event.clientX - rect.left + 10,
                        y: event.clientY - rect.top + 10,
                        text
                    };
                },
                hideTooltip() {
                    this.tooltip.show = false;
                },
                getStatementForSegment(segment) {
                    const statements = [
                        'Ich lerne im Unterricht viel.',
                        'Die Lehrkraft hat ein großes Hintergrundwissen.',
                        'Die Lehrkraft ist immer gut vorbereitet.',
                        'Die Lehrkraft zeigt Interesse an ihren Schülern.',
                        'Die Lehrkraft sorgt für ein gutes Lernklima in der Klasse.',
                        'Die Notengebung ist fair und nachvollziehbar.',
                        'Ich konnte dem Unterricht immer gut folgen.',
                        'Der Unterricht wird vielfältig gestaltet.'
                    ];
                    return statements[segment] || '';
                },
                handleSubmit() {
                    const surveyData = {
                        ratings: this.marks.map(mark => ({
                            segment: mark.segment,
                            rating: mark.rating,
                            statement: this.getStatementForSegment(mark.segment)
                        })),
                        feedback: this.$refs.feedbackText?.value || ''
                    };

                    // Log the results
                    console.log('Survey Results:', surveyData);
                    console.table(surveyData.ratings);
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>