@extends('admin.layouts.app')

@section('title', __('admin.guidings.page_title'))

@section('css_after')
<style>
    #guidingDetailsModal .guiding-detail-value { white-space: pre-wrap; word-break: break-word; }
    #guidingDetailsModal .modal-dialog-scrollable .modal-body { max-height: 85vh; }
    @media (min-width: 1200px) {
        #guidingDetailsModal .modal-xxl { max-width: 95vw; width: 95vw; }
    }

    /* Admin guidings list – visual polish */
    #guiding-datatable {
        border-collapse: separate;
        border-spacing: 0 6px;
    }

    #guiding-datatable thead th {
        border-bottom-width: 1px !important;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b7280;
        background-color: #f9fafb;
        white-space: nowrap;
    }

    #guiding-datatable tbody tr {
        background-color: #ffffff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    #guiding-datatable tbody tr:hover {
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
    }

    /* Guide avatar + name */
    .guiding-guide-cell {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        white-space: nowrap;
    }

    .guiding-guide-avatar,
    .guiding-guide-avatar-placeholder {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .guiding-guide-avatar {
        object-fit: cover;
        border: 2px solid #e5e7eb;
        background-color: #f3f4f6;
    }

    .guiding-guide-avatar-placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.8rem;
        color: #ffffff;
        background: radial-gradient(circle at 30% 30%, #60a5fa, #2563eb);
        border: 2px solid #e5e7eb;
        text-transform: uppercase;
    }

    .guiding-guide-name {
        font-weight: 500;
        font-size: 0.9rem;
        color: #111827;
    }

    .guiding-guide-name small {
        display: block;
        font-size: 0.75rem;
        color: #6b7280;
    }

    /* Tour images thumbnail column */
    .guiding-images-scroll {
        max-width: 220px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .guiding-images-row {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding-bottom: 0.15rem;
    }

    .guiding-thumb-img {
        width: 46px;
        height: 46px;
        border-radius: 0.55rem;
        object-fit: cover;
        border: 1px solid rgba(148, 163, 184, 0.5);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
        background-color: #f3f4f6;
        flex-shrink: 0;
    }

    .guiding-thumb-img-more {
        position: relative;
    }

    .guiding-thumb-img-more-badge {
        position: absolute;
        right: 4px;
        bottom: 4px;
        padding: 0 6px;
        border-radius: 999px;
        background-color: rgba(15, 23, 42, 0.8);
        color: #f9fafb;
        font-size: 0.7rem;
        font-weight: 500;
    }

    .guiding-images-empty {
        font-size: 0.75rem;
        color: #9ca3af;
        white-space: nowrap;
    }

    /* Language badges */
    .guiding-lang-badge {
        padding: 0.15rem 0.55rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
    }

    .guiding-lang-badge-main {
        background-color: #eef2ff;
        color: #4338ca;
        border-color: #c7d2fe;
    }

    .guiding-lang-badge-alt {
        background-color: #f3f4f6;
        color: #4b5563;
        border-color: #e5e7eb;
    }

    /* Stronger glow for translate button to highlight action */
    .btn-guiding-translate {
        position: relative;
        color: #ffffff;
        background-image: linear-gradient(135deg, #0ea5e9, #2563eb);
        border-color: #0ea5e9;
        box-shadow:
            0 0 0 0 rgba(56, 189, 248, 0.85),
            0 6px 14px rgba(37, 99, 235, 0.35);
        animation: guiding-translate-glow 1.4s ease-in-out infinite;
    }

    .btn-guiding-translate:hover,
    .btn-guiding-translate:focus {
        animation-play-state: paused;
        box-shadow:
            0 0 0 8px rgba(56, 189, 248, 0.45),
            0 8px 18px rgba(37, 99, 235, 0.45);
        transform: translateY(-1px);
    }

    @keyframes guiding-translate-glow {
        0% {
            box-shadow:
                0 0 0 0 rgba(56, 189, 248, 0.9),
                0 5px 12px rgba(37, 99, 235, 0.4);
            transform: translateY(0);
        }
        60% {
            box-shadow:
                0 0 0 12px rgba(56, 189, 248, 0),
                0 7px 16px rgba(37, 99, 235, 0.35);
            transform: translateY(-1px);
        }
        100% {
            box-shadow:
                0 0 0 0 rgba(56, 189, 248, 0),
                0 5px 12px rgba(37, 99, 235, 0.3);
            transform: translateY(0);
        }
    }

    .guiding-lang-dot {
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background-color: currentColor;
        opacity: 0.65;
    }

    .guiding-lang-code {
        letter-spacing: 0.06em;
    }

    .guiding-lang-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        opacity: 0.8;
    }

</style>
@endsection

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">{{ __('admin.guidings.breadcrumb_management') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row ">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                                <a href="{{ route('admin.guidings.create') }}" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> {{ __('admin.guidings.add_guiding') }}
                                </a>
                                @isset($guidingStats)
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="p-2 px-3 rounded border bg-light">
                                            <div class="small text-muted mb-1">{{ __('Total tours') }}</div>
                                            <div class="fw-bold">{{ $guidingStats['total_tours'] }}</div>
                                        </div>
                                        <div class="p-2 px-3 rounded border bg-light">
                                            <div class="small text-muted mb-1">{{ __('Active / Draft') }}</div>
                                            <div class="fw-bold">
                                                {{ $guidingStats['active_tours'] }} /
                                                {{ $guidingStats['draft_tours'] }}
                                            </div>
                                        </div>
                                        <div class="p-2 px-3 rounded border bg-light">
                                            <div class="small text-muted mb-1">{{ __('Tours with bookings') }}</div>
                                            <div class="fw-bold">
                                                {{ $guidingStats['tours_with_bookings'] }}
                                                @if($guidingStats['booked_active_tours_ratio'] !== null)
                                                    <span class="small text-muted">
                                                        ({{ $guidingStats['booked_active_tours_ratio'] }}%)
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="p-2 px-3 rounded border bg-light">
                                            <div class="small text-muted mb-1">{{ __('Bookings (A / R / P)') }}</div>
                                            <div class="fw-bold">
                                                {{ $guidingStats['accepted_bookings'] }}
                                                /
                                                {{ $guidingStats['rejected_bookings'] }}
                                                /
                                                {{ $guidingStats['pending_bookings'] }}
                                            </div>
                                        </div>
                                        <div class="p-2 px-3 rounded border bg-light">
                                            <div class="small text-muted mb-1">{{ __('Success / Cancel rate') }}</div>
                                            <div class="fw-bold">
                                                {{ $guidingStats['booking_success_rate'] ?? '–' }}%
                                                /
                                                {{ $guidingStats['cancellation_rate'] ?? '–' }}%
                                            </div>
                                        </div>
                                    </div>
                                @endisset
                            </div>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="guiding-datatable" class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">{{ __('admin.guidings.th_id') }}</th>
                                        <th class="wd-15p border-bottom-0">{{ __('admin.guidings.th_name') }}</th>
                                        <th class="wd-10p border-bottom-0">{{ __('admin.guidings.th_guide') }}</th>
                                        <th class="wd-15p border-bottom-0">
                                            @php
                                                $imagesHeader = __('admin.guidings.th_images');
                                            @endphp
                                            {{ $imagesHeader === 'admin.guidings.th_images' ? 'Images' : $imagesHeader }}
                                        </th>
                                        <th class="wd-12p border-bottom-0" title="{{ __('admin.guidings.th_languages_hint') }}">{{ __('admin.guidings.th_languages') }}</th>
                                        <th class="wd-25p border-bottom-0">{{ __('admin.guidings.th_actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $translationService = app(\App\Services\Translation\GuidingTranslationService::class);
                                        $translationTargetLangs = \App\Services\Translation\GuidingTranslationService::defaultTargetLanguages();
                                    @endphp
                                    @foreach($guidings as $guiding)
                                    @php
                                        $mainLang = $guiding->language ?? 'de';
                                        $translationLangs = $guiding->languageTranslations->pluck('language')->toArray();
                                        $availableLangs = array_unique(array_merge([$mainLang], $translationLangs));
                                        sort($availableLangs);
                                        $missingLangs = $translationService->getMissingLanguages($guiding, $translationTargetLangs);

                                        // Build small gallery for admin list
                                        $thumbPath = $guiding->thumbnail_path ?? null;
                                        $galleryRaw = $guiding->gallery_images ?? '[]';
                                        $galleryArray = is_array($galleryRaw) ? $galleryRaw : (function ($value) {
                                            try {
                                                return decode_if_json($value, true) ?: [];
                                            } catch (\Throwable $e) {
                                                return [];
                                            }
                                        })($galleryRaw);

                                        $allImages = [];
                                        if ($thumbPath && file_exists(public_path($thumbPath))) {
                                            $allImages[] = asset($thumbPath);
                                        }
                                        if (!empty($galleryArray) && is_array($galleryArray)) {
                                            foreach ($galleryArray as $img) {
                                                if (!empty($img) && $img !== $thumbPath && file_exists(public_path($img))) {
                                                    $allImages[] = asset($img);
                                                }
                                            }
                                        }
                                        $allImages = array_values(array_unique($allImages));
                                        $displayImages = array_slice($allImages, 0, 3);
                                        $extraCount = max(count($allImages) - count($displayImages), 0);

                                        $fullName = $guiding->user->full_name ?? '';
                                        $nameParts = preg_split('/\s+/', trim($fullName));
                                        $initials = '';
                                        if (!empty($nameParts)) {
                                            $initials .= mb_substr($nameParts[0], 0, 1);
                                            if (count($nameParts) > 1) {
                                                $initials .= mb_substr(end($nameParts), 0, 1);
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{$guiding -> id}}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold">{{$guiding -> title}}</div>
                                                <div class="text-info">{{$guiding -> location}}</div>
                                            </div>
       
                                        </td>
                                        <td>
                                            <a href="{{route('admin.guides.edit', $guiding->user->id)}}" class="text-decoration-none">
                                                <div class="guiding-guide-cell">
                                                    @if(!empty($guiding->user->profil_image ?? null))
                                                        <img
                                                            src="{{ asset('uploads/profile_images/' . $guiding->user->profil_image) }}"
                                                            alt="{{ $guiding->user->full_name }}"
                                                            class="guiding-guide-avatar"
                                                        >
                                                    @else
                                                        <span class="guiding-guide-avatar-placeholder">
                                                            {{ $initials ?: 'G' }}
                                                        </span>
                                                    @endif
                                                    <span class="guiding-guide-name">
                                                        {{ $guiding->user->full_name }}
                                                        @if($guiding->user->information->city ?? null)
                                                            <small>{{ $guiding->user->information->city }}</small>
                                                        @endif
                                                    </span>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            @if(!empty($displayImages))
                                                <div
                                                    class="guiding-images-scroll guiding-images-trigger"
                                                    data-guiding-id="{{ $guiding->id }}"
                                                    data-guiding-title="{{ e($guiding->title) }}"
                                                    data-guiding-location="{{ e($guiding->location ?? '') }}"
                                                    data-images='@json($allImages)'
                                                >
                                                    <div class="guiding-images-row">
                                                        @foreach($displayImages as $idx => $img)
                                                            @php
                                                                $isLast = $idx === count($displayImages) - 1;
                                                            @endphp
                                                            <div class="{{ $isLast && $extraCount > 0 ? 'guiding-thumb-img-more' : '' }}">
                                                                <img src="{{ $img }}" alt="Guiding image {{ $idx + 1 }}" class="guiding-thumb-img">
                                                                @if($isLast && $extraCount > 0)
                                                                    <span class="guiding-thumb-img-more-badge">+{{ $extraCount }}</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <span class="guiding-images-empty">{{ __('admin.guidings.no_images') ?? 'No images' }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($availableLangs as $langCode)
                                                @php
                                                    $isMain = $langCode === $mainLang;
                                                    $tooltip = $isMain
                                                        ? (__('admin.guidings.lang_source_tooltip') !== 'admin.guidings.lang_source_tooltip'
                                                            ? __('admin.guidings.lang_source_tooltip')
                                                            : 'Source language')
                                                        : (__('admin.guidings.lang_translated_tooltip') !== 'admin.guidings.lang_translated_tooltip'
                                                            ? __('admin.guidings.lang_translated_tooltip')
                                                            : 'Translated version');
                                                @endphp
                                                <span
                                                    class="badge guiding-lang-badge me-1 {{ $isMain ? 'guiding-lang-badge-main' : 'guiding-lang-badge-alt' }}"
                                                    title="{{ $tooltip }}"
                                                >
                                                    <span class="guiding-lang-dot"></span>
                                                    <span class="guiding-lang-code">{{ strtoupper($langCode) }}</span>
                                                    @if($isMain)
                                                        @php
                                                            $mainLabel = __('admin.guidings.lang_label_main');
                                                            if ($mainLabel === 'admin.guidings.lang_label_main') {
                                                                $mainLabel = 'SRC';
                                                            }
                                                        @endphp
                                                        <span class="guiding-lang-label ms-1">{{ $mainLabel }}</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                            @if(empty($availableLangs))
                                                <span class="text-muted">—</span>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-secondary ms-1 btn-guiding-language" title="{{ __('admin.guidings.btn_set_source_language') }}" data-guiding-id="{{ $guiding->id }}" data-guiding-title="{{ e($guiding->title) }}" data-current-lang="{{ $mainLang }}"><i class="fa fa-globe"></i></button>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                @if($guiding->status == 1)
                                                    <a href="{{ route('admin.changeGuidingStatus', $guiding->id) }}" title="{{ __('admin.guidings.deactivate') }}" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
                                                @else
                                                    <a href="{{ route('admin.changeGuidingStatus', $guiding->id) }}" title="{{ __('admin.guidings.activate') }}" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                                @endif
                                                <a href="{{ route('admin.guidings.edit', $guiding) }}" class="btn btn-sm btn-secondary"><i class="fa fa-pen"></i></a>
                                                <button type="button" class="btn btn-sm btn-primary btn-guiding-details" title="{{ __('admin.guidings.btn_show_details') }}" data-guiding-id="{{ $guiding->id }}" data-guiding-title="{{ e($guiding->title) }}" data-guiding-location="{{ e($guiding->location ?? '') }}" data-guiding-guide-name="{{ e($guiding->user->full_name ?? '') }}"><i class="fa fa-search"></i></button>
                                                <a href="{{ route('admin.guidings.show', $guiding) }}" class="btn btn-sm btn-outline-primary" title="{{ __('admin.guidings.btn_open_page') }}"><i class="fa fa-external-link-alt"></i></a>
                                                @if(!empty($missingLangs))
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-info btn-guiding-translate"
                                                        title="{{ __('admin.guidings.btn_translate_missing', ['langs' => strtoupper(implode(', ', $missingLangs))]) }}"
                                                        data-translate-url="{{ route('admin.guidings.translate', $guiding) }}"
                                                        data-missing="{{ implode(',', $missingLangs) }}"
                                                        data-guiding-title="{{ e($guiding->title) }}"
                                                    >
                                                        <i class="fa fa-language"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row bg-white p-2">
                {{-- <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                                    <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">ID</th>
                                        <th class="wd-15p border-bottom-0">Name des Guidings</th>
                                        <th class="wd-15p border-bottom-0">Ort</th>
                                        <th class="wd-10p border-bottom-0">Guide Name</th>
                                        <th class="wd-25p border-bottom-0">Preis pro Person</th>
                                        <th class="wd-25p border-bottom-0">Preis 2 Personen</th>
                                        <th class="wd-25p border-bottom-0">Preis 3 Personen</th>
                                        <th class="wd-25p border-bottom-0">Aktionen</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($guidings as $guiding)
                                    <tr>
                                        <td>{{$guiding -> id}}</td>
                                        <td>{{$guiding -> title}}</td>
                                        <td>{{$guiding -> location}}</td>
                                        <td>
                                            <a href="{{route('admin.guides.edit', $guiding->user->id)}}">
                                                {{$guiding -> user->full_name }}
                                            </a>
                                        </td>
                                        <td>{{$guiding -> price}} €</td>
                                        <td>
                                            @if($guiding -> price_two_persons)
                                                {{$guiding -> price_two_persons}} €
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($guiding -> price_three_persons)
                                                {{$guiding -> price_three_persons}} €
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                @if($guiding->status == 1)
                                                    <a href="{{ route('admin.changeGuidingStatus', $guiding->id) }}" title="{{ __('admin.guidings.deactivate') }}" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></a>
                                                @else
                                                    <a href="{{ route('admin.changeGuidingStatus', $guiding->id) }}" title="{{ __('admin.guidings.activate') }}" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                                @endif
                                                <a href="{{ route('admin.guidings.edit', $guiding) }}" class="btn btn-sm btn-secondary"><i class="fa fa-pen"></i></a>
                                                <a href="{{ route('admin.guidings.show', $guiding) }}" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>

    <!-- Guiding images modal -->
    <div class="modal fade" id="guidingImagesModal" tabindex="-1" aria-labelledby="guidingImagesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="guidingImagesModalLabel">
                        <i class="fa fa-images me-2"></i> {{ __('admin.guidings.images_modal_title') ?? 'Guiding images' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.guidings.modal_close') }}"></button>
                </div>
                <div class="modal-body">
                    <div id="guidingImagesMain" class="text-center mb-3">
                        <img id="guidingImagesMainImg" src="" alt="" class="img-fluid rounded shadow-sm d-none">
                        <div id="guidingImagesEmpty" class="text-muted py-4">
                            {{ __('admin.guidings.no_images') ?? 'No images available for this guiding.' }}
                        </div>
                    </div>
                    <div id="guidingImagesThumbs" class="d-flex flex-wrap gap-2 justify-content-center"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Guiding text details modal -->
    <div class="modal fade" id="guidingDetailsModal" tabindex="-1" aria-labelledby="guidingDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xxl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="guidingDetailsModalLabel">
                        <i class="fa fa-search me-2"></i> {{ __('admin.guidings.modal_details_title') }}
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <label class="mb-0 small text-muted">{{ __('admin.guidings.modal_language') }}</label>
                        <select id="guidingDetailsLangSwitch" class="form-select form-select-sm" style="width: auto;">
                            <option value="">—</option>
                        </select>
                        <a id="guidingDetailsShowLink" href="#" class="btn btn-sm btn-outline-primary" target="_blank" title="{{ __('admin.guidings.btn_open_page') }}"><i class="fa fa-external-link-alt"></i></a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.guidings.modal_close') }}"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="guidingDetailsLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ __('admin.guidings.modal_loading') }}</span></div>
                        <p class="mt-2 text-muted mb-0">{{ __('admin.guidings.modal_loading_text') }}</p>
                    </div>
                    <div id="guidingDetailsContent" class="d-none">
                        <div id="guidingDetailsSections"></div>
                    </div>
                    <div id="guidingDetailsError" class="alert alert-danger d-none mb-0"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Set guiding source language modal -->
    <div class="modal fade" id="guidingLanguageModal" tabindex="-1" aria-labelledby="guidingLanguageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="guidingLanguageModalLabel"><i class="fa fa-globe me-2"></i> {{ __('admin.guidings.modal_source_language_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.guidings.modal_close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">{!! __('admin.guidings.modal_source_language_paragraph') !!}</p>
                    <input type="hidden" id="guidingLanguageGuidingId" value="">
                    <div class="mb-0">
                        <label for="guidingLanguageSelect" class="form-label">{{ __('admin.guidings.modal_source_language_label') }}</label>
                        <select id="guidingLanguageSelect" class="form-select">
                            <option value="de">{{ __('admin.guidings.lang_de') }}</option>
                            <option value="en">{{ __('admin.guidings.lang_en') }}</option>
                            <option value="es">{{ __('admin.guidings.lang_es') }}</option>
                            <option value="fr">{{ __('admin.guidings.lang_fr') }}</option>
                            <option value="it">{{ __('admin.guidings.lang_it') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.guidings.modal_cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="guidingLanguageSaveBtn"><i class="fa fa-check me-1"></i> {{ __('admin.guidings.modal_save') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm translation modal -->
    <div class="modal fade" id="guidingTranslateConfirmModal" tabindex="-1" aria-labelledby="guidingTranslateConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="guidingTranslateConfirmModalLabel"><i class="fa fa-language me-2"></i> {{ __('admin.guidings.modal_translate_confirm_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.guidings.modal_close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="guidingTranslateConfirmMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.guidings.modal_cancel') }}</button>
                    <button type="button" class="btn btn-info" id="guidingTranslateConfirmBtn"><i class="fa fa-language me-1"></i> {{ __('admin.guidings.btn_translate') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('js_after')
@php
    $adminGuidingsJs = [
        'source_language_prefix' => __('admin.guidings.js_source_language_prefix'),
        'guiding_prefix' => __('admin.guidings.js_guiding_prefix'),
        'guide_prefix' => __('admin.guidings.js_guide_prefix'),
        'save_failed' => __('admin.guidings.js_save_failed'),
        'translation_failed' => __('admin.guidings.js_translation_failed'),
        'translation_request_failed' => __('admin.guidings.js_translation_request_failed'),
        'translation_rate_limited' => __('admin.guidings.js_translation_rate_limited'),
        'translation_retry_after' => __('admin.guidings.js_translation_retry_after'),
        'translation_seconds' => __('admin.guidings.js_translation_seconds'),
        'translation_minutes' => __('admin.guidings.js_translation_minutes'),
        'modal_translate_confirm_title' => __('admin.guidings.modal_translate_confirm_title'),
        'modal_translate_confirm_message' => __('admin.guidings.modal_translate_confirm_message'),
        'details_load_failed' => __('admin.guidings.js_details_load_failed'),
        'btn_edit' => __('admin.guidings.btn_edit'),
        'modal_save' => __('admin.guidings.modal_save'),
    ];
    $guidingDetailsLabels = [
        'labels' => [
            'title' => __('newguidings.title'),
            'description' => __('newguidings.overall_summary'),
            'additional_information' => __('newguidings.other_boat_info'),
            'desc_course_of_action' => __('newguidings.course_of_action'),
            'desc_meeting_point' => __('newguidings.meeting_point'),
            'desc_starting_time' => __('newguidings.starting_time'),
            'desc_tour_unique' => __('newguidings.tour_highlights'),
            'requirements' => __('newguidings.requirements_taking_part'),
            'recommendations' => __('newguidings.recommended_preparation'),
            'other_information' => __('newguidings.other_information'),
        ],
        'fieldMeta' => [
            'title' => ['type' => 'string', 'maxlength' => 255],
            'additional_information' => ['type' => 'string', 'maxlength' => 255],
            'description' => ['type' => 'longText', 'rows' => 8],
            'desc_course_of_action' => ['type' => 'text', 'rows' => 4],
            'desc_meeting_point' => ['type' => 'text', 'rows' => 4],
            'desc_starting_time' => ['type' => 'text', 'rows' => 3],
            'desc_tour_unique' => ['type' => 'text', 'rows' => 4],
            'other_information' => ['type' => 'longText', 'rows' => 6],
            'requirements' => ['type' => 'longText', 'rows' => 6],
            'recommendations' => ['type' => 'longText', 'rows' => 6],
        ],
        'noTextDetails' => __('newguidings.no_text_details_for_language'),
        'steps' => [
            ['step' => 1, 'title' => __('admin.guidings.step', ['num' => 1]), 'fields' => ['title']],
            ['step' => 2, 'title' => __('admin.guidings.step', ['num' => 2]), 'fields' => ['additional_information']],
            ['step' => 4, 'title' => __('admin.guidings.step', ['num' => 4]), 'fields' => ['description', 'desc_course_of_action', 'desc_starting_time', 'desc_meeting_point', 'desc_tour_unique']],
            ['step' => 5, 'title' => __('admin.guidings.step', ['num' => 5]), 'fields' => ['other_information', 'requirements', 'recommendations']],
        ],
    ];
@endphp
<script>
    window.guidingDetailsTranslations = @json($guidingDetailsLabels);
    window.adminGuidingsJs = @json($adminGuidingsJs);
</script>
<script>
    $(function() {
        $('#guiding-datatable').DataTable({
            order: [[0, 'desc']]
        });

        var guidingLanguageModalEl = document.getElementById('guidingLanguageModal');
        if (guidingLanguageModalEl) {
            var guidingLanguageModal = new bootstrap.Modal(guidingLanguageModalEl);
            $(document).on('click', '.btn-guiding-language', function() {
                var id = $(this).data('guiding-id');
                var title = $(this).data('guiding-title');
                var currentLang = $(this).data('current-lang') || 'de';
                var t = window.adminGuidingsJs || {};
                $('#guidingLanguageModalLabel').text((t.source_language_prefix || 'Source language:') + ' ' + (title || (t.guiding_prefix || 'Guiding #') + id));
                $('#guidingLanguageGuidingId').val(id);
                $('#guidingLanguageSelect').val(currentLang);
                guidingLanguageModal.show();
            });
            $('#guidingLanguageSaveBtn').on('click', function() {
                var id = $('#guidingLanguageGuidingId').val();
                var language = $('#guidingLanguageSelect').val();
                if (!id || !language) return;
                var $btn = $(this);
                $btn.prop('disabled', true);
                $.ajax({
                    url: '{{ url("admin/guidings") }}/' + id + '/language',
                    method: 'POST',
                    data: { language: language, _token: '{{ csrf_token() }}' },
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .done(function(res) {
                        if (res && res.success) {
                            guidingLanguageModal.hide();
                            window.location.reload();
                        } else {
                            var t = window.adminGuidingsJs || {};
                            alert(res && res.message ? res.message : (t.save_failed || 'Save failed.'));
                        }
                    })
                    .fail(function(xhr) {
                        var t = window.adminGuidingsJs || {};
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.language ? xhr.responseJSON.errors.language[0] : (t.save_failed || 'Save failed.'));
                        alert(msg);
                    })
                    .always(function() { $btn.prop('disabled', false); });
            });
        }

        // Images modal
        var guidingImagesModalEl = document.getElementById('guidingImagesModal');
        if (guidingImagesModalEl) {
            var guidingImagesModal = new bootstrap.Modal(guidingImagesModalEl);

            $(document).on('click', '.guiding-images-trigger', function() {
                var $el = $(this);
                var raw = $el.attr('data-images') || '[]';
                var images = [];
                try {
                    images = JSON.parse(raw);
                } catch (e) {
                    images = [];
                }
                if (!Array.isArray(images)) {
                    images = [];
                }

                var title = $el.data('guiding-title') || '';
                var location = $el.data('guiding-location') || '';
                var id = $el.data('guiding-id') || '';
                var headerTitle = title || ('Guiding #' + id);
                if (location) {
                    headerTitle += ' – ' + location;
                }
                $('#guidingImagesModalLabel').text(headerTitle);

                var $mainImg = $('#guidingImagesMainImg');
                var $empty = $('#guidingImagesEmpty');
                var $thumbs = $('#guidingImagesThumbs').empty();

                if (!images.length) {
                    $mainImg.addClass('d-none').attr('src', '');
                    $empty.removeClass('d-none');
                } else {
                    $empty.addClass('d-none');
                    $mainImg.removeClass('d-none').attr('src', images[0]);

                    images.forEach(function(src, idx) {
                        if (!src) return;
                        var thumb = $('<img>', {
                            src: src,
                            alt: 'Image ' + (idx + 1),
                            class: 'img-thumbnail guiding-images-thumb',
                            css: {
                                width: '72px',
                                height: '72px',
                                objectFit: 'cover',
                                cursor: 'pointer'
                            },
                            'data-src': src
                        });
                        $thumbs.append(thumb);
                    });
                }

                guidingImagesModal.show();
            });

            $(document).on('click', '.guiding-images-thumb', function() {
                var src = $(this).attr('data-src');
                if (src) {
                    $('#guidingImagesMainImg').attr('src', src);
                }
            });
        }

        var guidingTranslateConfirmModalEl = document.getElementById('guidingTranslateConfirmModal');
        if (guidingTranslateConfirmModalEl) {
            var guidingTranslateConfirmModal = new bootstrap.Modal(guidingTranslateConfirmModalEl);
            var pendingTranslateUrl = null;
            var pendingTranslateBtn = null;

            $(document).on('click', '.btn-guiding-translate', function() {
                var $btn = $(this);
                var url = $btn.data('translate-url');
                if (!url) return;
                var missing = ($btn.data('missing') || '').split(',').filter(Boolean);
                var title = $btn.data('guiding-title') || ('#' + $btn.closest('tr').find('td:first').text());
                var langsDisplay = missing.length ? missing.map(function(l) { return l.toUpperCase(); }).join(', ') : '';
                var t = window.adminGuidingsJs || {};
                var msgTemplate = t.modal_translate_confirm_message || 'Create missing translations for this guiding? Target languages: %s.';
                $('#guidingTranslateConfirmModalLabel').text((t.modal_translate_confirm_title || 'Confirm translation') + ' – ' + (title || ''));
                $('#guidingTranslateConfirmMessage').text(msgTemplate.replace('%s', langsDisplay));
                pendingTranslateUrl = url;
                pendingTranslateBtn = $btn;
                guidingTranslateConfirmModal.show();
            });

            $('#guidingTranslateConfirmBtn').on('click', function() {
                if (!pendingTranslateUrl || !pendingTranslateBtn) return;
                var $btn = pendingTranslateBtn;
                var url = pendingTranslateUrl;
                pendingTranslateUrl = null;
                pendingTranslateBtn = null;
                guidingTranslateConfirmModal.hide();
                $btn.prop('disabled', true);
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .done(function(res) {
                        if (res && res.success !== false) {
                            window.location.reload();
                        } else {
                            var t = window.adminGuidingsJs || {};
                            alert(res && res.message ? res.message : (t.translation_failed || 'Translation completed with some failures.'));
                            window.location.reload();
                        }
                    })
                    .fail(function(xhr) {
                        var t = window.adminGuidingsJs || {};
                        var msg = t.translation_request_failed || 'Translation request failed.';
                        if (xhr.status === 429) {
                            msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : (t.translation_rate_limited || 'Too many translation requests. Please try again later.');
                            if (xhr.responseJSON && xhr.responseJSON.retry_after) {
                                var sec = xhr.responseJSON.retry_after;
                                msg += ' ' + (t.translation_retry_after || 'You can try again in about') + ' ' + (sec <= 60 ? (sec + ' ' + (t.translation_seconds || 'seconds')) : (Math.ceil(sec / 60) + ' ' + (t.translation_minutes || 'minutes'))) + '.';
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        alert(msg);
                        $btn.prop('disabled', false);
                    });
            });
        }

        var guidingDetailsModal = document.getElementById('guidingDetailsModal');
        if (guidingDetailsModal) {
            var bsModal = new bootstrap.Modal(guidingDetailsModal);
            var currentDetails = null;
            var currentGuidingId = null;
            var showBaseUrl = '{{ url("admin/guidings") }}';
            var labels = (window.guidingDetailsTranslations && window.guidingDetailsTranslations.labels) || {};
            var stepsConfig = (window.guidingDetailsTranslations && window.guidingDetailsTranslations.steps) || [];
            var fieldMeta = (window.guidingDetailsTranslations && window.guidingDetailsTranslations.fieldMeta) || {};

            $(document).on('click', '.btn-guiding-details', function() {
                var id = $(this).data('guiding-id');
                var title = $(this).data('guiding-title') || ('Guiding #' + id);
                var location = $(this).data('guiding-location') || '';
                var guideName = $(this).data('guiding-guide-name') || '';
                var t = window.adminGuidingsJs || {};
                var modalTitle = (t.guiding_prefix || 'Guiding #') + id + ' - ' + title + (location ? ' | ' + location : '') + (guideName ? ' | ' + (t.guide_prefix || 'Guide:') + ' ' + guideName : '');
                $('#guidingDetailsModalLabel').text(modalTitle);
                $('#guidingDetailsShowLink').attr('href', showBaseUrl + '/' + id);
                $('#guidingDetailsContent').addClass('d-none');
                $('#guidingDetailsError').addClass('d-none');
                $('#guidingDetailsLoading').removeClass('d-none');
                $('#guidingDetailsLangSwitch').empty().append('<option value="">—</option>');
                bsModal.show();

                $.get('{{ url("admin/guidings") }}/' + id + '/details')
                    .done(function(res) {
                        if (res && res.error) {
                            $('#guidingDetailsLoading').addClass('d-none');
                            $('#guidingDetailsError').text(res.message || res.error || (t.details_load_failed || 'Could not load details.')).removeClass('d-none');
                            return;
                        }
                        currentDetails = res;
                        currentGuidingId = id;
                        $('#guidingDetailsLoading').addClass('d-none');
                        if (!res.available_languages || res.available_languages.length === 0) {
                            res.available_languages = [res.main_language || 'de'];
                        }
                        res.available_languages.forEach(function(lang) {
                            var label = lang.toUpperCase();
                            if (lang === 'de') label = 'Deutsch';
                            if (lang === 'en') label = 'English';
                            $('#guidingDetailsLangSwitch').append('<option value="' + lang + '">' + label + '</option>');
                        });
                        var initialLang = res.main_language || res.available_languages[0];
                        $('#guidingDetailsLangSwitch').val(initialLang);
                        renderGuidingDetailsContent(initialLang);
                        $('#guidingDetailsContent').removeClass('d-none');
                    })
                    .fail(function(xhr) {
                        $('#guidingDetailsLoading').addClass('d-none');
                        var t = window.adminGuidingsJs || {};
                        var errMsg = t.details_load_failed || 'Could not load details.';
                        try {
                            if (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) {
                                errMsg = xhr.responseJSON.message || xhr.responseJSON.error;
                            }
                        } catch (e) {}
                        $('#guidingDetailsError').text(errMsg).removeClass('d-none');
                    });
            });

            $('#guidingDetailsLangSwitch').on('change', function() {
                var lang = $(this).val();
                if (lang && currentDetails) renderGuidingDetailsContent(lang);
            });

            function getDataForLang(lang) {
                if (!currentDetails) return {};
                if (lang === currentDetails.main_language) return currentDetails.main || {};
                return currentDetails.translations && currentDetails.translations[lang] ? currentDetails.translations[lang] : (currentDetails.main || {});
            }

            function renderGuidingDetailsContent(lang) {
                var data = getDataForLang(lang);
                var steps = stepsConfig.length ? stepsConfig : [
                    { step: 1, title: 'Step 1', fields: ['title'] },
                    { step: 2, title: 'Step 2', fields: ['additional_information'] },
                    { step: 4, title: 'Step 4', fields: ['description', 'desc_course_of_action', 'desc_starting_time', 'desc_meeting_point', 'desc_tour_unique'] },
                    { step: 5, title: 'Step 5', fields: ['other_information', 'requirements', 'recommendations'] }
                ];
                var html = '';
                var editTitle = (window.adminGuidingsJs && window.adminGuidingsJs.btn_edit) || 'Edit';
                var saveTitle = (window.adminGuidingsJs && window.adminGuidingsJs.modal_save) || 'Save';

                function renderInput(key, val) {
                    var meta = fieldMeta[key] || { type: 'text', rows: 3 };
                    var label = labels[key] || key;
                    var strVal = (val === undefined || val === null) ? '' : (typeof val === 'object' ? JSON.stringify(val, null, 2) : String(val));
                    var editBtn = '<button type="button" class="btn btn-sm btn-outline-secondary btn-detail-edit ms-1" title="' + escapeAttr(editTitle) + '" data-field="' + escapeAttr(key) + '"><i class="fa fa-pen"></i></button><button type="button" class="btn btn-sm btn-success btn-detail-save d-none ms-1" title="' + escapeAttr(saveTitle) + '" data-field="' + escapeAttr(key) + '"><i class="fa fa-check"></i></button>';
                    var labelRow = '<div class="d-flex align-items-center flex-wrap gap-1 mb-1"><label class="form-label small text-muted mb-0">' + escapeHtml(label) + '</label>' + editBtn + '</div>';
                    var wrapStart = '<div class="mb-3 guiding-detail-field-row" data-field="' + escapeAttr(key) + '">' + labelRow;
                    var wrapEnd = '</div>';
                    if (meta.type === 'string' && meta.maxlength) {
                        return wrapStart + '<input type="text" class="form-control form-control-sm guiding-detail-input" readonly maxlength="' + meta.maxlength + '" value="' + escapeAttr(strVal) + '">' + wrapEnd;
                    }
                    var rows = meta.rows || 4;
                    return wrapStart + '<textarea class="form-control form-control-sm guiding-detail-value guiding-detail-input" readonly rows="' + rows + '" style="resize:vertical">' + escapeHtml(strVal) + '</textarea>' + wrapEnd;
                }

                function renderScalar(key, val) {
                    if (val === undefined || val === null) return renderInput(key, '');
                    if (typeof val === 'object' && !Array.isArray(val)) val = JSON.stringify(val);
                    if (Array.isArray(val)) val = val.join(', ');
                    return renderInput(key, String(val));
                }

                function renderList(key, arr) {
                    if (!arr || typeof arr !== 'object') return '';
                    var sectionLabel = labels[key] || key;
                    var html = '<div class="mb-4"><label class="form-label fw-bold mb-2">' + escapeHtml(sectionLabel) + '</label>';
                    if (!Array.isArray(arr)) {
                        html += '<textarea class="form-control form-control-sm guiding-detail-value" readonly rows="4" style="resize:vertical">' + escapeHtml(JSON.stringify(arr, null, 2)) + '</textarea></div>';
                        return html;
                    }
                    arr.forEach(function(item, idx) {
                        var itemLabel = '';
                        var itemValue = '';
                        var itemId = (item && typeof item === 'object' && item.id !== undefined) ? String(item.id) : '';
                        if (item && typeof item === 'object') {
                            itemLabel = (item.name !== undefined && item.name !== null) ? String(item.name) : (item.value !== undefined ? String(item.value).substring(0, 50) + (String(item.value).length > 50 ? '…' : '') : '');
                            itemValue = (item.value !== undefined && item.value !== null) ? String(item.value) : '';
                        } else {
                            itemValue = String(item);
                        }
                        var rows = itemValue.length > 120 ? 4 : 2;
                        var editBtn = '<button type="button" class="btn btn-sm btn-outline-secondary btn-detail-edit ms-1" title="' + escapeAttr(editTitle) + '" data-field="' + escapeAttr(key) + '" data-list-index="' + idx + '" data-list-id="' + escapeAttr(itemId) + '"><i class="fa fa-pen"></i></button><button type="button" class="btn btn-sm btn-success btn-detail-save d-none ms-1" title="' + escapeAttr(saveTitle) + '" data-field="' + escapeAttr(key) + '" data-list-index="' + idx + '" data-list-id="' + escapeAttr(itemId) + '"><i class="fa fa-check"></i></button>';
                        html += '<div class="mb-3 guiding-detail-field-row" data-field="' + escapeAttr(key) + '" data-list-index="' + idx + '" data-list-id="' + escapeAttr(itemId) + '"><div class="d-flex align-items-center flex-wrap gap-1 mb-1"><label class="form-label small text-muted mb-0">' + escapeHtml(itemLabel) + '</label>' + editBtn + '</div><textarea class="form-control form-control-sm guiding-detail-value guiding-detail-input" readonly rows="' + rows + '" style="resize:vertical">' + escapeHtml(itemValue) + '</textarea></div>';
                    });
                    html += '</div>';
                    return html;
                }

                function escapeAttr(text) {
                    var div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML.replace(/"/g, '&quot;');
                }

                steps.forEach(function(s) {
                    var sectionHtml = '';
                    s.fields.forEach(function(key) {
                        if (['requirements', 'recommendations', 'other_information'].indexOf(key) >= 0) {
                            sectionHtml += renderList(key, data[key]);
                        } else {
                            sectionHtml += renderScalar(key, data[key]);
                        }
                    });
                    html += '<div class="guiding-detail-step mb-4"><h6 class="text-primary border-bottom pb-2 mb-3">' + escapeHtml(s.title) + '</h6>' + (sectionHtml || '<p class="text-muted mb-0">—</p>') + '</div>';
                });

                var noDetails = (window.guidingDetailsTranslations && window.guidingDetailsTranslations.noTextDetails) || 'No text details for this language.';
                $('#guidingDetailsSections').html(html || '<p class="text-muted">' + escapeHtml(noDetails) + '</p>');
            }

            function escapeHtml(text) {
                var div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            $(document).on('click', '#guidingDetailsModal .btn-detail-edit', function() {
                var $row = $(this).closest('.guiding-detail-field-row');
                $row.find('.guiding-detail-input').prop('readonly', false);
                $(this).addClass('d-none');
                $row.find('.btn-detail-save').removeClass('d-none');
            });

            $(document).on('click', '#guidingDetailsModal .btn-detail-save', function() {
                var $btn = $(this);
                var $row = $btn.closest('.guiding-detail-field-row');
                var field = $row.data('field');
                var listIndex = $row.data('list-index');
                var listId = $row.data('list-id');
                var value = $row.find('.guiding-detail-input').val();
                var lang = $('#guidingDetailsLangSwitch').val();
                if (!currentGuidingId || !lang) return;
                var url = showBaseUrl + '/' + currentGuidingId + '/details-field';
                var payload = { field: field, value: value, language: lang, _token: '{{ csrf_token() }}' };
                if (listIndex !== undefined && listIndex !== null && String(listIndex) !== '') payload.list_index = listIndex;
                if (listId !== undefined && listId !== null && String(listId) !== '') payload.list_id = listId;
                $btn.prop('disabled', true);
                $.post(url, payload)
                    .done(function() {
                        if (!currentDetails) return;
                        if (lang === currentDetails.main_language) {
                            if (['requirements', 'recommendations', 'other_information'].indexOf(field) >= 0 && (listIndex !== undefined && listIndex !== null)) {
                                var arr = currentDetails.main[field];
                                if (Array.isArray(arr) && arr[listIndex]) arr[listIndex].value = value;
                            } else {
                                currentDetails.main[field] = value;
                            }
                        } else {
                            if (!currentDetails.translations[lang]) currentDetails.translations[lang] = {};
                            if (['requirements', 'recommendations', 'other_information'].indexOf(field) >= 0 && (listIndex !== undefined && listIndex !== null)) {
                                var arr = currentDetails.translations[lang][field];
                                if (!Array.isArray(arr)) arr = [];
                                if (arr[listIndex]) arr[listIndex].value = value; else arr[listIndex] = { value: value };
                                currentDetails.translations[lang][field] = arr;
                            } else {
                                currentDetails.translations[lang][field] = value;
                            }
                        }
                        renderGuidingDetailsContent(lang);
                    })
                    .fail(function(xhr) {
                        var t = window.adminGuidingsJs || {};
                        var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : (t.save_failed || 'Save failed.');
                        alert(msg);
                    })
                    .always(function() { $btn.prop('disabled', false); });
            });
        }
    });
</script>
@endsection
