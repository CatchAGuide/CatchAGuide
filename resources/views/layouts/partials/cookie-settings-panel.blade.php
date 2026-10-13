{{--
    Cookie on/off switches, used on /cookie-settings (pages.law.cookie-settings) and in Profile › Settings.
    The behaviour lives in layouts/partials/cookie-consent (it binds every [data-cookie-settings-panel]), and the
    popup stays closed on any page that shows this panel.
    GA4 is listed under Necessary on purpose (business decision, see tag-manager-head).
--}}
@php
    $cookieCategories = [
        ['key' => 'necessary', 'consent' => null],
        ['key' => 'analytics', 'consent' => 'analytics'],
        ['key' => 'marketing', 'consent' => 'marketing'],
    ];
    $panelId = 'cookie-panel-'.($panelKey ?? 'page');
@endphp
<div class="cag-cookie-panel" data-cookie-settings-panel>
    <div class="cag-cookie__categories">
        @foreach ($cookieCategories as $category)
            @php($inputId = $panelId.'-'.$category['key'])
            <div class="cag-cookie__category">
                <div class="cag-cookie__category-head">
                    <label class="cag-cookie__category-name" for="{{ $inputId }}">@lang('cookie.'.$category['key'])</label>
                    @if ($category['consent'])
                        <input class="cag-cookie__switch" type="checkbox" role="switch" id="{{ $inputId }}" data-cookie-consent="{{ $category['consent'] }}">
                    @else
                        <span class="cag-cookie__always-on">@lang('cookie.always-on')</span>
                        <input class="cag-cookie__switch" type="checkbox" role="switch" id="{{ $inputId }}" checked disabled>
                    @endif
                </div>
                <p class="cag-cookie__category-text">@lang('cookie.'.$category['key'].'-msg')</p>
                <ul class="cag-cookie__tags">
                    @foreach (explode('|', __('cookie.'.$category['key'].'-tags')) as $tag)
                        <li>{{ $tag }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    <div class="cag-cookie__actions cag-cookie__actions--row">
        <button type="button" class="cag-cookie__btn cag-cookie__btn--outline" data-cookie-save>@lang('cookie.save-btn')</button>
        <button type="button" class="cag-cookie__btn cag-cookie__btn--primary" data-cookie-accept-all>@lang('cookie.accept-all-btn')</button>
    </div>
    <p class="cag-cookie-panel__saved" data-cookie-saved role="status" hidden>
        <i class="fas fa-check-circle" aria-hidden="true"></i> @lang('cookie.saved')
    </p>
</div>
