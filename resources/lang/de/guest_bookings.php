<?php

// Gast-Buchungslink (/b/{token} → /your-booking/{id}). Wording: "deine Buchung", nie "dein Konto"
// oder "dein Profil" (Plan "Guest Booking Access").

return [
    'title' => 'Deine Buchung',
    'eyebrow' => 'Buchungsdetails',
    'eyebrow_voucher' => 'Gutscheinbestellung',
    'subtitle' => 'Alles zu deiner Buchung an einem Ort. Heb die E-Mail mit diesem Link auf: Er öffnet immer diese Seite.',
    'redacted_note' => 'Diese Reise liegt mehr als 90 Tage zurück. Wir zeigen deshalb nur noch die wichtigsten Angaben, Kontaktdaten und Nachrichten sind ausgeblendet.',
    'contact_hidden' => 'Nach der Tour ausgeblendet',
    'add_to_calendar' => 'In den Kalender',
    'download_voucher' => 'Gutschein herunterladen (PDF)',
    'calendar_description' => 'Deine Buchung bei Catch A Guide. Guide: :guide',
    'help_title' => 'Möchtest du etwas ändern?',
    'help_tour' => 'Für einen anderen Termin, mehr Personen oder eine Stornierung schreib deinem Guide oder uns. Wir klären das mit deinem Guide.',
    'help_other' => 'Bei Fragen oder Änderungswünschen schreib uns. Wir melden uns so schnell wie möglich.',
    'contact_support' => 'Support kontaktieren',
    'privacy' => 'Wir zeigen dir die Angaben, die du bei der Buchung gemacht hast, damit du und dein Guide alles für die Tour habt. Wir legen dafür kein Konto an und nutzen diese Seite nicht für Werbung.',
    'privacy_link' => 'Datenschutzerklärung',

    'types' => [
        'tour' => 'Tourbuchung',
        'camp' => 'Anfrage für ein Angelcamp',
        'trip' => 'Anfrage für eine Angelreise',
        'voucher' => 'Gutscheinbestellung',
    ],

    'unavailable' => [
        'title' => 'Dieser Link funktioniert hier nicht',
        'text' => 'Öffne deine Buchung über den Link aus deiner Buchungs-E-Mail. Klappt es dann immer noch nicht, wurde der Link vielleicht ersetzt: Schreib uns, und wir schicken dir den aktuellen.',
        'signed_in' => 'Meine Buchungen',
        'home' => 'Zur Startseite',
    ],

    // "Meine Buchung finden" (/my-bookings): alle Buchungen zu einer E-Mail, per Anmeldelink.
    'find' => [
        'nav' => 'Meine Buchung finden',
        'title' => 'Meine Buchung finden',
        'intro' => 'Gib die E-Mail-Adresse ein, mit der du gebucht hast. Wir schicken dir einen Link, der alle deine Buchungen zeigt: Touren, Angelcamps, Reisen und Gutscheine. Ganz ohne Passwort.',
        'email' => 'E-Mail-Adresse',
        'submit' => 'Link zuschicken',
        'sent' => 'Wenn wir Buchungen zu dieser E-Mail-Adresse haben, haben wir dir einen Link geschickt.',
        'sent_hint' => 'Schau in dein Postfach (und in den Spam-Ordner). Der Link funktioniert einmal und :minutes Minuten lang.',
        'throttled' => 'Zu viele Anfragen von dieser Verbindung. Bitte versuch es in einer Stunde noch einmal.',
        'confirm_title' => 'Deine Buchungen sind bereit',
        'confirm_text' => 'Tippe auf den Button, um alle Buchungen zu deiner E-Mail-Adresse zu sehen. Dieser Browser zeigt sie 7 Tage lang oder bis du dich abmeldest.',
        'confirm_button' => 'Meine Buchungen anzeigen',
        'link_expired_title' => 'Dieser Link ist abgelaufen',
        'link_expired_text' => 'Anmeldelinks funktionieren einmal und 30 Minuten lang. Fordere einfach einen neuen an.',
        'new_link' => 'Neuen Link zuschicken',
        'expired_session' => 'Deine Anmeldung ist abgelaufen. Fordere einen neuen Link an, um deine Buchungen wieder zu sehen.',
        'signed_out' => 'Du bist abgemeldet. Dieser Browser zeigt deine Buchungen nicht mehr.',
        'list_title' => 'Deine Buchungen',
        'list_subtitle' => 'Alles, was mit deiner E-Mail-Adresse gebucht wurde. Öffne eine Buchung für die Details, den Kalendereintrag oder den Gutschein.',
        'sign_out' => 'Abmelden',
        'open_booking' => 'Buchung öffnen',
        'see_all' => 'Mehrmals gebucht? Alle deine Buchungen ansehen',
        'login_hint' => 'Ohne Registrierung gebucht?',
        'login_hint_link' => 'Finde deine Buchung',
        'mail' => [
            'subject' => 'Dein Link zu deinen Buchungen',
            'title' => 'Deine Buchungen',
            'body' => 'hier ist der Link, den du angefordert hast. Er zeigt alle Buchungen zu dieser E-Mail-Adresse.',
            'button' => 'Meine Buchungen anzeigen',
            'hint' => 'Der Link funktioniert einmal und 30 Minuten lang.',
            'not_you' => 'Du hast das nicht angefordert? Dann kannst du diese E-Mail ignorieren: Ohne diesen Link sieht niemand deine Buchungen.',
        ],
    ],

    'mail' => [
        'button' => 'Buchung ansehen',
        'button_voucher' => 'Bestellung ansehen',
        'hint' => 'Deine Buchung jederzeit, ganz ohne Passwort. Heb diese E-Mail auf.',
        'link_subject' => 'Der Link zu deiner Buchung',
        'link_title' => 'Der Link zu deiner Buchung',
        'link_greeting' => 'Hallo,',
        'link_body' => 'hier ist der Link zu deiner :type. Dort siehst du den aktuellen Stand und alle Details, ohne Passwort.',
        'link_not_you' => 'Du hast das nicht angefordert? Dann kannst du diese E-Mail ignorieren. Der Link zeigt nur diese eine Buchung.',
    ],

    'admin' => [
        'send' => 'Buchungslink senden',
        'revoke' => 'Link sperren und neuen senden',
        'confirm_send' => 'Link zu dieser Buchung an :email senden?',
        'confirm_revoke' => 'Den aktuellen Link sperren (er funktioniert sofort nicht mehr) und einen neuen an :email senden?',
        'sent' => 'Buchungslink an :email gesendet.',
        'revoked' => 'Alter Link gesperrt. Neuer Link an :email gesendet.',
        'no_email' => 'Diese Buchung hat keine E-Mail-Adresse, daher kann kein Link gesendet werden.',
    ],
];
