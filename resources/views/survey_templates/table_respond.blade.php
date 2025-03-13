<x-survey-layout>
    <div x-data="tableFeedback()">
        <form method="POST" action="{{ route('surveys.submit', $survey->accesskey) }}" id="surveyForm">
            @csrf
            <input type="hidden" name="responses" x-bind:value="JSON.stringify({
                ratings: formatRatingsForSubmission(),
                feedback: {
                    positive: positive,
                    negative: negative,
                    suggestions: suggestions
                }
            })">

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <!-- Survey Information -->
                            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <h1 class="text-2xl font-bold mb-4">
                                    {{ $survey->feedback_template->title ?? 'Unterrichtsbeurteilung durch Schülerinnen und Schüler' }}
                                </h1>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                                    @if($survey->subject)
                                        <div>
                                            <span class="font-semibold">{{ __('surveys.subject') }}:</span>
                                            <span>{{ $survey->subject }}</span>
                                        </div>
                                    @endif

                                    @if($survey->grade_level)
                                        <div>
                                            <span class="font-semibold">{{ __('surveys.grade_level') }}:</span>
                                            <span>{{ $survey->grade_level }}</span>
                                        </div>
                                    @endif

                                    @if($survey->class)
                                        <div>
                                            <span class="font-semibold">{{ __('surveys.class') }}:</span>
                                            <span>{{ $survey->class }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Session Status -->
                            <x-auth-session-status class="mb-4" :status="session('status')" />

                            <!-- Validation Errors -->
                            @if ($errors->any())
                                <div class="mb-4">
                                    <div class="font-medium text-red-600 dark:text-red-400">
                                        {{ __('surveys.whoops') }}
                                    </div>

                                    <ul class="mt-3 list-disc list-inside text-sm text-red-600 dark:text-red-400">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Error Message -->
                            @if (session('error'))
                                <div class="mb-4 font-medium text-sm text-red-600 dark:text-red-400">
                                    {{ session('error') }}
                                </div>
                            @endif

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
                                            @foreach(['... ungeduldig', '... sicher im Auftreten', '... freundlich', '... energisch und aufbauend', '... tatkräftig, aktiv', '... aufgeschlossen'] as $index => $statement)
                                                <tr class="border-b">
                                                    <td class="py-2">{{ $statement }}</td>
                                                    @foreach(range(1, 4) as $option)
                                                        <td class="text-center">
                                                            <input
                                                                type="radio"
                                                                name="behavior_{{ Str::slug($statement) }}"
                                                                value="{{ $option }}"
                                                                x-on:change="setBehavior('{{ Str::slug($statement) }}', {{ $option }})"
                                                                class="form-radio">
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
                                            @foreach(['... bevorzugt manche Schülerinnen oder Schüler.', '... nimmt die Schülerinnen und Schüler ernst.', '... ermutigt und lobt viel.', '... entscheidet immer allein.', '... gesteht eigene Fehler ein.'] as $index => $statement)
                                                <tr class="border-b">
                                                    <td class="py-2">{{ $statement }}</td>
                                                    @foreach(range(1, 4) as $option)
                                                        <td class="text-center">
                                                            <input
                                                                type="radio"
                                                                name="fairness_{{ Str::slug($statement) }}"
                                                                value="{{ $option }}"
                                                                x-on:change="setFairness('{{ Str::slug($statement) }}', {{ $option }})"
                                                                class="form-radio">
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
                                            ] as $index => $statement)
                                                <tr class="border-b">
                                                    <td class="py-2">{{ $statement }}</td>
                                                    @foreach(range(1, 4) as $option)
                                                        <td class="text-center">
                                                            <input
                                                                type="radio"
                                                                name="class_{{ Str::slug($statement) }}"
                                                                value="{{ $option }}"
                                                                x-on:change="setClassQuality('{{ Str::slug($statement) }}', {{ $option }})"
                                                                class="form-radio">
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
                                            ] as $index => $statement)
                                                <tr class="border-b">
                                                    <td class="py-2">{{ $statement }}</td>
                                                    @foreach(range(1, 4) as $option)
                                                        <td class="text-center">
                                                            <input
                                                                type="radio"
                                                                name="eval_{{ Str::slug($statement) }}"
                                                                value="{{ $option }}"
                                                                x-on:change="setEvaluation('{{ Str::slug($statement) }}', {{ $option }})"
                                                                class="form-radio">
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
                                    <textarea x-model="positive" rows="3" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>
                                <div>
                                    <label class="block font-medium mb-2">Das hat mir nicht gefallen:</label>
                                    <textarea x-model="negative" rows="3" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>
                                <div>
                                    <label class="block font-medium mb-2">Verbesserungsvorschläge:</label>
                                    <textarea x-model="suggestions" rows="3" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>
                            </div>

                            <div id="validation-error" class="hidden mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                                {{ __('surveys.please_answer_all_questions') }}
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end mt-6">
                                <x-primary-button type="submit">
                                    Absenden <x-fas-arrow-right class="w-6 h-6 ml-2" />
                                </x-primary-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tableFeedback', () => ({
                behavior: {},
                fairness: {},
                classQuality: {},
                evaluation: {},
                positive: '',
                negative: '',
                suggestions: '',

                // Format the ratings in the expected format for the backend
                formatRatingsForSubmission() {
                    // Create a ratings object that matches the expected format
                    const ratings = {};

                    // Map all the ratings from different question categories
                    const questionMappings = {
                        '... ungeduldig': this.behavior['ungeduldig'],
                        '... sicher im Auftreten': this.behavior['sicher-im-auftreten'],
                        '... freundlich': this.behavior['freundlich'],
                        '... energisch und aufbauend': this.behavior['energisch-und-aufbauend'],
                        '... tatkräftig, aktiv': this.behavior['tatkraftig-aktiv'],
                        '... aufgeschlossen': this.behavior['aufgeschlossen'],

                        '... bevorzugt manche Schülerinnen oder Schüler.': this.fairness['bevorzugt-manche-schulerinnen-oder-schuler'],
                        '... nimmt die Schülerinnen und Schüler ernst.': this.fairness['nimmt-die-schulerinnen-und-schuler-ernst'],
                        '... ermutigt und lobt viel.': this.fairness['ermutigt-und-lobt-viel'],
                        '... entscheidet immer allein.': this.fairness['entscheidet-immer-allein'],
                        '... gesteht eigene Fehler ein.': this.fairness['gesteht-eigene-fehler-ein'],

                        'Die Ziele des Unterrichts sind klar erkennbar.': this.classQuality['die-ziele-des-unterrichts-sind-klar-erkennbar'],
                        'Der Lehrer redet zu viel.': this.classQuality['der-lehrer-redet-zu-viel'],
                        'Der Lehrer schweift oft vom Thema ab.': this.classQuality['der-lehrer-schweift-oft-vom-thema-ab'],
                        'Die Fragen und Beiträge der Schülerinnen und Schüler werden ernst genommen.': this.classQuality['die-fragen-und-beitrage-der-schulerinnen-und-schuler-werden-ernst-genommen'],
                        'Die Sprache des Lehrers ist gut verständlich.': this.classQuality['die-sprache-des-lehrers-ist-gut-verstandlich'],
                        'Der Lehrer achtet auf Ruhe und Disziplin im Unterricht.': this.classQuality['der-lehrer-achtet-auf-ruhe-und-disziplin-im-unterricht'],
                        'Der Unterricht ist abwechslungsreich.': this.classQuality['der-unterricht-ist-abwechslungsreich'],
                        'Unterrichtsmaterialien sind ansprechend und gut verständlich gestaltet.': this.classQuality['unterrichtsmaterialien-sind-ansprechend-und-gut-verstandlich-gestaltet'],
                        'Der Stoff wird ausreichend wiederholt und geübt.': this.classQuality['der-stoff-wird-ausreichend-wiederholt-und-geubt'],

                        'Die Themen der Schulaufgaben werden rechtzeitig vorher bekannt gegeben.': this.evaluation['die-themen-der-schulaufgaben-werden-rechtzeitig-vorher-bekannt-gegeben'],
                        'Der Schwierigkeitsgrad der Leistungsnachweise entspricht dem der Unterrichtsinhalte.': this.evaluation['der-schwierigkeitsgrad-der-leistungsnachweise-entspricht-dem-der-unterrichtsinhalte'],
                        'Die Bewertungen sind nachvollziehbar und verständlich.': this.evaluation['die-bewertungen-sind-nachvollziehbar-und-verstandlich']
                    };

                    // Add each rating to the ratings object, skipping undefined values
                    for (const [question, rating] of Object.entries(questionMappings)) {
                        if (rating !== undefined) {
                            ratings[question] = rating;
                        }
                    }

                    return ratings;
                },

                setBehavior(key, value) {
                    this.behavior[key] = value;
                },
                setFairness(key, value) {
                    this.fairness[key] = value;
                },
                setClassQuality(key, value) {
                    this.classQuality[key] = value;
                },
                setEvaluation(key, value) {
                    this.evaluation[key] = value;
                }
            }));
        });
    </script>
    @endpush
</x-survey-layout>