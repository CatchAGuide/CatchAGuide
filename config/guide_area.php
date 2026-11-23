<?php

/*
|--------------------------------------------------------------------------
| Guide Area (redesigned guide dashboard under /guide/*)
|--------------------------------------------------------------------------
|
| Every guide uses it (the per-guide beta flag users.guide_area_beta is no longer checked;
| the column stays). `enabled` is the global kill switch: false sends guides back to the old
| /profile/* guide pages.
|
*/

return [

    'enabled' => (bool) env('GUIDE_AREA_ENABLED', true),

    // Customer pages of /profile/* (overview, my bookings, favourites, account) in the Guide Area
    // design. false brings back the old profile pages for customers. Guides get these pages
    // inside the Guide Area sidebar while `enabled` is on.
    'customer_enabled' => (bool) env('CUSTOMER_AREA_ENABLED', true),

    'customer' => [
        'per_page' => 8,
        'password_min' => 8,
    ],

    'api' => [
        // Lifetime of bearer tokens from POST /api/guide/tokens; 0 = never expire.
        'token_days' => (int) env('GUIDE_AREA_API_TOKEN_DAYS', 90),
    ],

    'profile' => [
        'display_name_max' => 60,
        'about_me_max' => 1000,
        'tags_max_per_kind' => 15,
        'tag_max_length' => 40,
    ],

    /*
    | Languages a guide can pick. Stored in user_information.languages as a comma-separated
    | list of these English names, because public pages (getLanguagesWithFlags(), translate())
    | read that string. Labels come from guide_area.languages.{code}.
    */
    'languages' => [
        'de' => 'German',
        'en' => 'English',
        'nl' => 'Dutch',
        'fr' => 'French',
        'es' => 'Spanish',
        'it' => 'Italian',
        'pt' => 'Portuguese',
        'da' => 'Danish',
        'sv' => 'Swedish',
        'no' => 'Norwegian',
        'fi' => 'Finnish',
        'pl' => 'Polish',
        'cs' => 'Czech',
        'hu' => 'Hungarian',
        'hr' => 'Croatian',
        'sl' => 'Slovenian',
        'ru' => 'Russian',
        'tr' => 'Turkish',
        'el' => 'Greek',
        'is' => 'Icelandic',
    ],

    /*
    | German names seen in existing free-text profiles, mapped to the codes above so they
    | show up as chips. Anything that matches neither list is kept as a custom chip.
    */
    'language_aliases' => [
        'deutsch' => 'de',
        'englisch' => 'en',
        'niederländisch' => 'nl',
        'hollaendisch' => 'nl',
        'holländisch' => 'nl',
        'französisch' => 'fr',
        'spanisch' => 'es',
        'italienisch' => 'it',
        'portugiesisch' => 'pt',
        'dänisch' => 'da',
        'schwedisch' => 'sv',
        'norwegisch' => 'no',
        'finnisch' => 'fi',
        'polnisch' => 'pl',
        'tschechisch' => 'cs',
        'ungarisch' => 'hu',
        'kroatisch' => 'hr',
        'slowenisch' => 'sl',
        'russisch' => 'ru',
        'türkisch' => 'tr',
        'griechisch' => 'el',
        'isländisch' => 'is',
    ],

    /*
    | Tag suggestions. Picked values are stored as these keys (labels from
    | guide_area.tags.{kind}.{key}); typed values are stored as written.
    */
    'tags' => [
        'specialty' => [
            'spin_fishing', 'fly_fishing', 'trolling', 'vertical_jigging', 'drop_shot',
            'jerkbait', 'bottom_fishing', 'float_fishing', 'carp_fishing', 'sea_fishing',
            'ice_fishing', 'big_game', 'surf_casting', 'night_fishing', 'catch_and_release',
        ],
        'good_for' => [
            'beginner_friendly', 'kids_welcome', 'families', 'experienced_anglers',
            'groups', 'solo_travellers', 'women_anglers', 'seniors', 'wheelchair_accessible',
            'dogs_welcome',
        ],
    ],

    'certificates' => [
        // Shown as rows even before anything is uploaded ("Not added").
        'recommended' => ['boat_licence', 'first_aid', 'liability_insurance'],
        'types' => [
            'boat_licence', 'first_aid', 'liability_insurance', 'fishing_licence',
            'guide_licence', 'business_registration', 'other',
        ],
        'max_kb' => 10240,
        'mimes' => ['pdf', 'jpg', 'jpeg', 'png', 'webp'],
        'folder' => 'guide-certificates',
    ],

    'photo' => [
        'max_kb' => 8192,
        'folder' => 'guide-profiles',
        'quality' => 82,
    ],

    /*
    | Account & security (spec chapter 13). Legal forms per country (13.2) for the company
    | select; the stored value is the form's name. "Other" lets the guide type their own.
    */
    'account' => [
        'min_age' => 18,
        // After a type switch the other type's data is kept this long, then cleared (13.6).
        'retain_other_type_days' => 30,
        'legal_forms' => [
            'DE' => ['GmbH', 'UG (haftungsbeschränkt)', 'GbR', 'OHG', 'KG', 'GmbH & Co. KG', 'e.K.', 'AG', 'Einzelunternehmen'],
            'AT' => ['GmbH', 'OG', 'KG', 'e.U.', 'Einzelunternehmen'],
            'CH' => ['GmbH', 'AG', 'Einzelunternehmen', 'Kollektivgesellschaft'],
            'NL' => ['B.V.', 'V.O.F.', 'Eenmanszaak'],
            'BE' => ['BV', 'SRL', 'VOF'],
            'DK' => ['ApS', 'A/S', 'I/S', 'Enkeltmandsvirksomhed'],
            'SE' => ['AB', 'HB', 'Enskild firma'],
            'NO' => ['AS', 'ENK', 'ANS'],
            'FI' => ['Oy', 'Toiminimi'],
            'ES' => ['S.L.', 'S.A.', 'Autónomo'],
            'IT' => ['S.r.l.', 'S.p.A.', 'Ditta individuale'],
            'PT' => ['Lda.', 'Empresário em nome individual'],
            'HR' => ['d.o.o.', 'obrt'],
            'FR' => ['SARL', 'SAS', 'EURL', 'EI'],
            'PL' => ['sp. z o.o.', 'JDG'],
            'IE' => ['Ltd', 'LLP', 'Sole trader'],
            'GB' => ['Ltd', 'LLP', 'Sole trader'],
        ],
    ],
];
