@extends('layouts.app')
@section('title', 'Camp Offer')
@php
    $camp = [
        'title' => 'Ebro Fishing Camp – Riba Roja (Example)',
        'city' => 'Riba Roja d\'Ebre',
        'region' => 'Catalonia',
        'country' => 'Spain',
        'lat' => 41.2506,
        'lng' => 0.4921,
        'description' => [
            'camp_area_fishing' => 'Directly at the reservoir. Shallow water bays + old river courses – Top for catfish, pike perch & black bass. Short distances to pier & slipway.',
        ],
        'distances' => [
            'to_shop_km' => 5,
            'to_town_km' => 7,
            'to_airport_km' => 95,
            'to_ferry_km' => 180,
        ],
        'amenities' => [
            'swimming_pool' => false,
            'private_jetty' => true,
            'fish_cleaning_station' => true,
            'smoker_device' => true,
            'bbq_area' => true,
            'lockable_fishing_storage' => true,
            'fireplace' => false,
            'sauna' => false,
            'hot_tub' => false,
            'games_corner' => true,
            'parking_spaces' => true,
            'ev_charger' => true,
            'boat_ramp_nearby' => true,
            'reception' => true,
            'fishfilet_freezer' => true,
        ],
        'conditions' => [
            'minimum_stay_nights' => 3,
            'booking_window' => 'Bookable until 2 days before arrival, high season min. 7 nights (Sat-Sat)',
        ],
        'policies_regulations' => [
            'Fishing licenses required (available at camp)',
            'Catch & Release for black bass, observe local rules',
            'Life jacket mandatory on the boat',
        ],
        'best_travel_times' => [
            ['month' => 'Mar', 'note' => 'Pike perch active'],
            ['month' => 'Apr', 'note' => 'Catfish season start'],
            ['month' => 'Oct', 'note' => 'Top for pike perch/jerkbait'],
        ],
        'target_fish' => ['Catfish', 'Pike perch', 'Black bass', 'Carp'],
        'travel_info' => [
            'Arrival via Barcelona (BCN) or Reus (REU)',
            'Rental car recommended; road conditions good',
        ],
        'extras' => ['Bed linen', 'Towels', 'Final cleaning', 'Equipment rental'],
        'manual_gallery_images' => [
            'https://images.unsplash.com/photo-1512914890250-33530e2f0d72?q=80&w=1600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1469474968028-56623f02e42e?q=80&w=1600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=1600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?q=80&w=1600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?q=80&w=1600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=1600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1448375240586-882707db888b?q=80&w=1600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=1600&auto=format&fit=crop'
        ],
        'gallery_images' => [],
        'thumbnail_path' => 'https://images.unsplash.com/photo-1512914890250-33530e2f0d72?q=80&w=1600&auto=format&fit=crop',
    ];
    $accommodations = [
        [
            'id' => 1,
            'title' => 'Apartment 3 – Lake View',
            'max_occupancy' => 4,
            'price' => [
                'type' => 'per night',
                'amount' => 110,
                'currency' => 'EUR',
                'per_week' => 690,
            ],
        ],
        [
            'id' => 2,
            'title' => 'Casa Rio – Family House',
            'max_occupancy' => 6,
            'price' => [
                'type' => 'per night',
                'amount' => 140,
                'currency' => 'EUR',
                'per_week' => 880,
            ],
        ],
    ];
    $boats = [
        [
            'id' => 11,
            'title' => 'Aluminum 4.5m  |  15 HP',
            'seats' => 3,
            'sonar_gps' => true,
            'price_per_day' => 65,
            'currency' => 'EUR',
            'img' => 'https://images.unsplash.com/photo-1520440229-84f3865cf003?q=80&w=1600&auto=format&fit=crop',
        ],
        [
            'id' => 12,
            'title' => 'Aluminum 5.0m  |  30 HP',
            'seats' => 4,
            'sonar_gps' => true,
            'price_per_day' => 95,
            'currency' => 'EUR',
            'img' => 'https://images.unsplash.com/photo-1505839673365-e3971f8d9184?q=80&w=1600&auto=format&fit=crop',
        ],
    ];
    $guidings = [
        [
            'id' => 21,
            'title' => 'Half-day Guiding (4h)',
            'group_size' => 2,
            'price' => 180,
            'currency' => 'EUR',
            'img' => 'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop',
        ],
        [
            'id' => 22,
            'title' => 'Full-day Tour (8h)',
            'group_size' => 3,
            'price' => 320,
            'currency' => 'EUR',
            'img' => 'https://images.unsplash.com/photo-1469536526925-9b5547cd51f6?q=80&w=1600&auto=format&fit=crop',
        ],
    ];
    $showCategories = true;
    $campHero = !empty($camp['thumbnail_path'])
        ? $camp['thumbnail_path']
        : ($camp['manual_gallery_images'][0] ?? 'https://images.unsplash.com/photo-1512914890250-33530e2f0d72?q=80&w=1600&auto=format&fit=crop');
    $galleryImages = array_values(array_filter(array_merge(
        [$camp['thumbnail_path'] ?? null],
        $camp['manual_gallery_images'] ?? [],
        $camp['gallery_images'] ?? []
    )));
    if (empty($galleryImages)) {
        $galleryImages = [$campHero];
    }
    $galleryImages = array_values(array_filter($galleryImages));
    $primaryImage = $galleryImages[0];
    $topRightImages = array_slice($galleryImages, 1, 2);
    while (count($topRightImages) < 2) {
        $topRightImages[] = $primaryImage;
        if (count($topRightImages) >= count($galleryImages)) {
            break;
        }
    }
    $bottomStripImages = array_slice($galleryImages, 3, 5);
    $fallbackIndex = 0;
    while (count($bottomStripImages) < 5) {
        $bottomStripImages[] = $galleryImages[$fallbackIndex % count($galleryImages)];
        $fallbackIndex++;
        if ($fallbackIndex > 20) {
            break;
        }
    }
    $bottomStripImages = array_slice($bottomStripImages, 0, 5);
    $remainingGalleryCount = max(0, count($galleryImages) - 8);

@endphp
@section('content')
<div
    x-data="campConfigurator({
        camp: @json($camp),
        accommodations: @json($accommodations),
        boats: @json($boats),
        guidings: @json($guidings),
        showCategories: {{ $showCategories ? 'true' : 'false' }}
    })"
    x-init="init()"
    class="camp-page min-h-screen bg-gradient-to-b from-slate-50 to-white"
