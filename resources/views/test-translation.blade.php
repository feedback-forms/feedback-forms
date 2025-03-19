<div>
    <h1>Translation Test</h1>

    <h2>Current Locale: {{ app()->getLocale() }}</h2>

    <h3>English Locale Test:</h3>
    <ul>
        @php App::setLocale('en'); @endphp
        <li>admin.survey_aggregation: {{ __('admin.survey_aggregation') }}</li>
        <li>admin.select_filters_message: {{ __('admin.select_filters_message') }}</li>
        <li>admin.text_answers_excluded: {{ __('admin.text_answers_excluded') }}</li>
    </ul>

    <h3>German Locale Test:</h3>
    <ul>
        @php App::setLocale('de'); @endphp
        <li>admin.survey_aggregation: {{ __('admin.survey_aggregation') }}</li>
        <li>admin.select_filters_message: {{ __('admin.select_filters_message') }}</li>
        <li>admin.text_answers_excluded: {{ __('admin.text_answers_excluded') }}</li>
    </ul>

    @php App::setLocale(config('app.locale')); @endphp
</div>