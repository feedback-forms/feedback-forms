<?php

return [
    'surveys' => 'Umfragen',
    'expired' => 'Abgelaufen',
    'cancelled' => 'Abgebrochen',
    'running' => 'Läuft',
    'create_new_survey' => 'Neue Umfrage erstellen',
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
        'textarea' => 'Textfeld',
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
];
