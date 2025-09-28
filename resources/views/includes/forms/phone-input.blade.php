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
            <option value="+49" {{ ($selectedCountryCode ?? '+49') === '+49' ? 'selected' : '' }}>+49 (Germany)</option>
            <option value="+1" {{ ($selectedCountryCode ?? '+49') === '+1' ? 'selected' : '' }}>+1 (USA/Canada)</option>
            <option value="+44" {{ ($selectedCountryCode ?? '+49') === '+44' ? 'selected' : '' }}>+44 (UK)</option>
            <option value="+33" {{ ($selectedCountryCode ?? '+49') === '+33' ? 'selected' : '' }}>+33 (France)</option>
            <option value="+39" {{ ($selectedCountryCode ?? '+49') === '+39' ? 'selected' : '' }}>+39 (Italy)</option>
            <option value="+34" {{ ($selectedCountryCode ?? '+49') === '+34' ? 'selected' : '' }}>+34 (Spain)</option>
            <option value="+81" {{ ($selectedCountryCode ?? '+49') === '+81' ? 'selected' : '' }}>+81 (Japan)</option>
            <option value="+86" {{ ($selectedCountryCode ?? '+49') === '+86' ? 'selected' : '' }}>+86 (China)</option>
            <option value="+91" {{ ($selectedCountryCode ?? '+49') === '+91' ? 'selected' : '' }}>+91 (India)</option>
            <option value="+61" {{ ($selectedCountryCode ?? '+49') === '+61' ? 'selected' : '' }}>+61 (Australia)</option>
            <option value="+353" {{ ($selectedCountryCode ?? '+49') === '+353' ? 'selected' : '' }}>+353 (Ireland)</option>
            <option value="+31" {{ ($selectedCountryCode ?? '+49') === '+31' ? 'selected' : '' }}>+31 (Netherlands)</option>
            <option value="+46" {{ ($selectedCountryCode ?? '+49') === '+46' ? 'selected' : '' }}>+46 (Sweden)</option>
            <option value="+47" {{ ($selectedCountryCode ?? '+49') === '+47' ? 'selected' : '' }}>+47 (Norway)</option>
            <option value="+45" {{ ($selectedCountryCode ?? '+49') === '+45' ? 'selected' : '' }}>+45 (Denmark)</option>
            <option value="+358" {{ ($selectedCountryCode ?? '+49') === '+358' ? 'selected' : '' }}>+358 (Finland)</option>
            <option value="+32" {{ ($selectedCountryCode ?? '+49') === '+32' ? 'selected' : '' }}>+32 (Belgium)</option>
            <option value="+41" {{ ($selectedCountryCode ?? '+49') === '+41' ? 'selected' : '' }}>+41 (Switzerland)</option>
            <option value="+43" {{ ($selectedCountryCode ?? '+49') === '+43' ? 'selected' : '' }}>+43 (Austria)</option>
            <option value="+48" {{ ($selectedCountryCode ?? '+49') === '+48' ? 'selected' : '' }}>+48 (Poland)</option>
            <option value="+351" {{ ($selectedCountryCode ?? '+49') === '+351' ? 'selected' : '' }}>+351 (Portugal)</option>
            <option value="+30" {{ ($selectedCountryCode ?? '+49') === '+30' ? 'selected' : '' }}>+30 (Greece)</option>
            <option value="+420" {{ ($selectedCountryCode ?? '+49') === '+420' ? 'selected' : '' }}>+420 (Czech Republic)</option>
            <option value="+36" {{ ($selectedCountryCode ?? '+49') === '+36' ? 'selected' : '' }}>+36 (Hungary)</option>
            <option value="+7" {{ ($selectedCountryCode ?? '+49') === '+7' ? 'selected' : '' }}>+7 (Russia)</option>
            <option value="+380" {{ ($selectedCountryCode ?? '+49') === '+380' ? 'selected' : '' }}>+380 (Ukraine)</option>
            <option value="+90" {{ ($selectedCountryCode ?? '+49') === '+90' ? 'selected' : '' }}>+90 (Turkey)</option>
            <option value="+20" {{ ($selectedCountryCode ?? '+49') === '+20' ? 'selected' : '' }}>+20 (Egypt)</option>
            <option value="+27" {{ ($selectedCountryCode ?? '+49') === '+27' ? 'selected' : '' }}>+27 (South Africa)</option>
            <option value="+55" {{ ($selectedCountryCode ?? '+49') === '+55' ? 'selected' : '' }}>+55 (Brazil)</option>
            <option value="+52" {{ ($selectedCountryCode ?? '+49') === '+52' ? 'selected' : '' }}>+52 (Mexico)</option>
            <option value="+54" {{ ($selectedCountryCode ?? '+49') === '+54' ? 'selected' : '' }}>+54 (Argentina)</option>
            <option value="+56" {{ ($selectedCountryCode ?? '+49') === '+56' ? 'selected' : '' }}>+56 (Chile)</option>
            <option value="+57" {{ ($selectedCountryCode ?? '+49') === '+57' ? 'selected' : '' }}>+57 (Colombia)</option>
            <option value="+51" {{ ($selectedCountryCode ?? '+49') === '+51' ? 'selected' : '' }}>+51 (Peru)</option>
            <option value="+64" {{ ($selectedCountryCode ?? '+49') === '+64' ? 'selected' : '' }}>+64 (New Zealand)</option>
            <option value="+65" {{ ($selectedCountryCode ?? '+49') === '+65' ? 'selected' : '' }}>+65 (Singapore)</option>
            <option value="+60" {{ ($selectedCountryCode ?? '+49') === '+60' ? 'selected' : '' }}>+60 (Malaysia)</option>
            <option value="+66" {{ ($selectedCountryCode ?? '+49') === '+66' ? 'selected' : '' }}>+66 (Thailand)</option>
            <option value="+62" {{ ($selectedCountryCode ?? '+49') === '+62' ? 'selected' : '' }}>+62 (Indonesia)</option>
            <option value="+63" {{ ($selectedCountryCode ?? '+49') === '+63' ? 'selected' : '' }}>+63 (Philippines)</option>
            <option value="+84" {{ ($selectedCountryCode ?? '+49') === '+84' ? 'selected' : '' }}>+84 (Vietnam)</option>
            <option value="+82" {{ ($selectedCountryCode ?? '+49') === '+82' ? 'selected' : '' }}>+82 (South Korea)</option>
            <option value="+972" {{ ($selectedCountryCode ?? '+49') === '+972' ? 'selected' : '' }}>+972 (Israel)</option>
            <option value="+971" {{ ($selectedCountryCode ?? '+49') === '+971' ? 'selected' : '' }}>+971 (UAE)</option>
            <option value="+966" {{ ($selectedCountryCode ?? '+49') === '+966' ? 'selected' : '' }}>+966 (Saudi Arabia)</option>
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