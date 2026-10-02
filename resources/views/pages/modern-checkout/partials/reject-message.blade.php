<section class="tc-card tc-message" x-ref="field_message" aria-labelledby="tc-message-title">
    <h2 class="tc-card__title" id="tc-message-title">{{ __('checkout.reject.message_title') }}</h2>
    <p class="tc-muted tc-message__note" id="tc-message-note">{{ __('checkout.reject.message_note', ['min' => $reject->minMessage()]) }}</p>

    <label class="visually-hidden" for="tc-message">{{ __('checkout.reject.message_title') }}</label>
    <textarea
        id="tc-message"
        class="tc-input tc-textarea"
        rows="5"
        maxlength="{{ $reject->maxMessage() }}"
        placeholder="{{ __('checkout.reject.message_placeholder') }}"
        x-model="message"
        @input="clearError('message')"
        :class="{ 'is-invalid': errors.message }"
        :aria-invalid="Boolean(errors.message).toString()"
        aria-describedby="tc-message-note tc-message-error"
        required
    ></textarea>

    <div class="tc-message__meta">
        <p class="tc-field__error" id="tc-message-error" x-show="errors.message" x-text="errors.message" x-cloak></p>
        <span class="tc-counter tc-mono" :class="{ 'is-ok': messageOk }" x-text="charsLabel" aria-live="polite"></span>
    </div>
</section>
