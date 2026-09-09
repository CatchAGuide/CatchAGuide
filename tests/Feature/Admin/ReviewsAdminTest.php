<?php

namespace Tests\Feature\Admin;

use App\Models\Employee;
use App\Models\Review;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ReviewsAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // APP_URL is often "cag.local" without a scheme; url() then prefixes
        // paths as /cag.local/... and PHPUnit requests 404. Force a root URL.
        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    private function actingAsEmployee(): Employee
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');

        return $employee;
    }

    public function test_guest_is_redirected_from_reviews_index(): void
    {
        $response = $this->get('/admin/reviews');

        $response->assertRedirect();
    }

    public function test_admin_can_view_reviews_index(): void
    {
        $this->actingAsEmployee();

        $response = $this->get('/admin/reviews');

        $response->assertOk();
        $response->assertSee(__('admin.reviews.page_title'), false);
        $response->assertSee('reviews-datatable', false);
    }

    public function test_admin_can_filter_reviews_by_is_automatic(): void
    {
        $this->actingAsEmployee();

        $hasAutomatic = Review::query()->where('is_automatic', true)->exists();
        $hasGuest = Review::query()
            ->where(function ($q) {
                $q->where('is_automatic', false)->orWhereNull('is_automatic');
            })
            ->exists();

        if (! $hasAutomatic || ! $hasGuest) {
            $this->markTestSkipped('Need at least one automatic and one guest review in the database.');
        }

        $autoResponse = $this->get('/admin/reviews?is_automatic=1');
        $autoResponse->assertOk();
        $autoResponse->assertViewHas('reviews', function ($reviews) {
            return $reviews->isNotEmpty()
                && $reviews->every(fn (Review $review) => (bool) $review->is_automatic);
        });

        $guestResponse = $this->get('/admin/reviews?is_automatic=0');
        $guestResponse->assertOk();
        $guestResponse->assertViewHas('reviews', function ($reviews) {
            return $reviews->isNotEmpty()
                && $reviews->every(fn (Review $review) => ! (bool) $review->is_automatic);
        });
    }

    public function test_admin_can_fetch_review_detail_json(): void
    {
        $this->actingAsEmployee();

        $review = Review::query()->first();
        if (! $review) {
            $this->markTestSkipped('No reviews in test database.');
        }

        $response = $this->getJson('/admin/reviews/' . $review->id);

        $response->assertOk();
        $response->assertJsonPath('id', $review->id);
        $response->assertJsonStructure([
            'id',
            'is_automatic',
            'type_label',
            'comment',
            'scores' => ['overall', 'guide', 'region_water', 'grandtotal'],
            'guest' => ['name', 'email', 'is_guest_booking'],
            'guide' => ['id', 'name', 'email'],
            'guiding' => ['id', 'title', 'location', 'admin_url'],
            'booking' => ['id', 'admin_url', 'book_date'],
            'created_at',
            'created_at_human',
            'updated_at',
        ]);
    }
}
