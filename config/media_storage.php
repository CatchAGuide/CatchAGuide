<?php

return [

    'disk' => env('MEDIA_STORAGE_DISK', 'do_spaces'),

    'local_disk' => env('MEDIA_STORAGE_LOCAL_DISK', 'public'),

    /*
    | URL generation: when true, managed media paths resolve straight to the DO CDN
    | URL with no remote HEAD/exists call (best for PageSpeed on listing pages).
    | Backend read/move/delete still use cached exists checks via exists_cache_ttl.
    */

    'url_skip_exists' => env('MEDIA_URL_SKIP_EXISTS', true),

    'exists_cache_ttl' => (int) env('MEDIA_EXISTS_CACHE_TTL', 86400),

    /*
    | Default ACL for new uploads on object storage (public-read = browser accessible).
    */

    'object_visibility' => env('MEDIA_OBJECT_VISIBILITY', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Listing folder names inside the bucket (after staging/ or production/)
    |--------------------------------------------------------------------------
    |
    | Pattern: {folder}/{entity-id}/{filename}.webp
    | Temp uploads (before id exists): {folder}/temp/{filename}.webp
    |
    */

    'listing_folders' => [
        // 'guiding' => 'assets/images/guidings',
        'guiding' => 'guidings',
        'vacation' => 'vacations',
        'accommodation' => 'accommodations',
        'camp' => 'camps',
        'rental_boat' => 'rental-boats',
        'special_offer' => 'special-offers',
        'trip' => 'trips',
    ],

    /*
    | Per-listing upload behaviour for ListingGalleryImageProcessor.
    */

    'listing_upload' => [
        'accommodation' => [
            'temp_slug' => 'temp-accommodation',
            'thumbnail' => 'primary_index',
        ],
        'camp' => [
            'temp_slug' => 'temp-camp',
            'thumbnail' => 'requested_path',
            'cropped' => true,
            'empty_returns_null' => true,
        ],
        'rental_boat' => [
            'temp_slug' => 'temp-rental-boat',
            'thumbnail' => 'primary_index',
        ],
        'special_offer' => [
            'temp_slug' => 'temp-special-offer',
            'thumbnail' => 'requested_path',
            'cropped' => true,
            'empty_returns_null' => true,
        ],
        'trip' => [
            'temp_slug' => 'temp-trip',
            'thumbnail' => 'primary_index',
            'cropped' => true,
            'empty_returns_null' => true,
        ],
        'vacation' => [
            'temp_slug' => 'temp-vacation',
            'thumbnail' => 'primary_index',
        ],
    ],

    /*
    | Legacy flat folders still resolved for reads / relocation of old files.
    */

    'legacy_listing_folders' => [
        'guiding' => ['guidings-images', 'guidings'],
        'vacation' => ['vacations-images'],
        'camp' => ['camps-images'],
        'rental_boat' => ['rental-boats-images'],
        'special_offer' => ['special-offers-images'],
        'trip' => ['trips-images'],
    ],

    /*
    | Root directory segment → listing key (includes legacy roots).
    */

    'directories' => [
        'guidings' => 'guiding',
        'vacations' => 'vacation',
        'accommodations' => 'accommodation',
        'camps' => 'camp',
        'rental-boats' => 'rental_boat',
        'special-offers' => 'special_offer',
        'trips' => 'trip',
        'guidings-images' => 'guiding',
        'assets/images/guidings' => 'guiding',
        'vacations-images' => 'vacation',
        'camps-images' => 'camp',
        'rental-boats-images' => 'rental_boat',
        'special-offers-images' => 'special_offer',
        'trips-images' => 'trip',
    ],

    'sitewide_folders' => [
        'listing_media' => [
            'guidings' => [
                'listing' => 'guiding',
                'migrate' => true,
                'notes' => 'Transitional guidings/{id}/ paths (prefer assets/images/guidings/{id}/)',
            ],
            'vacations' => [
                'listing' => 'vacation',
                'migrate' => true,
            ],
            'accommodations' => [
                'listing' => 'accommodation',
                'migrate' => true,
            ],
            'camps' => [
                'listing' => 'camp',
                'migrate' => true,
            ],
            'rental-boats' => [
                'listing' => 'rental_boat',
                'migrate' => true,
            ],
            'special-offers' => [
                'listing' => 'special_offer',
                'migrate' => true,
            ],
            'trips' => [
                'listing' => 'trip',
                'migrate' => true,
            ],
            'guidings-images' => [
                'listing' => 'guiding',
                'migrate' => true,
                'notes' => 'Legacy flat guidings-images/* paths',
            ],
            'assets/images/guidings' => [
                'listing' => 'guiding',
                'migrate' => true,
                'notes' => 'assets/images/guidings/{id}/filename.webp',
            ],
            'vacations-images' => [
                'listing' => 'vacation',
                'migrate' => true,
                'notes' => 'Legacy flat paths',
            ],
        ],

        'legacy_guiding' => [
            'assets/guides' => [
                'listing' => 'guiding',
                'migrate' => false,
            ],
        ],

        'model_image_trait' => [
            'assets/guidings' => ['migrate' => false],
        ],

        'blog' => [
            'blog' => [
                'migrate' => true,
                'notes' => 'Blog images (includes blog/contents, blog/city, etc.)',
            ],
            'newblog' => [
                'migrate' => true,
                'notes' => 'Guide blog (newblog) post images',
            ],
            'blog/contents' => [
                'migrate' => false,
                'notes' => 'Covered by parent blog/ sync',
            ],
        ],

        'category_seo' => [
            'category' => ['migrate' => false],
        ],

        'profiles' => [
            'images' => [
                'migrate' => true,
                'notes' => 'General site images under public/images',
            ],
            'uploads' => [
                'migrate' => true,
                'notes' => 'User uploads under public/uploads (includes profile_images)',
            ],
            'uploads/profile_images' => ['migrate' => false],
        ],

        'local_only' => [
            'cache/guidings' => ['migrate' => false],
            'files' => ['migrate' => false],
            'assets/prompts' => ['migrate' => false],
        ],
    ],

];