>
    <style>
        :root{--brand:#313041;--accent:#e8604c;--camp-brand:#313041;--camp-accent:#e8604c;--camp-border:rgba(15,23,42,.08);--camp-radius:18px;--camp-shadow:0 18px 48px -34px rgba(15,23,42,.3);}
        .brand-btn{background:var(--brand);color:#fff;border-radius:0.75rem;padding:0.5rem 0.75rem}
        .brand-btn:hover{filter:brightness(0.95)}
        .accent-badge{position:relative;background:#fff;border:1px solid rgba(49,48,65,.2);color:#444;border-radius:0.75rem;padding:0.5rem 0.6rem;font-size:0.8rem}
        .accent-badge:before{content:"";position:absolute;left:0;top:0;bottom:0;width:6px;background:rgba(232,96,76,.35);border-top-left-radius:0.75rem;border-bottom-left-radius:0.75rem}
        .section-card{border-top:2px solid rgba(232,96,76,.2)}
        .nav-pill{padding:0.5rem 1rem;border-radius:9999px;font-weight:600;transition:all .2s ease;border:1px solid rgba(49,48,65,.25)}
        .nav-pill:hover{background:rgba(49,48,65,.06)}
        .nav-pill-active{background:var(--brand);color:#fff}
        [x-cloak]{display:none !important;}
        /* camp layout overrides */
        .camp-page{min-height:100vh;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);padding-bottom:80px;}
        .camp-header{box-shadow:0 18px 32px -28px rgba(17,24,39,.6);}
        .camp-container{max-width:1100px;margin:0 auto;padding-left:1.5rem;padding-right:1.5rem;}
                .camp-gallery{display:grid;grid-template-columns:minmax(0,3fr) minmax(0,2fr);gap:16px;margin:24px auto;}
        @media(max-width:900px){.camp-gallery{grid-template-columns:1fr;}}
        .camp-gallery__main{position:relative;border-radius:20px;overflow:hidden;background:#e2e8f0;min-height:240px;box-shadow:0 26px 52px -40px rgba(17,24,39,.35);}
        @media(min-width:768px){.camp-gallery__main{min-height:340px;}}
        .camp-gallery__main img{width:100%;height:100%;object-fit:cover;}
        .camp-gallery__right{display:flex;flex-direction:column;gap:16px;}
        .camp-gallery__right .camp-gallery__thumb{flex:1;min-height:150px;}
        .camp-gallery__thumb{position:relative;border-radius:18px;overflow:hidden;background:#e2e8f0;min-height:120px;}
        .camp-gallery__thumb img{width:100%;height:100%;object-fit:cover;}
        .camp-gallery__more{position:absolute;inset:0;background:rgba(17,24,39,.55);color:#fff;font-weight:600;font-size:1rem;display:flex;align-items:center;justify-content:center;}
        .camp-gallery__bottom{grid-column:1/-1;display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;}
        @media(max-width:900px){.camp-gallery__bottom{grid-template-columns:repeat(3,minmax(0,1fr));}}
        @media(max-width:600px){.camp-gallery__bottom{grid-template-columns:repeat(2,minmax(0,1fr));}}
        .camp-layout{display:grid;gap:24px;margin-bottom:32px;}
        .camp-layout__content{display:flex;flex-direction:column;gap:16px;flex:1;}
        @media(min-width:1024px){.camp-layout{grid-template-columns:minmax(0,3fr) 320px;align-items:flex-start;}.camp-config{position:sticky;top:110px;}}
.camp-topbar{background:#fff;border-bottom:1px solid rgba(15,23,42,.08);padding:32px 0 20px;position:relative;z-index:20;}
        .camp-topbar__inner{max-width:1100px;display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:24px;}
        .camp-topbar__info{flex:1 1 60%;min-width:260px;}
        .camp-topbar__title{margin:0;font-size:2.1rem;font-weight:700;color:#222133;line-height:1.2;}
        .camp-topbar__meta{margin-top:6px;font-size:0.95rem;color:#5b6171;display:flex;align-items:center;flex-wrap:wrap;gap:10px;}
        .camp-topbar__dot{color:#d1d5db;}
        .camp-topbar__link{color:var(--camp-accent);font-weight:600;text-decoration:none;}
        .camp-topbar__link:hover{text-decoration:underline;}
        .camp-topbar__actions{display:flex;flex-direction:column;align-items:flex-end;gap:6px;min-width:180px;}
        .camp-topbar__cta{border-radius:999px;padding:10px 22px;font-weight:600;box-shadow:0 10px 24px -18px rgba(17,24,39,.6);}
        .camp-topbar__note{font-size:0.8rem;color:#6b7280;}
        @media(max-width:768px){.camp-topbar__inner{flex-direction:column;align-items:flex-start;}.camp-topbar__actions{align-items:flex-start;}}
        .camp-config-card{border-radius:20px;box-shadow:0 24px 46px -34px rgba(17,24,39,.45);padding:18px 20px;}
        .camp-config-card .brand-btn{border-radius:16px;padding:12px 18px;}
        .camp-config-card .accent-badge{margin-top:10px;font-size:.82rem;background:#fff7f5;border-color:rgba(232,96,76,.35);}
        .camp-config-card .accent-badge strong{font-weight:600;}
        .camp-form{margin-top:14px;}
        .camp-form-grid{display:grid;grid-template-columns:1fr;gap:12px;}
        @media(min-width:768px){.camp-form-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
        .camp-form-field{display:flex;flex-direction:column;gap:6px;}
        .camp-form-field.full-span{grid-column:1/-1;}
        .camp-form-field label{font-size:.85rem;font-weight:600;color:#344054;}
        .camp-control{width:100%;border-radius:12px;border:1px solid rgba(17,24,39,.18);padding:8px 12px;font-size:.92rem;background:#fff;transition:border-color .2s ease,box-shadow .2s ease;}
        .camp-control:focus{outline:none;border-color:var(--camp-accent);box-shadow:0 0 0 0.14rem rgba(232,96,76,.22);}
        .camp-form-field__note{font-size:.72rem;color:#6b7280;}
        .camp-form__status{margin-top:6px;font-size:.8rem;color:#475569;}
        .camp-summary{margin-top:16px;border-radius:16px;background:rgba(241,245,249,.85);border:1px solid rgba(49,48,65,.12);padding:14px;}
        .camp-summary__row{display:flex;justify-content:space-between;align-items:center;padding:4px 0;font-size:.9rem;}
        .camp-summary__total{border-top:1px solid rgba(15,23,42,.12);margin-top:10px;padding-top:10px;font-weight:600;}
        .camp-summary__note{margin-top:8px;font-size:.72rem;color:#64748b;}
        .camp-sections{display:grid;gap:18px;}
        .camp-section{background:#fff;border:1px solid var(--camp-border);border-radius:var(--camp-radius);padding:26px;box-shadow:var(--camp-shadow);}
        .camp-section__title{margin-bottom:16px;font-size:1.05rem;font-weight:600;color:#1f2937;}
        .camp-section__body{font-size:0.95rem;color:#333d4d;line-height:1.55;}
        .camp-pill-row{display:flex;flex-wrap:wrap;gap:10px;}
        .camp-pill{display:inline-flex;align-items:center;justify-content:center;padding:8px 16px;font-size:0.85rem;font-weight:500;border-radius:999px;background:#f4f6fb;border:1px solid rgba(49,48,65,.12);color:#1f2a40;}
        .camp-section__list{margin:0;padding-left:1rem;color:#333d4d;}
        .camp-section__subtitle{margin-bottom:8px;font-size:0.9rem;font-weight:600;color:#1f2a37;}
        .camp-section__cols{display:grid;gap:12px;}
        @media(min-width:768px){.camp-section__cols{grid-template-columns:repeat(2,minmax(0,1fr));}}
        .camp-nav{margin-top:18px;margin-bottom:12px;}
        .camp-nav .nav-pill{display:inline-flex;align-items:center;justify-content:center;}
        .camp-info-grid{display:grid;grid-template-columns:1fr;gap:1.5rem;margin-top:1.5rem;margin-bottom:6rem;}
        @media(min-width:992px){.camp-info-grid{grid-template-columns:minmax(0,1fr);}}
        .section-card{border:1px solid var(--camp-border);border-radius:var(--camp-radius);padding:26px;background:#fff;box-shadow:var(--camp-shadow);}
        .camp-extras{margin-top:18px;margin-bottom:6rem;}
        .camp-offer-card{border-radius:20px;border:1px solid rgba(49,48,65,.15);box-shadow:0 20px 46px -36px rgba(17,24,39,.55);transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease;}
        .camp-offer-card:hover{transform:translateY(-4px);box-shadow:0 32px 60px -40px rgba(17,24,39,.7);}
        .camp-offer-card img{border-radius:14px;}
        .camp-offer-card button{border-radius:12px;border:1px solid rgba(49,48,65,.18);padding:8px 12px;background:#fff;}
        .camp-offer-card.is-active{border-color:rgba(232,96,76,.55);box-shadow:0 0 0 2px rgba(232,96,76,.3);}
        .camp-mobile-bar .camp-container{display:flex;align-items:center;justify-content:space-between;gap:16px;padding-top:12px;padding-bottom:12px;}
        .camp-mobile-bar .camp-container .text-sm{margin:0;}
        @media(min-width:992px){.camp-mobile-bar{display:none;}}
        
        /* Accommodation Card Specific Styles */
        .accommodation-card {
            background: #fff;
            border: 1px solid rgba(15,23,42,.08);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 18px 48px -34px rgba(15,23,42,.3);
        }
        
        .accommodation-card .grid {
            display: grid;
            gap: 12px;
            padding: 12px;
        }
        
        /* Override the grid to match target design */
        .accommodation-card .grid {
            grid-template-columns: 1fr 2fr;
            gap: 16px;
            padding: 16px;
        }
        
        .left-column {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .right-column {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        @media (max-width: 768px) {
            .accommodation-card .grid {
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 12px;
            }
            
            .left-column, .right-column {
                gap: 8px;
            }
        }
        
        
        .accommodation-gallery {
            position: relative;
            width: 100%;
            aspect-ratio: 1;
            overflow: hidden;
            border-radius: 12px;
            border: 1px solid rgba(15,23,42,.08);
            background: #f1f5f9;
        }
        
        .accommodation-gallery img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .gallery-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,.9);
            border: 1px solid rgba(15,23,42,.1);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #334155;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
        }
        
        .gallery-nav-btn:hover {
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
        }
        
        .gallery-nav-btn.prev {
            left: 4px;
        }
        
        .gallery-nav-btn.next {
            right: 4px;
        }
        
        .gallery-counter {
            position: absolute;
            bottom: 4px;
            right: 4px;
            background: rgba(255,255,255,.9);
            border-radius: 12px;
            padding: 2px 6px;
            font-size: 10px;
            color: #64748b;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
        }
        
        .info-chips {
            position: absolute;
            bottom: 8px;
            left: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        
        .info-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(255,255,255,.8);
            border: 1px solid rgba(15,23,42,.1);
            border-radius: 12px;
            padding: 4px 8px;
            font-size: 12px;
            color: #334155;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
        }
        
        .detail-box {
            background: rgba(241,245,249,.6);
            border: 1px solid rgba(15,23,42,.08);
            border-radius: 12px;
            padding: 8px;
            margin-top: 12px;
        }
        
        .detail-box-title {
            color: #64748b;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 4px;
        }
        
        .detail-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .detail-list li {
            font-size: 12px;
            color: #334155;
            margin-bottom: 2px;
        }
        
        .detail-list li:last-child {
            margin-bottom: 0;
        }
        
        .inclusive-extras {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 4px;
        }
        
        .inclusive-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(255,255,255,.8);
            border: 1px solid rgba(15,23,42,.1);
            border-radius: 12px;
            padding: 4px 8px;
            font-size: 11px;
            color: #334155;
        }
        
        .accommodation-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }
        
        .accommodation-info {
            flex: 1;
            min-width: 0;
        }
        
        .accommodation-location {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
        }
        
        .accommodation-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 4px 0;
            line-height: 1.3;
        }
        
        .accommodation-type {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
        }
        
        .accommodation-description {
            font-size: 13px;
            color: #475569;
            line-height: 1.4;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .accommodation-pricing {
            text-align: right;
            flex-shrink: 0;
            min-width: 140px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }
        
        .price-type {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 2px;
        }
        
        .price-amount {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .select-btn {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 500;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .select-btn:hover {
            background: #1d4ed8;
        }
        
        .select-accommodation-btn {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
            white-space: nowrap;
        }
        
        .select-accommodation-btn:hover {
            background: #1d4ed8;
        }
        
        .distance-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin: 8px 0;
        }
        
        .distance-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(255,255,255,.8);
            border: 1px solid rgba(15,23,42,.1);
            border-radius: 12px;
            padding: 4px 8px;
            font-size: 12px;
            color: #334155;
        }
        
        .info-matrix {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin: 12px 0;
        }
        
        .info-matrix .info-box:last-child {
            grid-column: 1 / -1;
        }
        
        @media (max-width: 1024px) {
            .info-matrix {
                grid-template-columns: 1fr;
            }
        }
        
        .info-box {
            background: rgba(241,245,249,.6);
            border: 1px solid rgba(15,23,42,.08);
            border-radius: 12px;
            padding: 8px;
        }
        
        .info-box-title {
            color: #64748b;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 4px;
        }
        
        .info-box-content {
            font-size: 12px;
            color: #334155;
            line-height: 1.4;
        }
        
        
        .amenities-section {
            margin-top: 12px;
        }
        
        .amenities-title {
            color: #64748b;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 6px;
        }
        
        .amenities-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        
        .amenity-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(255,255,255,.8);
            border: 1px solid rgba(15,23,42,.1);
            border-radius: 12px;
            padding: 4px 8px;
            font-size: 11px;
            color: #334155;
        }
        
        @media (max-width: 768px) {
            .accommodation-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .accommodation-pricing {
                text-align: left;
                min-width: auto;
                align-items: flex-start;
            }
            
            .info-matrix {
                grid-template-columns: 1fr;
            }
        }
        
        /* Guiding Card Specific Styles */
        .guiding-card {
            background: #fff;
            border: 1px solid rgba(15,23,42,.08);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 18px 48px -34px rgba(15,23,42,.3);
        }
        
        .guiding-card-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 0;
        }
        
        .guiding-left-column {
            display: flex;
            flex-direction: column;
            background: #f8fafc;
        }
        
        .guiding-gallery {
            position: relative;
            width: 100%;
            aspect-ratio: 1;
            min-height: 280px;
            overflow: hidden;
            background: #e2e8f0;
        }
        
        .guiding-gallery img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .guiding-inclusives-box {
            background: #fff;
            border-top: 1px solid rgba(15,23,42,.08);
            padding: 12px 16px;
        }
        
        .guiding-inclusives-box .inclusives-title {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 6px 0;
        }
        
        .guiding-right-column {
            padding: 18px 20px 20px;
            display: flex;
            flex-direction: column;
        }
        
        .guiding-info-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            align-items: start;
            margin-bottom: 6px;
        }
        
        .guiding-info-grid:last-of-type {
            margin-bottom: 12px;
        }
        
        .guiding-location {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: #64748b;
        }
        
        .location-icon {
            flex-shrink: 0;
            color: #94a3b8;
        }
        
        .guiding-location-text {
            color: #64748b;
            word-wrap: break-word;
            word-break: break-word;
        }
        
        .guiding-price-label {
            font-size: 11px;
            color: #64748b;
            line-height: 1;
            text-align: right;
            align-self: center;
        }
        
        .guiding-title {
            font-size: 19px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            line-height: 1.3;
            align-self: center;
        }
        
        .guiding-price-amount {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1;
            text-align: right;
            align-self: center;
        }
        
        .guiding-description {
            font-size: 13px;
            color: #475569;
            line-height: 1.5;
            margin: 0;
            align-self: start;
        }
        
        .select-guiding-btn {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
            white-space: nowrap;
            align-self: start;
        }
        
        .select-guiding-btn:hover {
            background: #1d4ed8;
        }
        
        .guiding-info-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }
        
        .guiding-info-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #f1f5f9;
            border: 1px solid rgba(15,23,42,.1);
            border-radius: 999px;
            padding: 5px 11px;
            font-size: 12px;
            color: #475569;
        }
        
        .pill-icon {
            flex-shrink: 0;
            color: #94a3b8;
        }
        
        .guiding-info-panels {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 4px;
        }
        
        .guiding-panel {
            background: #fff;
            border: 1px solid rgba(15,23,42,.1);
            border-radius: 12px;
            padding: 12px 14px;
        }
        
        .guiding-panel-title {
            color: #1e293b;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .guiding-panel-content {
            font-size: 12px;
            color: #475569;
            line-height: 1.5;
        }
        
        .guiding-panel-content > div {
            margin-bottom: 4px;
        }
        
        .guiding-panel-content > div:last-child {
            margin-bottom: 0;
        }
        
        .target-fish-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f1f5f9;
            border: 1px solid rgba(15,23,42,.1);
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            color: #475569;
        }
        
        .target-fish-chip span {
            color: #475569;
        }
        
        .chip-icon {
            flex-shrink: 0;
            color: #94a3b8;
        }
        
        .start-times-chips .start-time-chip {
            background: #f1f5f9;
            border: 1px solid rgba(15,23,42,.1);
            color: #475569;
        }
        
        .check-icon {
            flex-shrink: 0;
            color: #10b981;
        }
        
        .inclusive-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .methods-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }
        
        .method-chip {
            display: inline-flex;
            align-items: center;
            background: #f1f5f9;
            border: 1px solid rgba(15,23,42,.1);
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            color: #475569;
        }
        
        .start-times-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }
        
        .start-time-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #f1f5f9;
            border: 1px solid rgba(15,23,42,.1);
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            color: #475569;
        }
        
        .start-time-chip .chip-icon {
            color: #94a3b8;
        }
        
        .inclusives-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        @media (max-width: 1200px) {
            .guiding-info-panels {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 992px) {
            .guiding-card-grid {
                grid-template-columns: 1fr;
            }
            
            .guiding-gallery {
                min-height: 300px;
            }
        }
        
        @media (max-width: 768px) {
            .guiding-info-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .guiding-price-label,
            .guiding-price-amount {
                text-align: left;
            }
        }
    </style>
    <header class="camp-topbar">
        <div class="camp-container camp-topbar__inner">
            <div class="camp-topbar__info">
                <h1 class="camp-topbar__title" x-text="camp.title">{{ $camp['title'] }}</h1>
                <div class="camp-topbar__meta">
                    <span>{{ $camp['city'] }}, {{ $camp['region'] }}, {{ $camp['country'] }}</span>
                    <span class="camp-topbar__dot">•</span>
                    <a class="camp-topbar__link" href="#karte">Show on map</a>
                </div>
            </div>
            <div class="camp-topbar__actions">
                <a href="#konfigurator" class="brand-btn camp-topbar__cta">Make Inquiry</a>
                <span class="camp-topbar__note">Best Price Guarantee</span>
            </div>
        </div>
    </header>
                <div class="camp-container camp-gallery">
        <div class="camp-gallery__main">
            <img src="{{ $primaryImage }}" alt="{{ $camp['title'] }}">
        </div>
        <div class="camp-gallery__right">
            @foreach ($topRightImages as $image)
                <div class="camp-gallery__thumb">
                    <img src="{{ $image }}" alt="{{ $camp['title'] }} Galeriebild">
                </div>
            @endforeach
        </div>
        <div class="camp-gallery__bottom">
            @foreach ($bottomStripImages as $index => $image)
                <div class="camp-gallery__thumb">
                    <img src="{{ $image }}" alt="{{ $camp['title'] }} Galeriebild">
                    @if($loop->last && $remainingGalleryCount > 0)
                        <div class="camp-gallery__more">+{{ $remainingGalleryCount }} weitere</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="camp-container camp-layout">
        <div class="camp-layout__content">
            <nav class="camp-nav flex flex-wrap gap-3 py-3 text-sm">
                <a href="#general-information" class="nav-pill">General Information</a>
            </nav>
            <main id="general-information" class="camp-info-grid">
                                                                                <div class="camp-info-col">
                                                                                    <div class="camp-sections">
                                                                                        <section id="description" class="camp-section scroll-mt-24">
                                                                                            <h2 class="camp-section__title">Description</h2>
                                                                                            <div class="camp-section__body space-y-3">
                                                                                                <div>
                                                                                                    <h3 class="font-semibold text-gray-700">Camp</h3>
                                                                                                    <p>Our camp is located directly at the reservoir and offers short distances to pier, slipway and service areas. The accommodations are modernly equipped and designed for the needs of fishing groups.</p>
                                                                                                </div>
                                                                                                <div>
                                                                                                    <h3 class="font-semibold text-gray-700">Area</h3>
                                                                                                    <p>In the immediate vicinity you'll find protected bays, wooded shores and open water areas. The environment remains pleasantly quiet even in thermal conditions and is ideal for day trips.</p>
                                                                                                </div>
                                                                                                <div>
                                                                                                    <h3 class="font-semibold text-gray-700">Fishing</h3>
                                                                                                    <p>The lake is considered a hotspot for catfish, pike perch and black bass. Drop-offs, weed edges and oxbow areas can be reached quickly and regularly deliver trophy fish.</p>
                                                                                                </div>
                                                                                            </div>
                                                                                        </section>
                                                                                        <section id="distances" class="camp-section scroll-mt-24">
                                                                                            <h2 class="camp-section__title">Distances</h2>
                                                                                            <div class="camp-pill-row">
                                                                                                <span class="camp-pill">Shop: {{ $camp['distances']['to_shop_km'] }} km</span>
                                                                                                <span class="camp-pill">Nearest town: {{ $camp['distances']['to_town_km'] }} km</span>
                                                                                                <span class="camp-pill">Airport: {{ $camp['distances']['to_airport_km'] }} km</span>
                                                                                                <span class="camp-pill">Ferry port: {{ $camp['distances']['to_ferry_km'] }} km</span>
                                                                                            </div>
                                                                                        </section>
                                                                                        <section id="amenities" class="camp-section scroll-mt-24">
                                                                                            <h2 class="camp-section__title">Amenities (Camp)</h2>
                                                                                            <div class="camp-section__cols">
                                                                                                <div>Swimming pool: <strong>{{ $camp['amenities']['swimming_pool'] ? 'Yes' : 'No' }}</strong></div>
                                                                                                <div>Private jetty: <strong>{{ $camp['amenities']['private_jetty'] ? 'Yes' : 'No' }}</strong></div>
                                                                                                <div>Fish cleaning station: <strong>{{ $camp['amenities']['fish_cleaning_station'] ? 'Yes' : 'No' }}</strong></div>
                                                                                                <div>Smoker device: <strong>{{ $camp['amenities']['smoker_device'] ? 'Yes' : 'No' }}</strong></div>
                                                                                                <div>BBQ area: <strong>{{ $camp['amenities']['bbq_area'] ? 'Yes' : 'No' }}</strong></div>
                                                                                                <div>Lockable fishing storage: <strong>{{ $camp['amenities']['lockable_fishing_storage'] ? 'Yes' : 'No' }}</strong></div>
                                                                                                <div>Fireplace: <strong>{{ $camp['amenities']['fireplace'] ? 'Yes' : 'No' }}</strong></div>
                                                                                                <div>Sauna: <strong>{{ $camp['amenities']['sauna'] ? 'Yes' : 'No' }}</strong></div>
                                                                                                <div>Hot Tub/Pool: <strong>{{ $camp['amenities']['hot_tub'] ? 'Yes' : 'No' }}</strong></div>
                                                                                                <div>Games corner/Darts: <strong>{{ $camp['amenities']['games_corner'] ? 'Yes' : 'No' }}</strong></div>
                                                                                                <div>Parking spaces: <strong>{{ $camp['amenities']['parking_spaces'] ? 'Yes' : 'No' }}</strong></div>
                                                                                                <div>EV charging station: <strong>{{ $camp['amenities']['ev_charger'] ? 'Yes' : 'No' }}</strong></div>
                                                                                                <div>Boat ramp nearby: <strong>{{ $camp['amenities']['boat_ramp_nearby'] ? 'Yes' : 'No' }}</strong></div>
                                                                                                <div>Reception: <strong>{{ $camp['amenities']['reception'] ? 'Yes' : 'No' }}</strong></div>
                                                                                                <div>Fish fillet freezer: <strong>{{ $camp['amenities']['fishfilet_freezer'] ? 'Yes' : 'No' }}</strong></div>
                                                                                            </div>
                                                                                        </section>
                                                                                        <section id="conditions" class="camp-section scroll-mt-24">
                                                                                            <h2 class="camp-section__title">Camp Conditions</h2>
                                                                                            <div class="camp-section__cols">
                                                                                                <div>Minimum stay: <strong>{{ $camp['conditions']['minimum_stay_nights'] }} nights</strong></div>
                                                                                                <div>Booking window/Policies: <strong>{{ $camp['conditions']['booking_window'] }}</strong></div>
                                                                                            </div>
                                                                                        </section>
                                                                                        <section id="policies" class="camp-section scroll-mt-24">
                                                                                            <h2 class="camp-section__title">Policies & Regulations</h2>
                                                                                            <ul class="camp-section__list">
                                                                                                @foreach ($camp['policies_regulations'] as $policy)
                                                                                                    <li>{{ $policy }}</li>
                                                                                                @endforeach
                                                                                            </ul>
                                                                                        </section>
                                                                                        <section id="best-times" class="camp-section scroll-mt-24">
                                                                                            <h2 class="camp-section__title">Best Travel Times & Target Fish</h2>
                                                                                            <div class="camp-section__cols">
                                                                                                <div>
                                                                                                    <h3 class="camp-section__subtitle">Best Travel Times</h3>
                                                                                                    <ul class="camp-section__list">
                                                                                                        @foreach ($camp['best_travel_times'] as $season)
                                                                                                            <li><strong>{{ $season['month'] }}</strong>: {{ $season['note'] }}</li>
                                                                                                        @endforeach
                                                                                                    </ul>
                                                                                                </div>
                                                                                                <div>
                                                                                                    <h3 class="camp-section__subtitle">Target Fish</h3>
                                                                                                    <div class="camp-pill-row">
                                                                                                        @foreach ($camp['target_fish'] as $fish)
                                                                                                            <span class="camp-pill">{{ $fish }}</span>
                                                                                                        @endforeach
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </section>
                                                                                        <section id="travel-info" class="camp-section scroll-mt-24">
                                                                                            <h2 class="camp-section__title">Travel Information</h2>
                                                                                            <ul class="camp-section__list">
                                                                                                @foreach ($camp['travel_info'] as $info)
                                                                                                    <li>{{ $info }}</li>
                                                                                                @endforeach
                                                                                            </ul>
                                                                                        </section>
                                                                                        <section id="extras" class="camp-section scroll-mt-24">
                                                                                            <h2 class="camp-section__title">Extras</h2>
                                                                                            <div class="camp-pill-row">
                                                                                                @foreach ($camp['extras'] as $extra)
                                                                                                    <span class="camp-pill">{{ $extra }}</span>
                                                                                                @endforeach
                                                                                            </div>
                                                                                        </section>
                                                                                    </div>
                                                                                </div>
                                                                            </main>
        </div>
    <aside id="konfigurator" class="camp-config lg:sticky lg:top-20 h-max">
                                <div class="camp-config-card rounded-2xl border bg-white p-4 shadow-md section-card">
                                    <div class="text-lg font-semibold" style="color: var(--brand)">Configure Trip</div>
                                    <div class="mt-2 accent-badge">
                                        This offer is an <strong>inquiry</strong>. We confirm or decline within <strong>48 hours</strong>.
                                    </div>
                                    <div class="camp-form mt-4">
                                        <div class="camp-form-grid">
                                            <div class="camp-form-field full-span">
                                                <label for="accSelect">Accommodation</label>
                                                <select
                                                    id="accSelect"
                                                    class="camp-control"
                                                    x-model="selectedAccId"
                                                    @change="selectedAccId = $event.target.value"
                                                >
                                                    @foreach ($accommodations as $accommodation)
                                                        <option value="{{ $accommodation['id'] }}">
                                                            {{ $accommodation['title'] }} - {{ number_format($accommodation['price']['amount'], 2, ',', '.') }} {{ $accommodation['price']['currency'] }} / night
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="camp-form-field">
                                                <label for="boatSelect">Boat Rental</label>
                                                <select
                                                    id="boatSelect"
                                                    class="camp-control"
                                                    x-model="selectedBoatId"
                                                    @change="selectedBoatId = $event.target.value || null"
                                                >
                                                    <option value="">No boat</option>
                                                    @foreach ($boats as $boat)
                                                        <option value="{{ $boat['id'] }}">
                                                            {{ $boat['title'] }} - {{ number_format($boat['price_per_day'], 2, ',', '.') }} {{ $boat['currency'] }} / day
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="camp-form-field">
                                                <label for="guideSelect">Guiding</label>
                                                <select
                                                    id="guideSelect"
                                                    class="camp-control"
                                                    x-model="selectedGuideId"
                                                    @change="selectedGuideId = $event.target.value || null"
                                                >
                                                    <option value="">No guiding</option>
                                                    @foreach ($guidings as $guiding)
                                                        <option value="{{ $guiding['id'] }}">
                                                            {{ $guiding['title'] }} - {{ number_format($guiding['price'], 2, ',', '.') }} {{ $guiding['currency'] }} fixed price
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="camp-form-field full-span">
                                                <label for="guestInput">Persons</label>
                                                <input
                                                    id="guestInput"
                                                    type="number"
                                                    min="1"
                                                    :max="selectedAcc ? selectedAcc.max_occupancy : 10"
                                                    x-model.number="guests"
                                                    @input="
                                                        const max = selectedAcc ? selectedAcc.max_occupancy : 10;
                                                        if (!guests || guests < 1) { guests = 1; }
                                                        if (guests > max) { guests = max; }
                                                    "
                                                    class="camp-control"
                                                >
                                                <p class="camp-form-field__note" x-text="selectedAcc ? 'Maximum ' + selectedAcc.max_occupancy + ' persons for the selected accommodation.' : 'Maximum 10 persons.'"></p>
                                            </div>
                                            <div class="camp-form-field">
                                                <label for="checkInInput">Check-in</label>
                                                <input
                                                    id="checkInInput"
                                                    type="date"
                                                    x-model="checkIn"
                                                    class="camp-control"
                                                >
                                            </div>
                                            <div class="camp-form-field">
                                                <label for="checkOutInput">Check-out</label>
                                                <input
                                                    id="checkOutInput"
                                                    type="date"
                                                    x-model="checkOut"
                                                    class="camp-control"
                                                >
                                            </div>
                                        </div>
                                        <div class="camp-form__status" x-text="nights ? nights + ' nights selected' : 'Please select travel dates'"></div>
                                    </div>
                                    <div class="camp-summary mt-4">
                                        <div class="camp-summary__row">
                                            <span>Accommodation</span>
                                            <span x-text="selectedAcc ? fmt(accPrice, selectedAcc?.price?.currency ?? 'EUR') : '--'"></span>
                                        </div>
                                        <div class="camp-summary__row">
                                            <span>Boat Rental</span>
                                            <span x-text="selectedBoat ? fmt(boatPrice, selectedBoat?.currency ?? 'EUR') : '--'"></span>
                                        </div>
                                        <div class="camp-summary__row">
                                            <span>Guiding</span>
                                            <span x-text="selectedGuide ? fmt(guidePrice, selectedGuide?.currency ?? 'EUR') : '--'"></span>
                                        </div>
                                        <div class="camp-summary__row camp-summary__total">
                                            <span>Total</span>
                                            <span x-text="total ? fmt(total, 'EUR') : '--'"></span>
                                        </div>
                                        <p class="camp-summary__note">Send inquiry - binding confirmation within 48 hours.</p>
                                    </div>
                                    <button class="mt-3 w-full brand-btn py-2.5 font-medium">Send Inquiry</button>
                                </div>
            </aside>
    </div>

    <!-- Accommodations Section -->
    <div class="camp-container">
        <section id="accommodations" class="camp-section">
            <h2 class="camp-section__title">Accommodations</h2>
            
            @php
                // Sample accommodation data - in a real app, this would come from a database
                $sampleAccommodation = [
                    'id' => 1,
                    'title' => 'Apartment 3 – Lake View',
                    'accommodation_type' => 'Apartment / Holiday Home',
                    'thumbnail_path' => 'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?q=80&w=1600&auto=format&fit=crop',
                    'gallery_images' => [
                        'https://images.unsplash.com/photo-1523217582562-09d0def993a6?q=80&w=1600&auto=format&fit=crop',
                        'https://images.unsplash.com/photo-1505691723518-36a1f0f6f1b6?q=80&w=1600&auto=format&fit=crop',
                        'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?q=80&w=1600&auto=format&fit=crop',
                    ],
                    'city' => 'Riba Roja d\'Ebre',
                    'region' => 'Catalonia',
                    'country' => 'Spain',
                    'description' => 'Bright apartment with direct water access – perfect for 2–4 anglers. Short distances to boat jetty & filleting station.',
                    'living_area_sqm' => 80,
                    'max_occupancy' => 4,
                    'number_of_bedrooms' => 2,
                    'bathrooms' => 1,
                    'floors' => 'EG',
                    'year_or_renovated' => 'Renovated 2023',
                    'living_room' => true,
                    'dining_room' => true,
                    'bed_config' => (object)[
                        'single' => 2, 
                        'double' => 1, 
                        'sofabed' => 0, 
                        'bunk' => 0, 
                        'child' => 0, 
                        'folding' => 0
                    ],
                    'location_description' => 'Near the shore, sheltered bay; ideal for mooring and casting off.',
                    'distances' => (object)[
                        'to_water_m' => 40, 
                        'to_berth_m' => 60, 
                        'to_parking_m' => 20
                    ],
                    'amenities' => (object)[
                        'terrace' => true,
                        'garden' => false,
                        'pool' => false,
                        'bbq_area' => true,
                        'lockable_fishing_storage' => true,
                        'parking_spaces' => true,
                        'ev_charger' => false,
                        'tv' => true,
                        'keybox' => true,
                        'heating' => true,
                        'aircon' => false,
                        'fireplace' => false,
                        'sauna' => false,
                        'hot_tub' => false,
                        'games_corner' => false,
                        'fish_cleaning_station' => true,
                        'fishfilet_freezer' => true,
                        'wifi' => true,
                    ],
                    'kitchen' => (object)[
                        'refrigerator_freezer' => true,
                        'freezer_compartment' => true,
                        'oven' => true,
                        'stove' => true,
                        'microwave' => true,
                        'dishwasher' => true,
                        'coffee_machine' => 'Filter',
                        'kettle' => true,
                        'toaster' => true,
                        'blender' => false,
                        'cutlery' => true,
                        'baking_equipment' => true,
                        'dishwashing_items' => true,
                        'wine_glasses' => true,
                        'pans_pots' => true,
                        'sink' => true,
                        'basics' => true,
                    ],
                    'bathroom_laundry' => (object)[
                        'toilet' => 1,
                        'shower' => 1,
                        'washbasin' => 1,
                        'washing_machine' => true,
                        'dryer' => false,
                        'separate_wc_bath' => false,
                        'iron_board' => true,
                        'drying_rack' => true,
                    ],
                    'policies' => (object)[
                        'pets_allowed' => false,
                        'smoking_allowed' => false,
                        'children_allowed' => true,
                        'accessible' => false,
                        'self_checkin' => true,
                        'quiet_hours' => '22:00–7:00',
                        'waste_rules' => 'Waste separation, containers at parking lot',
                        'only_registered_guests' => true,
                        'deposit_required' => true,
                        'energy_included' => true,
                        'water_included' => true,
                    ],
                    'extras_inclusives' => (object)[
                        'inclusives' => ['WiFi', 'Electricity/Heating'],
                        'extras' => ['Bed linen', 'Towels', 'Final cleaning'],
                    ],
                    'price' => (object)[
                        'type' => 'per night', 
                        'amount' => 110, 
                        'currency' => 'EUR'
                    ],
                    'changeover_day' => 'Saturday',
                    'minimum_stay_nights' => 3,
                ];
            @endphp

            <div x-data="accommodationCard(@js($sampleAccommodation))" class="space-y-4">
                <article class="accommodation-card">
                    <div class="grid">
                        <!-- Left Column: Gallery + Details + Inclusives/Extras -->
                        <div class="left-column">
                            <div class="accommodation-gallery">
                                <img :src="currentImage || 'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?q=80&w=1600&auto=format&fit=crop'" :alt="accommodation.title || 'Apartment 3 - Lake View'" />
                                
                                <div>
                                    <button 
                                        type="button"
                                        aria-label="Previous image" 
                                        @click="prevImage()"
                                        class="gallery-nav-btn prev"
                                    >
                                        ‹
                                    </button>
                                    <button 
                                        type="button"
                                        aria-label="Next image" 
                                        @click="nextImage()"
                                        class="gallery-nav-btn next"
                                    >
                                        ›
                                    </button>
                                    <div class="gallery-counter" x-text="(currentImageIndex + 1) + '/' + (images.length || 3)">1/3</div>
                                </div>

                                <div class="info-chips">
                                    <span class="info-chip">
                                        👥 <span x-text="accommodation.max_occupancy || '4'">4</span> Pers
                                    </span>
                                    <span class="info-chip">
                                        🛏️ <span x-text="accommodation.number_of_bedrooms || '2'">2</span> BR
                                    </span>
                                    <span class="info-chip">
                                        🛁 <span x-text="accommodation.bathrooms || '1'">1</span> Bath
                                    </span>
                                    <span class="info-chip">
                                        📐 <span x-text="accommodation.living_area_sqm || '80'">80</span> m²
                                    </span>
                                </div>
                            </div>

                            <!-- Details Section -->
                            <div class="detail-box">
                                <div class="detail-box-title">Details</div>
                                <ul class="detail-list">
                                    <li>Floor(s): <span class="font-medium" x-text="accommodation.floors || 'EG'">EG</span></li>
                                    <li>Built/Renovated: <span class="font-medium" x-text="accommodation.year_or_renovated || 'Renovated 2023'">Renovated 2023</span></li>
                                    <li>Living room: <span class="font-medium" x-text="accommodation.living_room ? 'Yes' : 'No'">Yes</span></li>
                                    <li>Dining room: <span class="font-medium" x-text="accommodation.dining_room ? 'Yes' : 'No'">Yes</span></li>
                                </ul>
                            </div>

                            <!-- Inclusives & Extras -->
                            <div class="detail-box">
                                <div class="detail-box-title">Included & Extras</div>
                                
                                <div>
                                    <div class="detail-box-title" style="font-size: 11px; margin-bottom: 2px;">Included</div>
                                    <div class="inclusive-extras">
                                        <span class="inclusive-chip">✅ WiFi</span>
                                        <span class="inclusive-chip">✅ Electricity/Heating</span>
                                    </div>
                                </div>

                                <div style="margin-top: 8px;">
                                    <div class="detail-box-title" style="font-size: 11px; margin-bottom: 2px;">Extras</div>
                                    <div class="inclusive-extras">
                                        <span class="inclusive-chip">✅ Bed linen</span>
                                        <span class="inclusive-chip">✅ Towels</span>
                                        <span class="inclusive-chip">✅ Final cleaning</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Header, Price, Chips, Matrix, Amenities -->
                        <div class="right-column">
                            <div class="accommodation-header">
                                <div class="accommodation-info">
                                    <div class="accommodation-location">
                                        📍 <span x-text="accommodation.city + ', ' + accommodation.region + ', ' + accommodation.country">Riba Roja d'Ebre, Catalonia, Spain</span>
                                    </div>
                                    <h3 class="accommodation-title" x-text="accommodation.title">Apartment 3 – Lake View</h3>
                                    <div class="accommodation-type" x-text="accommodation.accommodation_type">Apartment / Holiday Home</div>
                                    <p class="accommodation-description" x-text="accommodation.description">Bright apartment with direct water access – perfect for 2–4 anglers. Short distances to boat jetty & filleting station.</p>
                                </div>
                                <div class="accommodation-pricing">
                                    <div class="price-type" x-text="accommodation.price?.type || 'per night'">per night</div>
                                    <div class="price-amount" x-text="accommodation.price?.amount ? fmt(accommodation.price.amount, accommodation.price.currency || 'EUR') : '€110.00'">€110.00</div>
                                    <button class="select-accommodation-btn">
                                        Select Accommodation
                                    </button>
                                </div>
                            </div>

                            <!-- Distance Chips -->
                            <div class="distance-chips">
                                <span class="distance-chip">
                                    🌊 Water: <span x-text="accommodation.distances?.to_water_m || '40'">40</span> m
                                </span>
                                <span class="distance-chip">
                                    ⚓ Jetty: <span x-text="accommodation.distances?.to_berth_m || '60'">60</span> m
                                </span>
                                <span class="distance-chip">
                                    🚗 Parking: <span x-text="accommodation.distances?.to_parking_m || '20'">20</span> m
                                </span>
                            </div>

                            <!-- Info Matrix: 3 boxes top, 4th bottom full-width -->
                            <div class="info-matrix">
                                <!-- Box 1: Beds & Location -->
                                <div class="info-box">
                                    <div class="info-box-title">Beds & Location</div>
                                    <div class="info-box-content">
                                        <div class="font-medium" x-text="getBedSummary() || '2× Single • 1× Double'">2× Single • 1× Double</div>
                                        <div style="margin-top: 8px;">
                                            <div class="info-box-title">Location</div>
                                            <p style="color: #334155; line-height: 1.4;" x-text="accommodation.location_description || 'Near the shore, sheltered bay; ideal for mooring and casting off.'">Near the shore, sheltered bay; ideal for mooring and casting off.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Box 2: Bath/Laundry -->
                                <div class="info-box">
                                    <div class="info-box-title">Bath/Laundry</div>
                                    <div class="info-box-content" x-text="getBathList().join(' · ') || 'Showers 1 • WC 1 • Washbasin 1 • Washing machine • Iron/Board • Drying rack'">Showers 1 • WC 1 • Washbasin 1 • Washing machine • Iron/Board • Drying rack</div>
                                </div>

                                <!-- Box 3: Kitchen -->
                                <div class="info-box">
                                    <div class="info-box-title">Kitchen</div>
                                    <div class="info-box-content" x-text="getKitchenList().join(' · ') || 'Fridge/Freezer • Oven • Stove • Microwave • Dishwasher • Coffee (Filter) • Kettle • Toaster • Cutlery • Wine glasses • Pans & Pots • Baking utensils • Dish soap/Sponge • Sink • Basics (Oil/Spices)'">Fridge/Freezer • Oven • Stove • Microwave • Dishwasher • Coffee (Filter) • Kettle • Toaster • Cutlery • Wine glasses • Pans & Pots • Baking utensils • Dish soap/Sponge • Sink • Basics (Oil/Spices)</div>
                                </div>

                                <!-- Box 4: Policies & Conditions (full width) -->
                                <div class="info-box">
                                    <div class="info-box-title">Policies & Conditions</div>
                                    <div class="info-box-content" x-text="getPolicyList().slice(0, 6).join(' · ') || 'Pets forbidden • Smoking forbidden • Children allowed • Accessible no • Self check-in yes • Only registered guests yes'">Pets forbidden • Smoking forbidden • Children allowed • Accessible no • Self check-in yes • Only registered guests yes</div>
                                    <div class="info-box-title" style="margin-top: 8px;">Conditions</div>
                                    <div class="info-box-content" x-text="getConditions() || 'Changeover day Saturday • 3 nights min.'">Changeover day Saturday • 3 nights min.</div>
                                </div>
                            </div>

                            <!-- Amenities -->
                            <div class="amenities-section">
                                <div class="amenities-title">Amenities</div>
                                <div class="amenities-chips">
                                    <span class="amenity-chip">WiFi</span>
                                    <span class="amenity-chip">Fishing room</span>
                                    <span class="amenity-chip">Filleting station</span>
                                    <span class="amenity-chip">Fish freezer</span>
                                    <span class="amenity-chip">BBQ Area</span>
                                    <span class="amenity-chip">Parking spaces</span>
                                    <span class="amenity-chip">TV</span>
                                    <span class="amenity-chip">Terrace</span>
                                    <span class="amenity-chip">Keybox</span>
                                    <span class="amenity-chip">Heating</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </div>

    <!-- Guidings/Tours Section -->
    <div class="camp-container">
        <section id="guidings" class="camp-section">
            <h2 class="camp-section__title">Guidings & Tours</h2>
            
            @php
                // Sample guiding data matching the image design
                $sampleGuiding = [
                    'id' => 1,
                    'title' => 'Shore-Guiding Nacht - Wels',
                    'location' => 'Bucht Nord - Riba Roja',
                    'description' => 'Uferbasiertes Nachtangeln auf Wels; Spots an Altarmen & Warmwassereinläufen.',
                    'thumbnail_path' => 'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop',
                    'gallery_images' => [
                        'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop',
                        'https://images.unsplash.com/photo-1469536526925-9b5547cd51f6?q=80&w=1600&auto=format&fit=crop',
                        'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=1600&auto=format&fit=crop',
                    ],
                    'duration_hours' => 6,
                    'max_persons' => 3,
                    'type' => 'Ufer',
                    'guiding_info' => [
                        'art' => 'Ufer',
                        'dauer' => '6 h',
                        'max_personen' => 3,
                        'gewaesser' => 'Stausee / Uferzonen'
                    ],
                    'target_fish' => ['Wels'],
                    'methods' => ['U-Pose', 'Boje', 'Grundmontage'],
                    'meeting_point' => 'Bucht Nord - Riba Roja',
                    'start_times' => ['abends', 'nachts'],
                    'inclusives' => ['Spottransfer', 'Signalhorn'],
                    'price' => [
                        'amount' => 260.00,
                        'currency' => 'EUR',
                        'type' => 'pro Tour'
                    ]
                ];
            @endphp

            <div x-data="guidingCard(@js($sampleGuiding))" class="space-y-4">
                <article class="guiding-card">
                    <div class="guiding-card-grid">
                        <!-- Left Column: Image + Inclusives -->
                        <div class="guiding-left-column">
                            <div class="guiding-gallery">
                                <img :src="currentImage || 'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop'" :alt="guiding.title || 'Shore-Guiding Nacht - Wels'" />
                            </div>
                            
                            <!-- Inclusives Section below Image -->
                            <div class="guiding-inclusives-box">
                                <div class="inclusives-title">Inklusive</div>
                                <div class="inclusives-chips">
                                    <span class="inclusive-chip">
                                        <svg class="check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        <span x-text="guiding.inclusives?.[0] || 'Spottransfer'">Spottransfer</span>
                                    </span>
                                    <span class="inclusive-chip">
                                        <svg class="check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        <span x-text="guiding.inclusives?.[1] || 'Signalhorn'">Signalhorn</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Content -->
                        <div class="guiding-right-column">
                            <!-- Row 1: Location | Preis pro Tour label -->
                            <div class="guiding-info-grid">
                                    <div class="guiding-location">
                                    <svg class="location-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    <span class="guiding-location-text" x-text="guiding.location || 'Bucht Nord - Riba Roja'">Bucht Nord - Riba Roja</span>
                                    </div>
                                <div class="guiding-price-label">Preis pro Tour</div>
                            </div>

                            <!-- Row 2: Title | Price Amount -->
                            <div class="guiding-info-grid">
                                    <h3 class="guiding-title" x-text="guiding.title">Shore-Guiding Nacht - Wels</h3>
                                <div class="guiding-price-amount" x-text="guiding.price?.amount ? fmt(guiding.price.amount, guiding.price.currency || 'EUR') : '260,00 €'">260,00 €</div>
                            </div>

                            <!-- Row 3: Description | Button -->
                            <div class="guiding-info-grid">
                                    <p class="guiding-description" x-text="guiding.description">Uferbasiertes Nachtangeln auf Wels; Spots an Altarmen & Warmwassereinläufen.</p>
                                <button class="select-guiding-btn">
                                    Dieses Guiding übernehmen
                                </button>
                            </div>

                            <!-- Information Pills with minimal icons -->
                            <div class="guiding-info-pills">
                                <span class="guiding-info-pill">
                                    <svg class="pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    <span x-text="guiding.duration_hours || '6'">6</span> h
                                </span>
                                <span class="guiding-info-pill">
                                    <svg class="pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span x-text="guiding.max_persons || '3'">3</span> Pers
                                </span>
                                <span class="guiding-info-pill">
                                    <svg class="pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M2 6s1.5-2 5-2 5 2 5 2 1.5-2 5-2 5 2 5 2v14s-1.5-2-5-2-5 2-5 2-1.5-2-5-2-5 2-5 2V6z"/>
                                    </svg>
                                    <span x-text="guiding.type || 'Ufer'">Ufer</span>
                                </span>
                            </div>

                            <!-- Information Panels (2x2 Grid: top 2, bottom 1 spanning) -->
                            <div class="guiding-info-panels">
                                <!-- Panel 1: Guiding Information -->
                                <div class="guiding-panel">
                                    <div class="guiding-panel-title">Guiding-Informationen</div>
                                    <div class="guiding-panel-content">
                                        <div>Art: <strong x-text="guiding.guiding_info?.art || 'Ufer'">Ufer</strong></div>
                                        <div>Dauer: <strong x-text="guiding.guiding_info?.dauer || '6 h'">6 h</strong></div>
                                        <div>Max. Personen: <strong x-text="guiding.guiding_info?.max_personen || '3'">3</strong></div>
                                        <div>Gewässer: <strong x-text="guiding.guiding_info?.gewaesser || 'Stausee / Uferzonen'">Stausee / Uferzonen</strong></div>
                                    </div>
                                </div>

                                <!-- Panel 2: Target Fish & Methods (Top Right) -->
                                <div class="guiding-panel">
                                    <div class="guiding-panel-title">Zielfische</div>
                                    <div class="target-fish-chip">
                                        <svg class="chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M6.5 12c.94-3.46 4.94-6 8.5-6 3.56 0 6.06 2.54 7 6-1 3.46-3.44 6-7 6s-7.56-2.54-8.5-6z"/>
                                            <path d="M18 5L22 9M18 19L22 15M6 9L2 5M6 15L2 19"/>
                                        </svg>
                                        <span x-text="guiding.target_fish?.[0] || 'Wels'">Wels</span>
                                        </div>
                                    <div class="guiding-panel-title" style="margin-top: 12px;">Methoden</div>
                                    <div class="methods-chips">
                                        <span class="method-chip" x-text="guiding.methods?.[0] || 'U-Pose'">U-Pose</span>
                                        <span class="method-chip" x-text="guiding.methods?.[1] || 'Boje'">Boje</span>
                                        <span class="method-chip" x-text="guiding.methods?.[2] || 'Grundmontage'">Grundmontage</span>
                                    </div>
                                </div>

                                <!-- Panel 3: Location & Notes (Bottom, Full Width) -->
                                <div class="guiding-panel">
                                    <div class="guiding-panel-title">Ort & Hinweise</div>
                                    <div class="guiding-panel-content">
                                        <div>Treffpunkt: <strong x-text="guiding.meeting_point || 'Bucht Nord - Riba Roja'">Bucht Nord - Riba Roja</strong></div>
                                        <div style="margin-top: 8px;">Startzeiten:</div>
                                        <div class="start-times-chips">
                                            <span class="start-time-chip">
                                                <svg class="chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="5"/>
                                                    <line x1="12" y1="1" x2="12" y2="3"/>
                                                    <line x1="12" y1="21" x2="12" y2="23"/>
                                                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                                                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                                                    <line x1="1" y1="12" x2="3" y2="12"/>
                                                    <line x1="21" y1="12" x2="23" y2="12"/>
                                                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                                                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                                                </svg>
                                                <span x-text="guiding.start_times?.[0] || 'abends'">abends</span>
                                            </span>
                                            <span class="start-time-chip">
                                                <svg class="chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                                                </svg>
                                                <span x-text="guiding.start_times?.[1] || 'nachts'">nachts</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>
                            </div>

    <!-- Rental Boats Section -->
    <div class="camp-container">
        <section id="boats" class="camp-section">
            <h2 class="camp-section__title">Rental Boats</h2>
            
            @php
                // Sample boat data
                $sampleBoat = [
                    'id' => 1,
                    'title' => 'Pedal-Kajak 3,6 m',
                    'type' => 'Kajak',
                    'location' => 'Flachwasser-Bucht am Camp',
                    'description' => 'Leises Kayak mit Pedalantrieb für stealthy Ufernähe und Krautfelder. Ideal zum Casting auf Schwarzbarsch.',
                    'thumbnail_path' => 'https://images.unsplash.com/photo-1520440229-84f3865cf003?q=80&w=1600&auto=format&fit=crop',
                    'gallery_images' => [
                        'https://images.unsplash.com/photo-1520440229-84f3865cf003?q=80&w=1600&auto=format&fit=crop',
                        'https://images.unsplash.com/photo-1505839673365-e3971f8d9184?q=80&w=1600&auto=format&fit=crop',
                    ],
                    'seats' => 1,
                    'length_m' => 3.6,
                    'width_m' => 0.9,
                    'year_built' => 2022,
                    'manufacturer' => 'Hobie',
                    'engine' => '-',
                    'power' => '-',
                    'max_speed_kmh' => 8,
                    'equipment' => ['Rutenhalter', 'Anker', 'Signalhorn', 'Erste Hilfe', 'Schwimmwesten', 'Ruder'],
                    'requirements' => ['Führerschein nicht nötig', 'Mindestalter 14', 'Ausweis mitbringen', 'Kaution nein', 'Sicherheitsunterweisung', 'Schwimmwestenpflicht'],
                    'inclusives' => ['Sicherheitsunterweisung', 'Anker', 'Signalhorn', 'Erste Hilfe Set', 'Schwimmweste'],
                    'extras' => ['Dry Bag'],
                    'price' => [
                        'amount' => 25.00,
                        'currency' => 'EUR',
                        'type' => 'pro Tag'
                    ]
                ];
            @endphp

            <div x-data="boatCard(@js($sampleBoat))" class="space-y-4">
                <article class="guiding-card">
                    <div class="guiding-card-grid">
                        <!-- Left Column: Image + Inclusives & Extras -->
                        <div class="guiding-left-column">
                            <div class="guiding-gallery">
                                <img :src="currentImage || 'https://images.unsplash.com/photo-1520440229-84f3865cf003?q=80&w=1600&auto=format&fit=crop'" :alt="boat.title || 'Pedal-Kajak 3,6 m'" />
                            </div>
                            
                            <!-- Inclusives & Extras Section below Image -->
                            <div class="guiding-inclusives-box">
                                <div class="inclusives-title">Inklusive & Extras</div>
                                
                                <div style="margin-bottom: 12px;">
                                    <div class="inclusives-title" style="font-size: 12px; margin-bottom: 6px;">Inklusive</div>
                                    <div class="inclusives-chips" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                                        <span class="inclusive-chip">
                                            <svg class="check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            <span>Sicherheitsunterweisung</span>
                                        </span>
                                        <span class="inclusive-chip">
                                            <svg class="check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            <span>Anker</span>
                                        </span>
                                        <span class="inclusive-chip">
                                            <svg class="check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            <span>Signalhorn</span>
                                        </span>
                                        <span class="inclusive-chip">
                                            <svg class="check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            <span>Erste Hilfe Set</span>
                                        </span>
                                        <span class="inclusive-chip">
                                            <svg class="check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            <span>Schwimmweste</span>
                                        </span>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="inclusives-title" style="font-size: 12px; margin-bottom: 6px;">Extras</div>
                                    <div class="inclusives-chips">
                                        <span class="inclusive-chip">
                                            <svg class="check-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            <span>Dry Bag</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Content -->
                        <div class="guiding-right-column">
                            <!-- Row 1: Location | Preis pro Tag label -->
                            <div class="guiding-info-grid">
                                <div class="guiding-location">
                                    <svg class="location-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    <span class="guiding-location-text" x-text="boat.location || 'Flachwasser-Bucht am Camp'">Flachwasser-Bucht am Camp</span>
                                </div>
                                <div class="guiding-price-label" x-text="boat.price?.type || 'pro Tag'">pro Tag</div>
                            </div>

                            <!-- Row 2: Title | Price Amount -->
                            <div class="guiding-info-grid">
                                <h3 class="guiding-title" x-text="boat.title">Pedal-Kajak 3,6 m</h3>
                                <div class="guiding-price-amount" x-text="boat.price?.amount ? fmt(boat.price.amount, boat.price.currency || 'EUR') : '€25.00'">25,00 €</div>
                            </div>

                            <!-- Row 3: Type + Description | Button -->
                            <div class="guiding-info-grid">
                                <div>
                                    <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;" x-text="boat.type">Kajak</div>
                                    <p class="guiding-description" x-text="boat.description">Leises Kayak mit Pedalantrieb für stealthy Ufernähe und Krautfelder. Ideal zum Casting auf Schwarzbarsch.</p>
                                </div>
                            <button class="select-guiding-btn">
                                    Dieses Boot übernehmen
                            </button>
                            </div>

                            <!-- Information Pills with minimal icons -->
                            <div class="guiding-info-pills">
                                <span class="guiding-info-pill">
                                    <svg class="pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span x-text="boat.seats || '1'">1</span> Sitze
                                </span>
                                <span class="guiding-info-pill">
                                    <svg class="pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                        <polyline points="12 5 19 12 12 19"/>
                                    </svg>
                                    <span x-text="boat.length_m || '3.6'">3.6</span> m
                                </span>
                                <span class="guiding-info-pill">
                                    <svg class="pill-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="3" y1="12" x2="21" y2="12"/>
                                        <line x1="3" y1="6" x2="21" y2="6"/>
                                        <line x1="3" y1="18" x2="21" y2="18"/>
                                    </svg>
                                    <span x-text="boat.width_m || '0.9'">0.9</span> m breit
                                </span>
                                </div>

                            <!-- Three Information Panels -->
                            <div class="guiding-info-panels">
                                <!-- Panel 1: Boat Information -->
                                <div class="guiding-panel">
                                    <div class="guiding-panel-title">Bootsinformationen</div>
                                    <div class="guiding-panel-content">
                                        <div>Sitzplätze: <strong x-text="boat.seats || '1'">1</strong></div>
                                        <div>Länge: <strong x-text="boat.length_m + ' m' || '3.6 m'">3.6 m</strong></div>
                                        <div>Breite: <strong x-text="boat.width_m + ' m' || '0.9 m'">0.9 m</strong></div>
                                        <div>Baujahr: <strong x-text="boat.year_built || '2022'">2022</strong></div>
                                        <div>Boot: <strong x-text="boat.manufacturer || 'Hobie'">Hobie</strong></div>
                                        <div>Motor: <strong x-text="boat.engine || '-'">-</strong></div>
                                        <div>Leistung: <strong x-text="boat.power || '-'">-</strong></div>
                                        <div>Vmax: <strong x-text="boat.max_speed_kmh + ' km/h' || '8 km/h'">8 km/h</strong></div>
                                    </div>
                                </div>

                                <!-- Panel 2: Equipment -->
                                <div class="guiding-panel">
                                    <div class="guiding-panel-title">Ausstattung</div>
                                    <div class="methods-chips">
                                        <span class="method-chip">
                                            <svg class="chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="2"/>
                                                <path d="M12 1v6m0 6v6m-6-6h6m6 0h-6"/>
                                            </svg>
                                            Rutenhalter
                                        </span>
                                        <span class="method-chip">
                                            <svg class="chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 2v20m0-4c-2 0-4-2-4-4s2-4 4-4m0 8c2 0 4-2 4-4s-2-4-4-4"/>
                                            </svg>
                                            Anker
                                        </span>
                                        <span class="method-chip">
                                            <svg class="chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                                <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                                            </svg>
                                            Signalhorn
                                        </span>
                                        <span class="method-chip">
                                            <svg class="chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                                <polyline points="12 22 12 12"/>
                                            </svg>
                                            Erste Hilfe
                                        </span>
                                        <span class="method-chip">
                                            <svg class="chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"/>
                                            </svg>
                                            Schwimmwesten
                                        </span>
                                        <span class="method-chip">
                                            <svg class="chip-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="12" y1="2" x2="12" y2="22"/>
                                                <polyline points="5 9 12 2 19 9"/>
                                            </svg>
                                            Ruder
                                        </span>
                                    </div>
                                </div>

                                <!-- Panel 3: Requirements -->
                                <div class="guiding-panel">
                                    <div class="guiding-panel-title">Anforderungen</div>
                                    <div class="methods-chips">
                                        <span class="method-chip">Führerschein nicht nötig</span>
                                        <span class="method-chip">Mindestalter 14</span>
                                        <span class="method-chip">Ausweis mitbringen</span>
                                        <span class="method-chip">Kaution nein</span>
                                        <span class="method-chip">Sicherheitsunterweisung</span>
                                        <span class="method-chip">Schwimmwestenpflicht</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </div>

    </div>

@endsection

@push('scripts')
    @once
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endonce
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('campConfigurator', ({ camp, accommodations, boats, guidings, showCategories = true }) => ({
                camp,
                accommodations,
                boats,
                guidings,
                showCategories,
                checkIn: '',
                checkOut: '',
                guests: Math.min(2, accommodations[0]?.max_occupancy ?? 2),
                selectedAccId: accommodations[0]?.id ? String(accommodations[0].id) : null,
                selectedBoatId: null,
                selectedGuideId: null,
                init() {
                    this.$watch('selectedAccId', () => {
                        const max = this.selectedAcc ? this.selectedAcc.max_occupancy : 10;
                        if (this.guests > max) {
                            this.guests = max;
                        }
                        if (!this.guests || this.guests < 1) {
                            this.guests = 1;
                        }
                    });
                },
                fmt(value, currency = 'EUR') {
                    try {
                        return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value ?? 0);
                    } catch (error) {
                        const amount = typeof value === 'number' ? value.toFixed(2) : '0.00';
                        return `${amount} ${currency}`;
                    }
                },
                nightsBetween(start, end) {
                    if (!start || !end) return 0;
                    const startDate = new Date(start);
                    const endDate = new Date(end);
                    if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime())) return 0;
                    const diff = Math.floor((endDate.getTime() - startDate.getTime()) / 86400000);
                    return diff > 0 ? diff : 0;
                },
                blendedPrice(nights, perNight, perWeek) {
                    if (!nights || !perNight) return 0;
                    if (perWeek && nights >= 7) {
                        const weeks = Math.floor(nights / 7);
                        const rest = nights % 7;
                        return weeks * perWeek + rest * perNight;
                    }
                    return nights * perNight;
                },
                get selectedAcc() {
                    return this.accommodations.find(item => String(item.id) === String(this.selectedAccId)) ?? null;
                },
                get selectedBoat() {
                    return this.boats.find(item => String(item.id) === String(this.selectedBoatId)) ?? null;
                },
                get selectedGuide() {
                    return this.guidings.find(item => String(item.id) === String(this.selectedGuideId)) ?? null;
                },
                get nights() {
                    return this.nightsBetween(this.checkIn, this.checkOut);
                },
                get accPrice() {
                    if (!this.selectedAcc) return 0;
                    return this.blendedPrice(this.nights, this.selectedAcc.price?.amount, this.selectedAcc.price?.per_week);
                },
                get boatPrice() {
                    if (!this.selectedBoat) return 0;
                    return (this.selectedBoat.price_per_day || 0) * (this.nights || 0);
                },
                get guidePrice() {
                    if (!this.selectedGuide) return 0;
                    return this.selectedGuide.price || 0;
                },
                get total() {
                    return this.accPrice + this.boatPrice + this.guidePrice;
                },
                get hero() {
                    return this.camp.thumbnail_path || this.camp.manual_gallery_images?.[0] || 'https://images.unsplash.com/photo-1512914890250-33530e2f0d72?q=80&w=1600&auto=format&fit=crop';
                },
                setBoat(id) {
                    this.selectedBoatId = this.selectedBoatId === id ? null : id;
                },
                setGuide(id) {
                    this.selectedGuideId = this.selectedGuideId === id ? null : id;
                },
            }));
        });

        // Accommodation Card Alpine.js Component
        Alpine.data('accommodationCard', (accommodation) => ({
            accommodation,
            images: [],
            currentImageIndex: 0,

            init() {
                // Build images array from thumbnail and gallery
                this.images = [
                    accommodation.thumbnail_path,
                    ...(accommodation.gallery_images || [])
                ].filter(Boolean);
            },

            get currentImage() {
                return this.images[this.currentImageIndex] || this.images[0] || 'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?q=80&w=1600&auto=format&fit=crop';
            },

            nextImage() {
                if (this.images.length > 1) {
                    this.currentImageIndex = (this.currentImageIndex + 1) % this.images.length;
                }
            },

            prevImage() {
                if (this.images.length > 1) {
                    this.currentImageIndex = (this.currentImageIndex - 1 + this.images.length) % this.images.length;
                }
            },

            fmt(value, currency = 'EUR') {
                try {
                    return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value ?? 0);
                } catch (error) {
                    const amount = typeof value === 'number' ? value.toFixed(2) : '0.00';
                    return `${amount} ${currency}`;
                }
            },

            getBedSummary() {
                const bed = this.accommodation.bed_config || {};
                const parts = [];
                if (bed.single) parts.push(`${bed.single}× Single`);
                if (bed.double) parts.push(`${bed.double}× Double`);
                if (bed.sofabed) parts.push(`${bed.sofabed}× Sofa`);
                if (bed.bunk) parts.push(`${bed.bunk}× Bunk`);
                if (bed.child) parts.push(`${bed.child}× Child`);
                if (bed.folding) parts.push(`${bed.folding}× Folding`);
                return parts.join(' · ') || '—';
            },

            getKitchenList() {
                const k = this.accommodation.kitchen || {};
                const list = [];
                if (k.refrigerator_freezer || k.freezer_compartment) list.push('Fridge/Freezer');
                if (k.oven) list.push('Oven');
                if (k.stove) list.push('Stove');
                if (k.microwave) list.push('Microwave');
                if (k.dishwasher) list.push('Dishwasher');
                if (k.coffee_machine) list.push(`Coffee${typeof k.coffee_machine === 'string' ? ' (' + k.coffee_machine + ')' : ''}`);
                if (k.kettle) list.push('Kettle');
                if (k.toaster) list.push('Toaster');
                if (k.blender) list.push('Blender');
                if (k.cutlery) list.push('Cutlery');
                if (k.wine_glasses) list.push('Wine glasses');
                if (k.pans_pots) list.push('Pans & Pots');
                if (k.baking_equipment) list.push('Baking utensils');
                if (k.dishwashing_items) list.push('Dish soap/Sponge');
                if (k.sink) list.push('Sink');
                if (k.basics) list.push('Basics (Oil/Spices)');
                return list;
            },

            getBathList() {
                const b = this.accommodation.bathroom_laundry || {};
                const list = [];
                if (b.shower != null) list.push(`Showers ${b.shower}`);
                if (b.toilet != null) list.push(`WC ${b.toilet}`);
                if (b.washbasin != null) list.push(`Washbasin ${b.washbasin}`);
                if (b.separate_wc_bath) list.push('Separate WC/Bath');
                if (b.washing_machine) list.push('Washing machine');
                if (b.dryer) list.push('Dryer');
                if (b.iron_board) list.push('Iron/Board');
                if (b.drying_rack) list.push('Drying rack');
                return list;
            },

            getAmenityChips() {
                const m = this.accommodation.amenities || {};
                const items = [];
                if (m.wifi) items.push({ label: 'WiFi' });
                if (m.lockable_fishing_storage) items.push({ label: 'Fishing room' });
                if (m.fish_cleaning_station) items.push({ label: 'Filleting station' });
                if (m.fishfilet_freezer) items.push({ label: 'Fish freezer' });
                if (m.bbq_area) items.push({ label: 'BBQ Area' });
                if (m.parking_spaces) items.push({ label: 'Parking spaces' });
                if (m.ev_charger) items.push({ label: 'EV Charger' });
                if (m.tv) items.push({ label: 'TV' });
                if (m.garden) items.push({ label: 'Garden' });
                if (m.terrace) items.push({ label: 'Terrace' });
                if (m.sauna) items.push({ label: 'Sauna' });
                if (m.hot_tub) items.push({ label: 'Hot Tub' });
                if (m.games_corner) items.push({ label: 'Games corner/Darts' });
                if (m.private_jetty) items.push({ label: 'Private jetty' });
                if (m.boat_ramp_nearby) items.push({ label: 'Boat ramp' });
                if (m.keybox) items.push({ label: 'Keybox' });
                if (m.heating) items.push({ label: 'Heating' });
                if (m.aircon) items.push({ label: 'Air conditioning' });
                return items;
            },

            getPolicyList() {
                const p = this.accommodation.policies || {};
                const list = [];
                if (p.pets_allowed != null) list.push(`Pets ${p.pets_allowed ? 'allowed' : 'forbidden'}`);
                if (p.smoking_allowed != null) list.push(`Smoking ${p.smoking_allowed ? 'allowed' : 'forbidden'}`);
                if (p.children_allowed != null) list.push(`Children ${p.children_allowed ? 'allowed' : '—'}`);
                if (p.accessible != null) list.push(`Accessible ${p.accessible ? 'yes' : 'no'}`);
                if (p.self_checkin != null) list.push(`Self check-in ${p.self_checkin ? 'yes' : 'no'}`);
                if (p.only_registered_guests != null) list.push(`Only registered guests ${p.only_registered_guests ? 'yes' : 'no'}`);
                if (p.deposit_required != null) list.push(`Deposit ${p.deposit_required ? 'required' : 'no'}`);
                if (p.energy_included != null) list.push(`Energy incl. ${p.energy_included ? 'yes' : 'no'}`);
                if (p.water_included != null) list.push(`Water incl. ${p.water_included ? 'yes' : 'no'}`);
                if (p.quiet_hours) list.push(`Quiet hours ${p.quiet_hours}`);
                if (p.waste_rules) list.push(`Waste/Recycling: ${p.waste_rules}`);
                return list;
            },

            getConditions() {
                const conditions = [];
                if (this.accommodation.changeover_day) conditions.push(`Changeover day ${this.accommodation.changeover_day}`);
                if (this.accommodation.minimum_stay_nights) conditions.push(`${this.accommodation.minimum_stay_nights} nights min.`);
                return conditions.join(' · ') || '—';
            }
        }));

        // Guiding Card Alpine.js Component
        Alpine.data('guidingCard', (guiding) => ({
            guiding,
            images: [],
            currentImageIndex: 0,

            init() {
                // Build images array from thumbnail and gallery
                this.images = [
                    guiding.thumbnail_path,
                    ...(guiding.gallery_images || [])
                ].filter(Boolean);
            },

            get currentImage() {
                return this.images[this.currentImageIndex] || this.images[0] || 'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop';
            },

            nextImage() {
                if (this.images.length > 1) {
                    this.currentImageIndex = (this.currentImageIndex + 1) % this.images.length;
                }
            },

            prevImage() {
                if (this.images.length > 1) {
                    this.currentImageIndex = (this.currentImageIndex - 1 + this.images.length) % this.images.length;
                }
            },

            fmt(value, currency = 'EUR') {
                try {
                    return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value ?? 0);
                } catch (error) {
                    const amount = typeof value === 'number' ? value.toFixed(2) : '0.00';
                    return `${amount} ${currency}`;
                }
            }
        }));

        // Boat Card Alpine.js Component
        Alpine.data('boatCard', (boat) => ({
            boat,
            images: [],
            currentImageIndex: 0,

            init() {
                // Build images array from thumbnail and gallery
                this.images = [
                    boat.thumbnail_path,
                    ...(boat.gallery_images || [])
                ].filter(Boolean);
            },

            get currentImage() {
                return this.images[this.currentImageIndex] || this.images[0] || 'https://images.unsplash.com/photo-1520440229-84f3865cf003?q=80&w=1600&auto=format&fit=crop';
            },

            nextImage() {
                if (this.images.length > 1) {
                    this.currentImageIndex = (this.currentImageIndex + 1) % this.images.length;
                }
            },

            prevImage() {
                if (this.images.length > 1) {
                    this.currentImageIndex = (this.currentImageIndex - 1 + this.images.length) % this.images.length;
                }
            },

            fmt(value, currency = 'EUR') {
                try {
                    return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value ?? 0);
                } catch (error) {
                    const amount = typeof value === 'number' ? value.toFixed(2) : '0.00';
                    return `${amount} ${currency}`;
                }
            }
        }));
    </script>
@endpush
