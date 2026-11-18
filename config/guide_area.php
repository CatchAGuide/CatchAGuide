<?php

/*
|--------------------------------------------------------------------------
| Guide Area (redesigned guide dashboard under /guide/*)
|--------------------------------------------------------------------------
|
| Ships behind a per-guide beta flag (users.guide_area_beta). `enabled` is the global kill
| switch: false hides /guide/* for everyone, flagged or not. The old /profile/* guide pages
| stay live until rollout.
|
*/

return [

    'enabled' => (bool) env('GUIDE_AREA_ENABLED', true),

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
];
