<x-app-layout>
    <!-- Create Survey Button -->
    <div class="fixed bottom-8 right-8 z-50">
        <form action="{{ route('surveys.create') }}" method="GET">
            <input type="hidden" name="template" value="table">
            <x-primary-button class="gap-2 text-base py-3 px-6 shadow-lg">
                {{ __('templates.use_template') }}
                <x-fas-arrow-right class="w-4 h-4" />
            </x-primary-button>
        </form>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold text-center mb-8">Unterrichtsbeurteilung durch Schülerinnen und Schüler</h1>

                    <!-- Teacher Behavior Section -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Verhalten des Lehrers</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2 w-1/3">Aussage</th>
                                        <th class="text-center py-2">trifft völlig zu</th>
                                        <th class="text-center py-2">trifft eher zu</th>
                                        <th class="text-center py-2">trifft eher nicht zu</th>
                                        <th class="text-center py-2">trifft überhaupt nicht zu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b">
                                        <td class="py-2 font-medium">Sie/Er ist ...</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    @foreach(['... ungeduldig', '... sicher im Auftreten', '... freundlich', '... energisch und aufbauend', '... tatkräftig, aktiv', '... aufgeschlossen'] as $statement)
                                        <tr class="border-b">
                                            <td class="py-2">{{ $statement }}</td>
                                            @foreach(range(1, 4) as $option)
                                                <td class="text-center">
                                                    <input type="radio" name="behavior_{{ Str::slug($statement) }}" value="{{ $option }}" class="form-radio">
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Teacher Fairness Section -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Bewerten Sie folgende Aussagen</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2 w-1/3">Aussage</th>
                                        <th class="text-center py-2">trifft völlig zu</th>
                                        <th class="text-center py-2">trifft eher zu</th>
                                        <th class="text-center py-2">trifft eher nicht zu</th>
                                        <th class="text-center py-2">trifft überhaupt nicht zu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b">
                                        <td class="py-2 font-medium">Die Lehrerin, der Lehrer ...</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    @foreach(['... bevorzugt manche Schülerinnen oder Schüler.', '... nimmt die Schülerinnen und Schüler ernst.', '... ermutigt und lobt viel.', '... entscheidet immer allein.', '... gesteht eigene Fehler ein.'] as $statement)
                                        <tr class="border-b">
                                            <td class="py-2">{{ $statement }}</td>
                                            @foreach(range(1, 4) as $option)
                                                <td class="text-center">
                                                    <input type="radio" name="fairness_{{ Str::slug($statement) }}" value="{{ $option }}" class="form-radio">
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Class Quality Section -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Wie ist der Unterricht?</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2 w-1/3">Aussage</th>
                                        <th class="text-center py-2">trifft völlig zu</th>
                                        <th class="text-center py-2">trifft eher zu</th>
                                        <th class="text-center py-2">trifft eher nicht zu</th>
                                        <th class="text-center py-2">trifft überhaupt nicht zu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach([
                                        'Die Ziele des Unterrichts sind klar erkennbar.',
                                        'Der Lehrer redet zu viel.',
                                        'Der Lehrer schweift oft vom Thema ab.',
                                        'Die Fragen und Beiträge der Schülerinnen und Schüler werden ernst genommen.',
                                        'Die Sprache des Lehrers ist gut verständlich.',
                                        'Der Lehrer achtet auf Ruhe und Disziplin im Unterricht.',
                                        'Der Unterricht ist abwechslungsreich.',
                                        'Unterrichtsmaterialien sind ansprechend und gut verständlich gestaltet.',
                                        'Der Stoff wird ausreichend wiederholt und geübt.'
                                    ] as $statement)
                                        <tr class="border-b">
                                            <td class="py-2">{{ $statement }}</td>
                                            @foreach(range(1, 4) as $option)
                                                <td class="text-center">
                                                    <input type="radio" name="class_{{ Str::slug($statement) }}" value="{{ $option }}" class="form-radio">
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Evaluation Claims Section -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Bewerten Sie folgende Behauptungen</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2 w-1/3">Aussage</th>
                                        <th class="text-center py-2">trifft völlig zu</th>
                                        <th class="text-center py-2">trifft eher zu</th>
                                        <th class="text-center py-2">trifft eher nicht zu</th>
                                        <th class="text-center py-2">trifft überhaupt nicht zu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach([
                                        'Die Themen der Schulaufgaben werden rechtzeitig vorher bekannt gegeben.',
                                        'Der Schwierigkeitsgrad der Leistungsnachweise entspricht dem der Unterrichtsinhalte.',
                                        'Die Bewertungen sind nachvollziehbar und verständlich.'
                                    ] as $statement)
                                        <tr class="border-b">
                                            <td class="py-2">{{ $statement }}</td>
                                            @foreach(range(1, 4) as $option)
                                                <td class="text-center">
                                                    <input type="radio" name="eval_{{ Str::slug($statement) }}" value="{{ $option }}" class="form-radio">
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Open Feedback Section -->
                    <div class="space-y-6">
                        <div>
                            <label class="block font-medium mb-2">Das hat mir besonders gut gefallen:</label>
                            <textarea rows="3" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div>
                            <label class="block font-medium mb-2">Das hat mir nicht gefallen:</label>
                            <textarea rows="3" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div>
                            <label class="block font-medium mb-2">Verbesserungsvorschläge:</label>
                            <textarea rows="3" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end mt-6">
                        <x-primary-button disabled>
                            Absenden <x-fas-arrow-right class="w-6 h-6 ml-2" />
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
