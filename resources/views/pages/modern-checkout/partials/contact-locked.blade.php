{{-- Reschedule: contact details come from the original request and are shown masked only.
     data-clarity-mask keeps them out of session recordings. --}}
@php($contact = $checkout->lockedContact())
<section class="tc-card tc-contact tc-contact--locked" aria-labelledby="tc-contact-title">
    <div class="tc-contact__head">
        <h2 class="tc-card__title" id="tc-contact-title">{{ __('checkout.tour.contact_title') }}</h2>
        <a href="{{ route('additional.contact') }}" class="tc-link tc-contact__login">{{ __('checkout.reschedule.contact_change') }}</a>
    </div>
    <p class="tc-muted tc-contact__privacy">{{ __('checkout.reschedule.contact_note') }}</p>

    <dl class="tc-contact__summary" data-clarity-mask="True">
        @foreach ([
            'name' => __('checkout.reschedule.name'),
            'email' => __('checkout.tour.email'),
            'phone' => __('checkout.tour.phone'),
        ] as $field => $label)
            @if (($contact[$field] ?? '') !== '')
                <div class="tc-contact__item">
                    <dt class="tc-field__label">{{ $label }}</dt>
                    <dd class="tc-contact__value">{{ $contact[$field] }}</dd>
                </div>
            @endif
        @endforeach
    </dl>
</section>
