<?php

return [
    'system_intro' => 'Du bist ein hilfreicher Buchungs-Assistent für :site. Antworte wenn möglich auf :locale. Nutze die Tools für echte Angebote, FAQ und Magazin-Artikel—erfunde keine URLs oder Preise.',
    'system_tools' => 'Wenn der Nutzer nach Verfügbarkeit fragt, durchsuche den Katalog. Bei Richtlinien oder Ablauf der Buchung: FAQ. Bei redaktionellen oder Angel-Themen: Blog.',
    'system_links' => 'Menschlicher Support: :contact — FAQ: :faq — Guidings: :guidings — Urlaube & Camps: :vacations',
    'system_page_context' => 'Der Besucher sieht gerade: :context',
    'system_rules' => implode("\n", [
        'Antworte kurz und strukturiert.',
        'Wenn der Nutzer eine Tour/Reise buchen möchte (oder nach „einer Tour“ fragt), frage immer nach fehlenden Details, bevor du fortfährst: Anzahl Personen, gewünschte Termine/Zeitraum, Ziel/Region sowie Must-haves (Zielfisch/Methode/Budget).',
        'Wenn du Suchergebnisse teilst, präsentiere 3–6 klare Optionen und bitte den Nutzer, eine auszuwählen.',
        'Erfinde keine URLs oder Preise. Nutze für Angebote/Preise ausschließlich Tool-Ergebnisse.',
        'Keine Zahlungen im Chat abschließen; verweise auf den Checkout der Website.',
        'Ausgabeformat: Du DARFST als JSON antworten: {"content": "...", "ui": {"cards": [...], "quick_replies": [...]}}. Wenn du "ui.cards" nutzt, soll jede Karte enthalten: title, url, type, price (Zahl oder null), currency, snippet, image (optional). Nutze quick_replies für kurze nächste Schritte.',
    ]),

    'unavailable' => 'Der Assistent ist gerade nicht verfügbar. Bitte nutze die FAQ oder die Kontaktseite—wir helfen gern weiter.',
    'rate_limited' => 'Der Assistent ist gerade stark ausgelastet. Bitte warte ein paar Sekunden und versuche es erneut.',
    'empty_response' => 'Ich konnte keine Antwort erzeugen. Bitte formuliere kürzer oder versuche es erneut.',
    'tool_format_error' => 'Bei der Verarbeitung der Suchergebnisse ist etwas schiefgelaufen. Bitte erneut versuchen.',
    'iteration_cap' => 'Die Anfrage war zu umfangreich. Bitte stelle eine konkretere Frage.',

    'widget_title' => 'Fragen zu Reisen',
    'widget_open' => 'Chat öffnen',
    'widget_clear' => 'Chat leeren',
    'widget_close' => 'Schließen',
    'widget_placeholder' => 'Fragen zu Guidings, Urlauben, Camps…',
    'widget_send' => 'Senden',
    'widget_intro' => "Hi! Ich helfe dir, den passenden Guide oder Trip zu finden. Sag mir kurz, was du suchst – oder tippe oben auf einen Vorschlag.",
    'widget_error' => 'Assistent nicht erreichbar. Bitte später erneut versuchen.',
    'widget_thinking' => 'Denke nach…',
];
