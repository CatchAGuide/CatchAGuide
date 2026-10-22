@use('App\Domain\Checkout\ViewModels\VoucherCheckoutViewModel')
{{-- Recipient(s), "from" and personal message; all three are printed on the standard voucher. --}}
<section class="tc-card tc-voucher" aria-labelledby="tc-voucher-title">
    <h2 class="tc-card__title" id="tc-voucher-title">{{ __('checkout.voucher.details_title') }}</h2>
    <p class="tc-muted">{{ __('checkout.voucher.details_text') }}</p>

    <div class="tc-voucher__grid">
        <div class="tc-field" x-ref="field_recipients">
            <label class="tc-field__label" for="tc-recipients">{{ __('checkout.voucher.recipients') }}</label>
            <input
                id="tc-recipients"
                class="tc-input"
                type="text"
                name="recipient_names"
                maxlength="{{ VoucherCheckoutViewModel::RECIPIENTS_MAX }}"
                placeholder="{{ __('checkout.voucher.recipients_placeholder') }}"
                x-model="voucher.recipients"
                @input="clearError('recipients')"
                :class="{ 'is-invalid': errors.recipients }"
                :aria-invalid="Boolean(errors.recipients).toString()"
                aria-describedby="tc-recipients-error"
                required
            >
            <p class="tc-field__error" id="tc-recipients-error" x-show="errors.recipients" x-text="errors.recipients" x-cloak></p>
        </div>

        <div class="tc-field" x-ref="field_sender">
            <label class="tc-field__label" for="tc-sender">{{ __('checkout.voucher.sender') }}</label>
            <input
                id="tc-sender"
                class="tc-input"
                type="text"
                name="sender_name"
                maxlength="{{ VoucherCheckoutViewModel::SENDER_MAX }}"
                placeholder="{{ __('checkout.voucher.sender_placeholder') }}"
                x-model="voucher.sender"
                @input="clearError('sender')"
                :class="{ 'is-invalid': errors.sender }"
                aria-describedby="tc-sender-error"
            >
            <p class="tc-field__error" id="tc-sender-error" x-show="errors.sender" x-text="errors.sender" x-cloak></p>
        </div>

        <div class="tc-field" x-ref="field_message">
            <label class="tc-field__label" for="tc-message">{{ __('checkout.voucher.message') }}</label>
            <textarea
                id="tc-message"
                class="tc-input tc-textarea"
                name="personal_message"
                rows="3"
                maxlength="{{ VoucherCheckoutViewModel::MESSAGE_MAX }}"
                placeholder="{{ __('checkout.voucher.message_placeholder') }}"
                x-model="voucher.message"
                @input="clearError('message')"
                @keydown.enter="if ((voucher.message.match(/\n/g) || []).length >= {{ VoucherCheckoutViewModel::MESSAGE_MAX_LINES - 1 }}) $event.preventDefault()"
                :class="{ 'is-invalid': errors.message }"
                aria-describedby="tc-message-error tc-message-count"
            ></textarea>
            <div class="tc-message__meta">
                <p class="tc-field__error" id="tc-message-error" x-show="errors.message" x-text="errors.message" x-cloak></p>
                <span class="tc-counter" id="tc-message-count" x-text="messageCounter"></span>
            </div>
        </div>
    </div>
</section>
