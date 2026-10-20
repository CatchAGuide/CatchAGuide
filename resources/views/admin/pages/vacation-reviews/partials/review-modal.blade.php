{{--
    Add-review modal for the camp & trip reviews page. Opened empty by the "Add review" button (pick a
    listing) or for one booking by an "Enter review" button, whose data-booking-* attributes fill it.
    Needs $listingOptions (manualListingOptions()), $modalBooking (bookingOpenForReview() or null) and
    $openModal; it reopens itself after a failed submit.
--}}
@php
    $scoreFields = [
        'overall_score' => __('admin.vacation_reviews.score_overall'),
        'accommodation_score' => __('admin.vacation_reviews.score_accommodation'),
        'fishing_score' => __('admin.vacation_reviews.score_fishing'),
        'value_score' => __('admin.vacation_reviews.score_value'),
    ];
    $typeLabels = ['camp' => __('admin.vacation_reviews.type_camp'), 'trip' => __('admin.vacation_reviews.type_trip')];
    $prefill = $modalBooking['booking'] ?? null;
    $defaultName = $prefill ? ($prefill->first_name ?: strtok((string) $prefill->name, ' ')) : '';
    $defaultLanguage = $prefill ? $prefill->customerLocale() : app()->getLocale();
    $bookingLabel = $prefill
        ? __('admin.vacation_reviews.create_for_booking', [
            'type' => $typeLabels[$modalBooking['type']],
            'id' => $prefill->id,
            'title' => $modalBooking['title'] ?? '—',
        ])
        : '';
    $hasErrors = $errors->any();
@endphp
<div class="modal fade" id="vacationReviewModal" tabindex="-1" aria-labelledby="vacationReviewModalLabel" aria-hidden="true"
     data-auto-open="{{ $openModal || $hasErrors ? '1' : '0' }}">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form method="POST" action="{{ route('admin.vacation-reviews.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="vacationReviewModalLabel">{{ __('admin.vacation_reviews.create_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.vacation_reviews.cancel') }}"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">{{ __('admin.vacation_reviews.create_subtitle') }}</p>

                <div data-review-booking @class(['d-none' => ! $prefill])>
                    <input type="hidden" name="booking_type" value="{{ $modalBooking['type'] ?? '' }}" @disabled(! $prefill)>
                    <input type="hidden" name="booking_id" value="{{ $prefill?->id }}" @disabled(! $prefill)>
                    <div class="alert alert-light border">
                        <span data-review-booking-label>{{ $bookingLabel }}</span>
                        <div class="small text-muted" data-review-booking-contact>{{ $prefill ? $prefill->name.' · '.$prefill->email : '' }}</div>
                    </div>
                    @error('booking_id')<div class="alert alert-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3" data-review-listing @class(['d-none' => (bool) $prefill])>
                    <label class="form-label" for="review-listing">{{ __('admin.vacation_reviews.field_listing') }}</label>
                    <select id="review-listing" name="listing" class="form-select @error('listing') is-invalid @enderror" @required(! $prefill) @disabled((bool) $prefill)>
                        <option value="">{{ __('admin.vacation_reviews.field_listing_placeholder') }}</option>
                        @foreach($typeLabels as $listingType => $groupLabel)
                            <optgroup label="{{ $groupLabel }}">
                                @foreach($listingOptions[$listingType] as $listing)
                                    @php $value = $listingType.':'.$listing->id; @endphp
                                    <option value="{{ $value }}" @selected(old('listing') === $value)>#{{ $listing->id }} · {{ $listing->title }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('listing')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label" for="review-guest-name">{{ __('admin.vacation_reviews.field_guest_name') }}</label>
                        <input id="review-guest-name" type="text" name="guest_name" maxlength="120" required
                               class="form-control @error('guest_name') is-invalid @enderror"
                               value="{{ old('guest_name', $defaultName) }}">
                        <div class="form-text">{{ __('admin.vacation_reviews.field_guest_name_hint') }}</div>
                        @error('guest_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="review-date">{{ __('admin.vacation_reviews.field_reviewed_at') }}</label>
                        <input id="review-date" type="date" name="reviewed_at" required max="{{ now()->toDateString() }}"
                               class="form-control @error('reviewed_at') is-invalid @enderror"
                               value="{{ old('reviewed_at', now()->toDateString()) }}">
                        @error('reviewed_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="review-language">{{ __('admin.vacation_reviews.field_language') }}</label>
                        <select id="review-language" name="language" class="form-select @error('language') is-invalid @enderror">
                            @foreach(['de' => __('admin.vacation_reviews.language_de'), 'en' => __('admin.vacation_reviews.language_en')] as $code => $label)
                                <option value="{{ $code }}" @selected(old('language', $defaultLanguage) === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    @foreach($scoreFields as $field => $label)
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="review-{{ $field }}">{{ $label }}</label>
                            <select id="review-{{ $field }}" name="{{ $field }}" class="form-select @error($field) is-invalid @enderror" required>
                                <option value="">–</option>
                                @for($score = 10; $score >= 1; $score--)
                                    <option value="{{ $score }}" @selected((string) old($field) === (string) $score)>{{ $score }} / 10</option>
                                @endfor
                            </select>
                            @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                </div>

                <div class="mb-0">
                    <label class="form-label" for="review-comment">{{ __('admin.vacation_reviews.th_comment') }}</label>
                    <textarea id="review-comment" name="comment" rows="5" maxlength="5000" required
                              class="form-control @error('comment') is-invalid @enderror">{{ old('comment') }}</textarea>
                    <div class="form-text">{{ __('admin.vacation_reviews.field_comment_hint') }}</div>
                    @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('admin.vacation_reviews.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('admin.vacation_reviews.save') }}</button>
            </div>
        </form>
    </div>
</div>

@push('js_after')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('vacationReviewModal');
        if (!modal || typeof bootstrap === 'undefined') return;

        const form = modal.querySelector('form');
        const bookingBox = modal.querySelector('[data-review-booking]');
        const listingBox = modal.querySelector('[data-review-listing]');
        const listingSelect = listingBox.querySelector('select');
        const bookingInputs = bookingBox.querySelectorAll('input[type="hidden"]');
        const defaultLanguage = @js(app()->getLocale());

        // A click on "Add review" / "Enter review" starts a fresh form; the auto-open after a
        // failed submit has no trigger and keeps the server-rendered values and errors.
        modal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const data = trigger.dataset;
            const forBooking = !!data.bookingId;

            form.reset();
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            bookingBox.querySelectorAll('.alert-danger').forEach(el => el.remove());

            bookingBox.classList.toggle('d-none', !forBooking);
            listingBox.classList.toggle('d-none', forBooking);
            bookingInputs.forEach(input => { input.disabled = !forBooking; });
            listingSelect.disabled = forBooking;
            listingSelect.required = !forBooking;
            listingSelect.value = '';

            form.elements.booking_type.value = data.bookingType || '';
            form.elements.booking_id.value = data.bookingId || '';
            bookingBox.querySelector('[data-review-booking-label]').textContent = data.bookingLabel || '';
            bookingBox.querySelector('[data-review-booking-contact]').textContent = data.bookingContact || '';
            form.elements.guest_name.value = data.guestName || '';
            form.elements.language.value = data.language || defaultLanguage;
            form.elements.reviewed_at.value = form.elements.reviewed_at.max;
            form.elements.comment.value = '';
            ['overall_score', 'accommodation_score', 'fishing_score', 'value_score'].forEach(name => { form.elements[name].value = ''; });
        });

        if (modal.dataset.autoOpen === '1') {
            (bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal)).show();
        }
    });
</script>
@endpush
