{{-- Chosen alternative dates under the calendar (guide reject form). Tap to remove. --}}
<div class="tc-date-picks">
    <p class="tc-date-picks__count" x-text="countLabel"></p>
    <ul class="tc-date-picks__list" x-show="dates.length" x-cloak>
        <template x-for="iso in dates" :key="iso">
            <li>
                <button type="button" class="tc-date-pick" @click="selectDate(iso)" :aria-label="removeLabel(iso)">
                    <span x-text="shortLabel(iso)"></span>
                    @include('pages.modern-checkout.partials.icon', ['name' => 'x', 'size' => 12, 'stroke' => 2.2])
                </button>
            </li>
        </template>
    </ul>
</div>
{{-- errors.date is shown by the calendar partial itself. --}}
