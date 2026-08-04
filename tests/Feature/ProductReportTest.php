<?php

namespace Tests\Feature;

use App\Mail\ProductReportAdminMail;
use App\Mail\ProductReportCustomerMail;
use App\Models\ProductReport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProductReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        if (!Schema::hasTable('product_reports')) {
            Schema::create('product_reports', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email');
                $table->string('phone')->nullable();
                $table->string('reason');
                $table->text('description');
                $table->string('reported_url');
                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('status')->default('open');
                $table->text('admin_comment')->nullable();
                $table->string('locale', 10)->nullable();
                $table->string('ip', 45)->nullable();
                $table->timestamps();
            });
        }

        ProductReport::query()->delete();

        if ($this->name() !== 'test_store_endpoint_is_throttled') {
            $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        }
    }

    public function test_notice_and_takedown_route_is_registered(): void
    {
        $this->assertTrue(Route::has('law.notice-and-takedown'));
        $this->assertTrue(Route::has('product-reports.store'));
    }

    public function test_notice_and_takedown_page_is_accessible(): void
    {
        $this->get('/notice-and-takedown')
            ->assertOk()
            ->assertSee(__('notice-takedown.heading'), false);
    }

    public function test_product_page_report_persists_and_sends_mails(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Jane Reporter',
            'email' => 'jane@example.com',
            'phone' => '+49123456789',
            'reason' => ProductReport::REASON_FRAUD,
            'description' => 'This listing appears to be a fraudulent offer with fake photos.',
            'reported_url' => 'https://example.com/guidings/1/test-tour',
            'source_type' => ProductReport::SOURCE_GUIDING,
            'source_id' => 1,
        ];

        $response = $this->postJson('/product-reports', $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('product_reports', [
            'email' => 'jane@example.com',
            'reason' => ProductReport::REASON_FRAUD,
            'source_type' => ProductReport::SOURCE_GUIDING,
            'source_id' => 1,
            'status' => ProductReport::STATUS_OPEN,
        ]);

        Mail::assertSent(ProductReportAdminMail::class);
        Mail::assertSent(ProductReportCustomerMail::class);
    }

    public function test_general_report_with_url_only_persists(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Alex Guest',
            'email' => 'alex@example.com',
            'phone' => '+49111222333',
            'reason' => ProductReport::REASON_COPYRIGHT,
            'description' => 'I believe this page uses my copyrighted photographs without permission.',
            'reported_url' => 'https://example.com/vacations/trips/some-trip',
        ];

        $this->from('/notice-and-takedown')
            ->post('/product-reports', $payload)
            ->assertRedirect('/notice-and-takedown');

        $this->assertDatabaseHas('product_reports', [
            'email' => 'alex@example.com',
            'reason' => ProductReport::REASON_COPYRIGHT,
            'reported_url' => 'https://example.com/vacations/trips/some-trip',
            'source_type' => null,
            'source_id' => null,
        ]);

        Mail::assertSent(ProductReportAdminMail::class);
        Mail::assertSent(ProductReportCustomerMail::class);
    }

    public function test_validation_rejects_missing_required_fields(): void
    {
        Mail::fake();

        $this->postJson('/product-reports', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone', 'reason', 'description', 'reported_url']);

        Mail::assertNothingSent();
        $this->assertSame(0, ProductReport::query()->count());
    }

    public function test_validation_rejects_invalid_reason(): void
    {
        Mail::fake();

        $this->postJson('/product-reports', [
            'name' => 'Sam',
            'email' => 'sam@example.com',
            'phone' => '+49999888777',
            'reason' => 'not-a-valid-reason',
            'description' => 'This description is long enough to pass the minimum length.',
            'reported_url' => 'https://example.com/page',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);

        Mail::assertNothingSent();
    }

    public function test_store_endpoint_is_throttled(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Throttle Tester',
            'email' => 'throttle@example.com',
            'phone' => '+49000000000',
            'reason' => ProductReport::REASON_OTHER,
            'description' => 'Repeated submissions should eventually be rate limited by throttle.',
            'reported_url' => 'https://example.com/report-me',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/product-reports', array_merge($payload, [
                'email' => "throttle{$i}@example.com",
            ]))->assertOk();
        }

        $this->postJson('/product-reports', array_merge($payload, [
            'email' => 'throttle6@example.com',
        ]))->assertStatus(429);
    }
}
