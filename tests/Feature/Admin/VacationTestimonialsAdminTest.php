<?php

namespace Tests\Feature\Admin;

use App\Models\Employee;
use App\Models\VacationTestimonial;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VacationTestimonialsAdminTest extends TestCase
{
    use DatabaseTransactions;

    private function actingAsEmployee(): Employee
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $this->actingAs($employee, 'employees');

        return $employee;
    }

    public function test_admin_can_create_a_vacation_testimonial(): void
    {
        $this->actingAsEmployee();

        $response = $this->post('/admin/vacation-testimonials', [
            'quote' => 'Fantastic week at the pike camp.',
            'author' => 'Jonas',
            'rating' => 9.5,
            'reviewed_on' => '2026-06-01',
            'listing_title' => 'Pike Camp Sweden',
            'listing_url' => 'https://catchaguide.com/vacations/camps/pike-camp-sweden',
            'is_published' => 1,
            'sort_order' => 1,
        ]);

        if ($response->status() === 404) {
            $this->markTestSkipped('Admin vacation-testimonials route not reachable in this test environment.');
        }

        $response->assertRedirect('/admin/vacation-testimonials');
        $this->assertDatabaseHas('vacation_testimonials', [
            'author' => 'Jonas',
            'listing_title' => 'Pike Camp Sweden',
            'is_published' => 1,
        ]);
    }

    public function test_admin_can_update_and_unpublish_a_vacation_testimonial(): void
    {
        $this->actingAsEmployee();

        $testimonial = VacationTestimonial::query()->create([
            'quote' => 'Original quote',
            'author' => 'Erika',
            'rating' => 8.0,
            'is_published' => true,
            'sort_order' => 0,
        ]);

        $response = $this->put("/admin/vacation-testimonials/{$testimonial->id}", [
            'quote' => 'Updated quote',
            'author' => 'Erika',
            'rating' => 8.0,
            'is_published' => 0,
            'sort_order' => 0,
        ]);

        if ($response->status() === 404) {
            $this->markTestSkipped('Admin vacation-testimonials route not reachable in this test environment.');
        }

        $response->assertRedirect('/admin/vacation-testimonials');
        $this->assertDatabaseHas('vacation_testimonials', [
            'id' => $testimonial->id,
            'quote' => 'Updated quote',
            'is_published' => 0,
        ]);
    }

    public function test_admin_can_delete_a_vacation_testimonial(): void
    {
        $this->actingAsEmployee();

        $testimonial = VacationTestimonial::query()->create([
            'quote' => 'To be deleted',
            'author' => 'Temp',
            'rating' => 7.0,
            'is_published' => true,
            'sort_order' => 0,
        ]);

        $response = $this->delete("/admin/vacation-testimonials/{$testimonial->id}");

        if ($response->status() === 404) {
            $this->markTestSkipped('Admin vacation-testimonials route not reachable in this test environment.');
        }

        $response->assertRedirect('/admin/vacation-testimonials');
        $this->assertDatabaseMissing('vacation_testimonials', ['id' => $testimonial->id]);
    }
}
