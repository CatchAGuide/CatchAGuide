@extends('admin.layouts.app')

@section('title', __('admin.reviews.page_title'))

@section('content')
@php
    $hasActiveFilters = collect($filters)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
@endphp
<div class="side-app">
    <div class="main-container container-fluid">
        <div class="reviews-hero" data-parallax-hero>
            <div class="reviews-hero__layer reviews-hero__layer--a" data-parallax-layer="0.15"></div>
            <div class="reviews-hero__layer reviews-hero__layer--b" data-parallax-layer="0.08"></div>
            <div class="reviews-hero__content">
                <div class="page-header mb-0 border-0 bg-transparent">
                    <h1 class="page-title">{{ __('admin.reviews.page_title') }}</h1>
                    <div>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('admin.reviews.breadcrumb') }}</li>
                        </ol>
                    </div>
                </div>
                <p class="reviews-hero__subtitle mb-0">{{ __('admin.reviews.subtitle') }}</p>
            </div>
        </div>

        <div class="reviews-stats">
            <div class="reviews-stat">
                <div class="reviews-stat__value">{{ number_format($stats['total']) }}</div>
                <div class="reviews-stat__label">{{ __('admin.reviews.stat_total') }}</div>
            </div>
            <div class="reviews-stat reviews-stat--guest">
                <div class="reviews-stat__value">{{ number_format($stats['guest']) }}</div>
                <div class="reviews-stat__label">{{ __('admin.reviews.stat_guest') }}</div>
            </div>
            <div class="reviews-stat reviews-stat--auto">
                <div class="reviews-stat__value">{{ number_format($stats['automatic']) }}</div>
                <div class="reviews-stat__label">{{ __('admin.reviews.stat_automatic') }}</div>
            </div>
            <div class="reviews-stat reviews-stat--score">
                <div class="reviews-stat__value">{{ $stats['avg_score'] }}</div>
                <div class="reviews-stat__label">{{ __('admin.reviews.stat_avg_score') }}</div>
            </div>
            @if($hasActiveFilters)
                <div class="reviews-stat reviews-stat--filtered">
                    <div class="reviews-stat__value">{{ number_format($stats['filtered']) }}</div>
                    <div class="reviews-stat__label">{{ __('admin.reviews.stat_filtered') }}</div>
                </div>
            @endif
        </div>

        <div class="row row-sm">
            <div class="col-12">
                <div class="card shadow-sm border-0 reviews-card">
                    <div class="card-header reviews-card__header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h3 class="card-title mb-0">{{ __('admin.reviews.table_title') }}</h3>
                            <div class="reviews-type-chips" role="group" aria-label="{{ __('admin.reviews.filter_type') }}">
                                <a href="{{ route('admin.reviews.index', array_merge(request()->except('is_automatic'), [])) }}"
                                   class="reviews-chip {{ $filters['is_automatic'] === null ? 'is-active' : '' }}">
                                    {{ __('admin.reviews.type_all') }}
                                </a>
                                <a href="{{ route('admin.reviews.index', array_merge(request()->except('page'), ['is_automatic' => '0'])) }}"
                                   class="reviews-chip reviews-chip--guest {{ $filters['is_automatic'] === '0' ? 'is-active' : '' }}">
                                    {{ __('admin.reviews.type_guest') }}
                                </a>
                                <a href="{{ route('admin.reviews.index', array_merge(request()->except('page'), ['is_automatic' => '1'])) }}"
                                   class="reviews-chip reviews-chip--auto {{ $filters['is_automatic'] === '1' ? 'is-active' : '' }}">
                                    {{ __('admin.reviews.type_automatic') }}
                                </a>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" type="button"
                                data-bs-toggle="collapse" data-bs-target="#reviewsFilters"
                                aria-expanded="{{ $hasActiveFilters ? 'true' : 'false' }}"
                                aria-controls="reviewsFilters">
                            <i class="fa fa-filter me-1"></i>{{ __('admin.reviews.filters_toggle') }}
                            @if($hasActiveFilters)
                                <span class="badge bg-primary ms-1">{{ __('admin.reviews.filters_active') }}</span>
                            @endif
                        </button>
                    </div>

                    <div id="reviewsFilters" class="collapse {{ $hasActiveFilters ? 'show' : '' }}">
                        <div class="card-body border-bottom reviews-filters">
                            <form method="get" action="{{ route('admin.reviews.index') }}" class="row g-3 align-items-end">
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label reviews-filters__label" for="filter_is_automatic">{{ __('admin.reviews.filter_type') }}</label>
                                    <select name="is_automatic" id="filter_is_automatic" class="form-select form-select-sm">
                                        <option value="">{{ __('admin.reviews.type_all') }}</option>
                                        <option value="0" @selected($filters['is_automatic'] === '0')>{{ __('admin.reviews.type_guest') }}</option>
                                        <option value="1" @selected($filters['is_automatic'] === '1')>{{ __('admin.reviews.type_automatic') }}</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label reviews-filters__label" for="filter_guide_id">{{ __('admin.reviews.filter_guide') }}</label>
                                    <select name="guide_id" id="filter_guide_id" class="form-select form-select-sm">
                                        <option value="">{{ __('admin.reviews.filter_all') }}</option>
                                        @foreach($guides as $id => $name)
                                            <option value="{{ $id }}" @selected($filters['guide_id'] === (int) $id)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label reviews-filters__label" for="filter_score_min">{{ __('admin.reviews.filter_score_min') }}</label>
                                    <input type="number" step="0.1" min="0" max="10" name="score_min" id="filter_score_min"
                                           class="form-control form-control-sm" value="{{ $filters['score_min'] ?? '' }}"
                                           placeholder="0">
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label reviews-filters__label" for="filter_score_max">{{ __('admin.reviews.filter_score_max') }}</label>
                                    <input type="number" step="0.1" min="0" max="10" name="score_max" id="filter_score_max"
                                           class="form-control form-control-sm" value="{{ $filters['score_max'] ?? '' }}"
                                           placeholder="10">
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label reviews-filters__label" for="filter_date_from">{{ __('admin.reviews.filter_date_from') }}</label>
                                    <input type="date" name="date_from" id="filter_date_from"
                                           class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}">
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label reviews-filters__label" for="filter_date_to">{{ __('admin.reviews.filter_date_to') }}</label>
                                    <input type="date" name="date_to" id="filter_date_to"
                                           class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}">
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label reviews-filters__label" for="filter_has_comment">{{ __('admin.reviews.filter_has_comment') }}</label>
                                    <select name="has_comment" id="filter_has_comment" class="form-select form-select-sm">
                                        <option value="">{{ __('admin.reviews.filter_all') }}</option>
                                        <option value="1" @selected($filters['has_comment'] === '1')>{{ __('admin.reviews.filter_yes') }}</option>
                                        <option value="0" @selected($filters['has_comment'] === '0')>{{ __('admin.reviews.filter_no') }}</option>
                                    </select>
                                </div>
                                <div class="col-12 col-lg-4 d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fa fa-search me-1"></i>{{ __('admin.reviews.filter_apply') }}
                                    </button>
                                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-outline-secondary">
                                        {{ __('admin.reviews.filter_reset') }}
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 admin-listing-datatable" id="reviews-datatable">
                                <thead>
                                    <tr>
                                        <th>{{ __('admin.reviews.th_id') }}</th>
                                        <th>{{ __('admin.reviews.th_date') }}</th>
                                        <th>{{ __('admin.reviews.th_type') }}</th>
                                        <th>{{ __('admin.reviews.th_guest') }}</th>
                                        <th>{{ __('admin.reviews.th_guide') }}</th>
                                        <th>{{ __('admin.reviews.th_guiding') }}</th>
                                        <th>{{ __('admin.reviews.th_score') }}</th>
                                        <th>{{ __('admin.reviews.th_comment') }}</th>
                                        <th class="text-end">{{ __('admin.reviews.th_actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reviews as $review)
                                        @php
                                            $booking = $review->booking;
                                            $guestUser = $booking?->user;
                                            $guestName = $guestUser
                                                ? trim(($guestUser->firstname ?? '') . ' ' . ($guestUser->lastname ?? ''))
                                                : '';
                                            $guide = $review->reviewedGuide;
                                            $guideName = $guide
                                                ? trim(($guide->firstname ?? '') . ' ' . ($guide->lastname ?? ''))
                                                : '';
                                            $guiding = $review->guiding;
                                            $isAuto = (bool) $review->is_automatic;
                                            $score = round((float) $review->grandtotal_score, 1);
                                        @endphp
                                        <tr class="reviews-row {{ $isAuto ? 'reviews-row--auto' : 'reviews-row--guest' }}"
                                            data-type="{{ $isAuto ? 'automatic' : 'guest' }}">
                                            <td class="fw-semibold" data-order="{{ $review->id }}">#{{ $review->id }}</td>
                                            <td data-order="{{ optional($review->created_at)->timestamp }}">
                                                <span class="reviews-date">{{ optional($review->created_at)->format('M j, Y') }}</span>
                                                <span class="reviews-date__time d-block">{{ optional($review->created_at)->format('g:i A') }}</span>
                                            </td>
                                            <td data-order="{{ $isAuto ? 1 : 0 }}">
                                                @if($isAuto)
                                                    <span class="reviews-badge reviews-badge--auto">{{ __('admin.reviews.type_automatic') }}</span>
                                                @else
                                                    <span class="reviews-badge reviews-badge--guest">{{ __('admin.reviews.type_guest') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="reviews-person" title="{{ e($guestName ?: '—') }}">{{ $guestName !== '' ? \Illuminate\Support\Str::limit($guestName, 28) : '—' }}</span>
                                                @if($booking?->is_guest)
                                                    <span class="reviews-person__meta d-block">{{ __('admin.reviews.guest_booking') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="reviews-person" title="{{ e($guideName ?: '—') }}">{{ $guideName !== '' ? \Illuminate\Support\Str::limit($guideName, 28) : '—' }}</span>
                                            </td>
                                            <td>
                                                @if($guiding)
                                                    <span class="reviews-guiding" title="{{ e($guiding->title) }}">{{ \Illuminate\Support\Str::limit($guiding->title, 36) }}</span>
                                                    @if($guiding->location)
                                                        <span class="reviews-guiding__loc d-block">{{ \Illuminate\Support\Str::limit($guiding->location, 32) }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td data-order="{{ $score }}">
                                                <div class="reviews-score" title="{{ __('admin.reviews.score_breakdown', [
                                                    'overall' => $review->overall_score,
                                                    'guide' => $review->guide_score,
                                                    'region' => $review->region_water_score,
                                                ]) }}">
                                                    <span class="reviews-score__value">{{ number_format($score, 1) }}</span>
                                                    <span class="reviews-score__bar" aria-hidden="true">
                                                        <span class="reviews-score__fill" style="width: {{ min(100, max(0, $score * 10)) }}%"></span>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                @if(filled($review->comment))
                                                    <span class="reviews-comment" title="{{ e($review->comment) }}">{{ \Illuminate\Support\Str::limit($review->comment, 48) }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button"
                                                            class="btn btn-outline-info js-review-detail"
                                                            data-url="{{ route('admin.reviews.show', $review) }}"
                                                            title="{{ __('admin.reviews.btn_view') }}">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                    @if($booking)
                                                        <a href="{{ route('admin.bookings.show', $booking) }}"
                                                           class="btn btn-outline-secondary"
                                                           title="{{ __('admin.reviews.btn_booking') }}">
                                                            <i class="fa fa-calendar"></i>
                                                        </a>
                                                    @endif
                                                    @if($guiding)
                                                        <a href="{{ route('admin.guidings.edit', $guiding) }}"
                                                           class="btn btn-outline-secondary"
                                                           title="{{ __('admin.reviews.btn_guiding') }}">
                                                            <i class="fa fa-fish"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5 text-muted">
                                                <i class="fa fa-star fa-2x mb-2 d-block opacity-50"></i>
                                                {{ __('admin.reviews.empty') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Detail modal --}}
<div class="modal fade" id="reviewDetailModal" tabindex="-1" aria-labelledby="reviewDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="reviewDetailModalLabel">{{ __('admin.reviews.modal_title') }}</h5>
                    <div class="small text-muted" id="reviewDetailMeta"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.reviews.modal_close') }}"></button>
            </div>
            <div class="modal-body">
                <div id="reviewDetailLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted mb-0">{{ __('admin.reviews.modal_loading') }}</p>
                </div>
                <div id="reviewDetailError" class="alert alert-danger d-none"></div>
                <div id="reviewDetailBody" class="d-none">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span id="reviewDetailTypeBadge" class="reviews-badge"></span>
                        <span id="reviewDetailScorePill" class="reviews-score-pill"></span>
                    </div>

                    <div class="accordion" id="reviewDetailAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingScores">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseScores" aria-expanded="true" aria-controls="collapseScores">
                                    {{ __('admin.reviews.accordion_scores') }}
                                </button>
                            </h2>
                            <div id="collapseScores" class="accordion-collapse collapse show" aria-labelledby="headingScores" data-bs-parent="#reviewDetailAccordion">
                                <div class="accordion-body">
                                    <div class="reviews-detail-scores" id="reviewDetailScores"></div>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingComment">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseComment" aria-expanded="false" aria-controls="collapseComment">
                                    {{ __('admin.reviews.accordion_comment') }}
                                </button>
                            </h2>
                            <div id="collapseComment" class="accordion-collapse collapse" aria-labelledby="headingComment" data-bs-parent="#reviewDetailAccordion">
                                <div class="accordion-body">
                                    <p id="reviewDetailComment" class="mb-0 reviews-detail-comment"></p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingPeople">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePeople" aria-expanded="false" aria-controls="collapsePeople">
                                    {{ __('admin.reviews.accordion_people') }}
                                </button>
                            </h2>
                            <div id="collapsePeople" class="accordion-collapse collapse" aria-labelledby="headingPeople" data-bs-parent="#reviewDetailAccordion">
                                <div class="accordion-body">
                                    <dl class="row mb-0 reviews-detail-dl">
                                        <dt class="col-sm-3">{{ __('admin.reviews.th_guest') }}</dt>
                                        <dd class="col-sm-9" id="reviewDetailGuest"></dd>
                                        <dt class="col-sm-3">{{ __('admin.reviews.th_guide') }}</dt>
                                        <dd class="col-sm-9" id="reviewDetailGuide"></dd>
                                        <dt class="col-sm-3">{{ __('admin.reviews.th_guiding') }}</dt>
                                        <dd class="col-sm-9" id="reviewDetailGuiding"></dd>
                                        <dt class="col-sm-3">{{ __('admin.reviews.th_booking') }}</dt>
                                        <dd class="col-sm-9" id="reviewDetailBooking"></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.reviews.modal_close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js_after')
<script>
    $(function () {
        var $table = $('#reviews-datatable');
        if ($table.length && $.fn.DataTable && $table.find('tbody tr').length && !$table.find('td[colspan]').length) {
            $table.DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/de-DE.json"
                },
                pagingType: "full_numbers",
                responsive: true,
                autoWidth: false,
                order: [[0, 'desc']],
                stripeClasses: [],
                columnDefs: [
                    { targets: 0, type: 'num' },
                    { targets: 1, type: 'num' },
                    { targets: 6, type: 'num' },
                    { targets: -1, orderable: false }
                ]
            });
        }

        // Soft parallax on hero layers
        var $hero = $('[data-parallax-hero]');
        if ($hero.length) {
            var ticking = false;
            $(window).on('scroll', function () {
                if (ticking) return;
                ticking = true;
                window.requestAnimationFrame(function () {
                    var scrollY = window.scrollY || window.pageYOffset;
                    $hero.find('[data-parallax-layer]').each(function () {
                        var factor = parseFloat(this.getAttribute('data-parallax-layer')) || 0.1;
                        this.style.transform = 'translate3d(0,' + (scrollY * factor) + 'px,0)';
                    });
                    ticking = false;
                });
            });
        }

        var $modal = $('#reviewDetailModal');
        var $loading = $('#reviewDetailLoading');
        var $error = $('#reviewDetailError');
        var $body = $('#reviewDetailBody');
        var labels = {
            overall: @json(__('admin.reviews.score_overall')),
            guide: @json(__('admin.reviews.score_guide')),
            region: @json(__('admin.reviews.score_region')),
            grandtotal: @json(__('admin.reviews.score_grandtotal')),
            noComment: @json(__('admin.reviews.no_comment')),
            guestBooking: @json(__('admin.reviews.guest_booking')),
            openBooking: @json(__('admin.reviews.btn_booking')),
            openGuiding: @json(__('admin.reviews.btn_guiding')),
            loadError: @json(__('admin.reviews.modal_error'))
        };

        function resetModal() {
            $loading.removeClass('d-none');
            $error.addClass('d-none').text('');
            $body.addClass('d-none');
            $('#reviewDetailMeta').text('');
        }

        function scoreRow(label, value) {
            var pct = Math.min(100, Math.max(0, Number(value) * 10));
            return '<div class="reviews-detail-score">' +
                '<div class="reviews-detail-score__head"><span>' + label + '</span><strong>' + Number(value).toFixed(1) + '</strong></div>' +
                '<div class="reviews-score__bar"><span class="reviews-score__fill" style="width:' + pct + '%"></span></div>' +
                '</div>';
        }

        function esc(str) {
            return $('<div>').text(str == null ? '' : String(str)).html();
        }

        $(document).on('click', '.js-review-detail', function () {
            var url = $(this).data('url');
            resetModal();
            var modal = bootstrap.Modal.getOrCreateInstance($modal[0]);
            modal.show();

            $.getJSON(url)
                .done(function (data) {
                    $loading.addClass('d-none');
                    $body.removeClass('d-none');

                    $('#reviewDetailModalLabel').text(@json(__('admin.reviews.modal_title')) + ' #' + data.id);
                    $('#reviewDetailMeta').text(data.created_at_human || '');

                    var $badge = $('#reviewDetailTypeBadge');
                    $badge.text(data.type_label)
                        .removeClass('reviews-badge--auto reviews-badge--guest')
                        .addClass(data.is_automatic ? 'reviews-badge--auto' : 'reviews-badge--guest');

                    $('#reviewDetailScorePill').text(labels.grandtotal + ': ' + Number(data.scores.grandtotal).toFixed(1));

                    $('#reviewDetailScores').html(
                        scoreRow(labels.overall, data.scores.overall) +
                        scoreRow(labels.guide, data.scores.guide) +
                        scoreRow(labels.region, data.scores.region_water) +
                        scoreRow(labels.grandtotal, data.scores.grandtotal)
                    );

                    var comment = (data.comment || '').trim();
                    $('#reviewDetailComment').text(comment !== '' ? comment : labels.noComment);

                    var guestHtml = esc(data.guest.name);
                    if (data.guest.email) {
                        guestHtml += ' <span class="text-muted">&lt;' + esc(data.guest.email) + '&gt;</span>';
                    }
                    if (data.guest.is_guest_booking) {
                        guestHtml += ' <span class="badge bg-secondary">' + esc(labels.guestBooking) + '</span>';
                    }
                    $('#reviewDetailGuest').html(guestHtml);

                    var guideHtml = esc(data.guide.name);
                    if (data.guide.email) {
                        guideHtml += ' <span class="text-muted">&lt;' + esc(data.guide.email) + '&gt;</span>';
                    }
                    $('#reviewDetailGuide').html(guideHtml);

                    var guidingHtml = '—';
                    if (data.guiding && data.guiding.id) {
                        guidingHtml = esc(data.guiding.title || ('#' + data.guiding.id));
                        if (data.guiding.location) {
                            guidingHtml += ' <span class="text-muted">· ' + esc(data.guiding.location) + '</span>';
                        }
                        if (data.guiding.admin_url) {
                            guidingHtml += ' <a href="' + esc(data.guiding.admin_url) + '" class="ms-1">' + esc(labels.openGuiding) + '</a>';
                        }
                    }
                    $('#reviewDetailGuiding').html(guidingHtml);

                    var bookingHtml = '—';
                    if (data.booking && data.booking.id) {
                        bookingHtml = '#' + data.booking.id;
                        if (data.booking.book_date) {
                            bookingHtml += ' <span class="text-muted">· ' + esc(data.booking.book_date) + '</span>';
                        }
                        if (data.booking.admin_url) {
                            bookingHtml += ' <a href="' + esc(data.booking.admin_url) + '" class="ms-1">' + esc(labels.openBooking) + '</a>';
                        }
                    }
                    $('#reviewDetailBooking').html(bookingHtml);

                    // Expand comment accordion when there is a comment
                    if (comment !== '') {
                        var commentCollapse = bootstrap.Collapse.getOrCreateInstance(document.getElementById('collapseComment'), { toggle: false });
                        commentCollapse.show();
                    }
                })
                .fail(function () {
                    $loading.addClass('d-none');
                    $error.removeClass('d-none').text(labels.loadError);
                });
        });

        $modal.on('hidden.bs.modal', resetModal);
    });
</script>
@endpush
