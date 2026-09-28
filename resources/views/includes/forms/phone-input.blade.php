<div class="phone-input-container {{ isset($modernCheckout) && $modernCheckout ? 'modern-checkout-phone' : '' }}">
    @if(isset($showLabel) && $showLabel)
        <label for="{{ $id ?? 'phone' }}" class="{{ isset($modernCheckout) && $modernCheckout ? 'block text-sm font-medium text-slate-700 mb-1' : '' }}">{{ __($labelText ?? 'forms.pNumber') }}<span style="color: #e8604c !important; font-size: 12px;">*</span></label>
    @endif
    
    <div class="d-flex {{ isset($modernCheckout) && $modernCheckout ? 'gap-2' : '' }}">
        <select class="form-control rounded w-25 me-2 {{ $errorClass ?? '' }} {{ isset($modernCheckout) && $modernCheckout ? 'form-input' : '' }}" 
                name="{{ $countryCodeName ?? 'countryCode' }}" 
                id="{{ $countryCodeId ?? 'countryCode' }}"
                style="max-width: 120px;" 
                @if(isset($wireModelCountryCode) && $wireModelCountryCode) wire:model="{{ $wireModelCountryCode }}" @endif
                @if(isset($alpineModelCountryCode) && $alpineModelCountryCode) x-model="{{ $alpineModelCountryCode }}" @endif
                @if(isset($alpineDisabled) && $alpineDisabled) :disabled="{{ $alpineDisabled }}" @endif
                {{ isset($required) && $required ? 'required' : '' }}>
            @foreach(config('phone_country_codes', []) as $dialCode => $countryLabel)
                <option value="{{ $dialCode }}" {{ ($selectedCountryCode ?? '+49') === $dialCode ? 'selected' : '' }}>{{ $dialCode }} ({{ $countryLabel }})</option>
            @endforeach
        </select>
        
        <input type="number" 
               class="form-control rounded {{ $errorClass ?? '' }} {{ isset($modernCheckout) && $modernCheckout ? 'form-input' : '' }}" 
               placeholder="{{ __($placeholder ?? 'forms.pNumber') }}" 
               name="{{ $name ?? 'phone' }}" 
               id="{{ $id ?? 'phone' }}"
               value="{{ $phoneValue ?? '' }}"
               @if(isset($wireModel) && $wireModel) wire:model="{{ $wireModel }}" @endif
               @if(isset($alpineModel) && $alpineModel) x-model="{{ $alpineModel }}" @endif
               @if(isset($alpineDisabled) && $alpineDisabled) :disabled="{{ $alpineDisabled }}" @endif
               {{ isset($required) && $required ? 'required' : '' }}>
    </div>
    
    @if(isset($showHelpText) && $showHelpText)
        <small class="text-muted">{{ __($helpText ?? 'forms.pNumberMsg') }}</small>
    @endif
</div> 