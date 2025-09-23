@extends('layouts.app')

@section('title', 'Rental Boat Form Example')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>Rental Boat Form Example</h4>
                </div>
                <div class="card-body">
                    <p>This is an example of how to use the rental boat multi-step form component.</p>
                    
                    <h5>Basic Usage:</h5>
                    <pre><code>&lt;x-rental-boat-form 
    :form-data="[]" 
    form-action="{{ route('rental-boats.store') }}"
    target-redirect="{{ route('rental-boats.index') }}" /&gt;</code></pre>

                    <h5>With Edit Data:</h5>
                    <pre><code>&lt;x-rental-boat-form 
    :form-data="[
        'id' => 1,
        'is_update' => 1,
        'title' => 'Beautiful Yacht for Rent',
        'boat_type' => 'yacht',
        'location' => 'Monaco',
        'status' => 'active',
        'gallery_images' => ['image1.jpg', 'image2.jpg'],
        'boat_information' => [
            'length' => '50ft',
            'capacity' => 8,
            'year' => 2020
        ]
    ]" 
    form-action="{{ route('rental-boats.update', 1) }}"
    target-redirect="{{ route('rental-boats.index') }}" /&gt;</code></pre>

                    <h5>Available Parameters:</h5>
                    <ul>
                        <li><strong>form-data</strong> (array): Existing form data for edit mode</li>
                        <li><strong>form-action</strong> (string): Form submission URL</li>
                        <li><strong>target-redirect</strong> (string): Redirect URL after successful submission</li>
                    </li>

                    <h5>Form Data Structure:</h5>
                    <pre><code>[
    'id' => 1,                    // Rental boat ID (for updates)
    'is_update' => 1,             // 1 for edit mode, 0 for create
    'user_id' => 1,               // Owner user ID
    'title' => 'Boat Title',      // Boat title
    'slug' => 'boat-slug',        // URL slug
    'thumbnail_path' => 'path',   // Main image path
    'gallery_images' => [],       // Array of image paths
    'location' => 'Location',     // General location
    'city' => 'City',            // City
    'country' => 'Country',      // Country
    'region' => 'Region',        // Region/State
    'lat' => 40.7128,           // Latitude
    'lng' => -74.0060,          // Longitude
    'boat_type' => 'yacht',     // Boat type
    'desc_of_boat' => 'Description', // Boat description
    'requirements' => 'Requirements', // Rental requirements
    'boat_information' => [      // Technical information
        'length' => '50ft',
        'capacity' => 8,
        'engine' => 'Twin Diesel',
        'year' => 2020,
        'fuel_type' => 'diesel',
        'safety_equipment' => 'Life jackets, First aid kit'
    ],
    'boat_extras' => ['GPS', 'Sound system'], // Array of extras
    'price_type' => 'per_day',   // Pricing type
    'prices' => [                // Pricing structure
        'base_price' => 500.00
    ],
    'pricing_extra' => [],       // Extra pricing items
    'inclusions' => ['Fuel', 'Captain'], // What's included
    'status' => 'active'         // Availability status
]</code></pre>

                    <h5>Controller Example:</h5>
                    <pre><code>public function create()
{
    return view('rental-boats.create', [
        'formData' => [
            'is_update' => 0,
            'user_id' => auth()->id(),
            'status' => 'active'
        ]
    ]);
}

public function edit(RentalBoat $rentalBoat)
{
    return view('rental-boats.edit', [
        'formData' => array_merge($rentalBoat->toArray(), [
            'is_update' => 1
        ])
    ]);
}</code></pre>

                    <h5>Routes Example:</h5>
                    <pre><code>Route::get('/rental-boats/create', [RentalBoatController::class, 'create'])->name('rental-boats.create');
Route::get('/rental-boats/{rentalBoat}/edit', [RentalBoatController::class, 'edit'])->name('rental-boats.edit');
Route::post('/rental-boats', [RentalBoatController::class, 'store'])->name('rental-boats.store');
Route::put('/rental-boats/{rentalBoat}', [RentalBoatController::class, 'update'])->name('rental-boats.update');</code></pre>

                    <div class="mt-4">
                        <h5>Live Example:</h5>
                        <x-rental-boat-form 
                            :form-data="[]" 
                            form-action="{{ route('rental-boats.store') }}"
                            target-redirect="{{ route('rental-boats.index') }}" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
