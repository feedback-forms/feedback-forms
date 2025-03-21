<?php

return [
    'surveys' => 'Umfragen',
    'expired' => 'Abgelaufen',
    'cancelled' => 'Abgebrochen',
    'running' => 'Läuft',
    'create_new_survey' => 'Neue Umfrage erstellen',
    'survey_name' => 'Name der Umfrage',
    'select_school_year' => 'Schuljahr',
    'select_school_year_placeholder' => 'Schuljahr auswählen...',
    'select_department' => 'Abteilung',
    'select_department_placeholder' => 'Abteilung auswählen...',
    'select_grade_level' => 'Jahrgangsstufe',
    'select_grade_level_placeholder' => 'Jahrgangsstufe auswählen...',
    'select_class' => 'Klasse',
    'select_class_placeholder' => 'Klasse auswählen...',
    'select_subject' => 'Fach',
    'select_subject_placeholder' => 'Fach auswählen...',
    'response_limit' => 'Antwortlimit',
    'response_limit_help' => '-1 für unbegrenzte Antworten',
    'expire_date' => 'Ablaufdatum',
    'create_survey' => 'Umfrage erstellen',
    'created_successfully' => 'Umfrage wurde erfolgreich erstellt',
    'creation_failed' => 'Fehler beim Erstellen der Umfrage',
    'select_template_first' => 'Bitte wählen Sie zuerst eine Vorlage aus, um eine Umfrage zu erstellen',
    'template_not_found' => 'Die ausgewählte Vorlage wurde nicht gefunden',

    // Template information
    'template_information' => 'Vorlageninformation',
    'template_info_description' => 'Diese Umfrage wird mit der folgenden Vorlage erstellt. Die Fragen werden automatisch aus der Vorlage übernommen.',

    // Questions
    'questions' => 'Fragen',
    'add_question' => 'Frage hinzufügen',
    'question_text' => 'Fragetext',
    'question_type' => 'Fragetyp',
    'select_question_type' => 'Fragetyp auswählen...',

    // Question types
    'question_types' => [
        'range' => 'Bereich (1-5)',
        'checkboxes' => 'Checkboxen',
        'checkbox' => 'Ja/Nein Frage',
        'textarea' => 'Textfeld',
    ],

    // Checkbox options
    'checkbox_options' => [
        'yes' => 'Ja',
        'no' => 'Nein',
        'na' => 'Nicht zutreffend'
    ],

    // Multiple choice options
    'checkboxes_options' => [
        'strongly_agree' => 'Stimme voll zu',
        'agree' => 'Stimme eher zu',
        'neutral' => 'Neutral',
        'disagree' => 'Stimme eher nicht zu',
        'strongly_disagree' => 'Stimme überhaupt nicht zu'
    ],

    // Departments
    'departments' => [
        'ait' => 'Automatisierungstechnik',
        'it' => 'Informationstechnologie',
        'et' => 'Elektronik',
        'mb' => 'Maschinenbau',
    ],

    // Subjects
    'subjects' => [
        'math' => 'Mathematik',
        'english' => 'Englisch',
        'science' => 'Naturwissenschaften',
        'history' => 'Geschichte',
    ],

    // Survey management
    'edit_survey' => 'Umfrage bearbeiten',
    'back_to_surveys' => 'Zurück zu Umfragen',
    'back_to_overview' => 'Zurück zur Übersicht',
    'save_changes' => 'Änderungen speichern',
    'updated_successfully' => 'Umfrage wurde erfolgreich aktualisiert',
    'update_failed' => 'Fehler beim Aktualisieren der Umfrage',
    'survey_status' => 'Umfragestatus',
    'access_key' => 'Zugriffsschlüssel',
    'responses' => 'Antworten',
    'created_at' => 'Erstellt am',
    'updated_at' => 'Aktualisiert am',
    'view_details' => 'Details anzeigen',
    'edit' => 'Bearbeiten',
    'survey_title' => 'Umfragetitel',
    'expires' => 'Läuft ab',
    'status' => [
        'expired' => 'Abgelaufen',
        'running' => 'Läuft',
        'cancelled' => 'Abgebrochen'
    ],
    'active' => 'Aktiv',
    'statistics' => 'Statistiken',

    // Survey statistics
    'survey_statistics' => 'Umfrage-Statistiken',
    'survey_details' => 'Umfrage-Details',
    'question_statistics' => 'Fragenstatistiken',
    'no_responses_yet' => 'Noch keine Antworten',
    'no_responses_explanation' => 'Diese Umfrage hat noch keine Antworten erhalten. Statistiken werden angezeigt, sobald Antworten eingegangen sind.',
    'target_survey_results' => 'Zielumfrage-Ergebnisse',
    'target_results' => 'Zielergebnisse',
    'segment_ratings_description' => 'Segmentbewertungen für Zieldiagramm',
    'responses_count' => ':count Antwort(en) erhalten',
    'segment' => 'Segment',
    'average_rating_short' => 'Durchschn. Bewertung',
    'distribution' => 'Verteilung',
    'no_target_responses' => 'Für diese Zielscheiben-Umfrage wurden noch keine Antworten empfangen.',
    'table_survey_results' => 'Tabellen-Umfrageergebnisse',
    'ratings_grouped_by_category' => 'Bewertungen nach Kategorie gruppiert',
    'smiley_survey_results' => 'Smiley-Feedback Ergebnisse',
    'smiley_feedback_description' => 'Positives und negatives Feedback der Befragten',
    'no_positive_feedback' => 'Es wurde noch kein positives Feedback gegeben.',
    'no_negative_feedback' => 'Es wurde noch kein negatives Feedback gegeben.',
    'no_smiley_responses' => 'Für diese Smiley-Feedback-Umfrage wurden noch keine Antworten empfangen.',
    'open_feedback' => 'Offenes Feedback',
    'no_open_feedback' => 'Es wurde noch kein offenes Feedback gegeben.',
    'category' => 'Kategorie',
    'question' => 'Frage',
    'no_category_responses' => 'Für diese Kategorie sind noch keine Antwortstatistiken verfügbar.',
    'no_categories_available' => 'Keine Kategorien verfügbar',
    'no_categories_generated_description' => 'Aus den Umfragefragen konnten keine Kategorien generiert werden.',
    'text_responses_count' => ':count Textantwort(en) erhalten',
    'untitled_survey' => 'Unbenannte Umfrage',
    'question_results' => 'Fragenergebnisse',
    'response' => 'Antwort',
    'no_feedback_responses' => 'Noch keine Feedback-Antworten',
    'general_feedback' => 'Allgemeines Feedback',
    'detailed_feedback' => 'Detailliertes Feedback',
    'additional_comments' => 'Zusätzliche Kommentare',

    // Checkbox specific translations
    'checkbox_feedback' => 'Checkbox-Feedback',
    'checkbox_question_results' => 'Ja/Nein-Fragenergebnisse',
    'multiple_choice_results' => 'Multiple-Choice-Ergebnisse',
    'checkbox_distribution' => 'Antwortverteilung',
    'yes_responses' => 'Ja-Antworten',
    'no_responses' => 'Nein-Antworten',
    'na_responses' => 'Nicht zutreffend-Antworten',
    'submit' => 'Absenden',

    // Survey response
    'survey' => 'Umfrage',
    'access_survey' => 'Umfrage aufrufen',
    'enter_access_key_hint' => 'Geben Sie den 8-stelligen Zugriffsschlüssel ein, den Sie von Ihrem Lehrer erhalten haben',
    'invalid_access_key' => 'Ungültiger Zugriffsschlüssel. Bitte überprüfen und erneut versuchen.',
    'survey_not_available' => 'Diese Umfrage ist nicht mehr verfügbar. Sie ist möglicherweise abgelaufen oder hat das Antwortlimit erreicht.',
    'submit_response' => 'Antwort absenden',
    'response_submitted' => 'Ihre Antwort wurde erfolgreich übermittelt.',
    'submission_failed' => 'Fehler beim Übermitteln Ihrer Antwort.',
    'thank_you' => 'Vielen Dank!',
    'response_received' => 'Ihre Antwort wurde empfangen. Vielen Dank für Ihr Feedback.',
    'access_another_survey' => 'Eine andere Umfrage aufrufen',
    'whoops' => 'Hoppla! Etwas ist schiefgelaufen.',

    // Survey response options
    'strongly_agree' => 'Stimme voll zu',
    'agree' => 'Stimme eher zu',
    'disagree' => 'Stimme eher nicht zu',
    'strongly_disagree' => 'Stimme überhaupt nicht zu',
    'subject' => 'Fach',
    'grade_level' => 'Jahrgangsstufe',
    'class' => 'Klasse',

    // QR Code
    'show_qr' => 'QR anzeigen',
    'qr_code_title' => 'Umfrage QR-Code',
    'scan_to_access' => 'Scannen Sie diesen QR-Code, um auf die Umfrage zuzugreifen',
    'close' => 'Schließen',
    'qr_code_error' => 'QR-Code konnte nicht generiert werden. Bitte versuchen Sie es erneut.',

    // Smiley template
    'smiley' => [
        'positive' => 'Was hat Ihnen gefallen?',
        'negative' => 'Was könnte verbessert werden?',
        'button' => 'Feedback absenden'
    ],

    // Filter options
    'filter_options' => 'Filteroptionen',
    'school_year' => 'Schuljahr',
    'department' => 'Abteilung',
    'grade_level' => 'Jahrgangsstufe',
    'class' => 'Klasse',
    'subject' => 'Fach',
    'all' => 'Alle',
    'all_school_years' => 'Alle Schuljahre',
    'all_departments' => 'Alle Abteilungen',
    'all_grade_levels' => 'Alle Jahrgangsstufen',
    'all_classes' => 'Alle Klassen',
    'all_subjects' => 'Alle Fächer',
    'no_surveys_found' => 'Keine Umfragen gefunden',
    'no_surveys_found_hint' => 'Versuchen Sie, Ihre Filter anzupassen oder erstellen Sie eine neue Umfrage.',
];
