<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class LoadingComponentsTest extends TestCase
{
    public function test_overlay_renders_default_message_and_spinner(): void
    {
        App::setLocale('en');

        $html = View::make('components.loading.overlay')->render();

        $this->assertStringContainsString('page-loading-overlay', $html);
        $this->assertStringContainsString('cag-spinner', $html);
        $this->assertStringContainsString('Casting your line', $html);
    }

    public function test_overlay_renders_custom_message_instead_of_default(): void
    {
        App::setLocale('en');

        $html = View::make('components.loading.overlay', ['message' => 'Custom wait message'])->render();

        $this->assertStringContainsString('Custom wait message', $html);
        $this->assertStringNotContainsString('Casting your line', $html);
    }

    public function test_overlay_forwards_extra_attributes(): void
    {
        $html = Blade::render('<x-loading.overlay id="page-loading-overlay" hidden />');

        $this->assertStringContainsString('id="page-loading-overlay"', $html);
        $this->assertStringContainsString('hidden', $html);
    }

    public function test_inline_loader_renders_and_merges_extra_classes(): void
    {
        $html = Blade::render('<x-loading.inline class="offers-gallery-modal__spinner" :label="__(\'forms.loading\')" />');

        $this->assertStringContainsString('loading-inline', $html);
        $this->assertStringContainsString('offers-gallery-modal__spinner', $html);
        $this->assertStringContainsString('visually-hidden', $html);
    }

    public function test_spinner_is_the_shared_primitive_behind_both_variants(): void
    {
        $overlay = View::make('components.loading.overlay')->render();
        $inline = Blade::render('<x-loading.inline />');

        $this->assertStringContainsString('cag-spinner', $overlay);
        $this->assertStringContainsString('cag-spinner', $inline);
    }

    public function test_spinner_forwards_extra_attributes(): void
    {
        $html = Blade::render('<x-loading.spinner class="extra" data-foo="bar" />');

        $this->assertStringContainsString('cag-spinner', $html);
        $this->assertStringContainsString('class="cag-spinner extra"', $html);
        $this->assertStringContainsString('data-foo="bar"', $html);
    }

    public function test_loading_lang_files_have_matching_keys(): void
    {
        $en = array_keys(require resource_path('lang/en/loading.php'));
        $de = array_keys(require resource_path('lang/de/loading.php'));

        sort($en);
        sort($de);

        $this->assertSame($en, $de);
    }
}
