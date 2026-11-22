<?php

namespace Tests\Unit\Guiding;

use App\Http\Requests\StoreNewGuidingRequest;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class StoreNewGuidingRequestTest extends TestCase
{
    public function test_prepare_for_validation_does_not_copy_desc_meeting_point_into_meeting_point(): void
    {
        $longMeetingPointDescription = trim(str_repeat('Treffpunkt ist der Hauptparkplatz am See. ', 20));
        $this->assertGreaterThan(255, strlen($longMeetingPointDescription));

        $request = StoreNewGuidingRequest::create('/newguiding/save-draft-sync', 'POST', [
            'is_draft' => 1,
            'desc_course_of_action' => 'Some course of action.',
            'desc_meeting_point' => $longMeetingPointDescription,
            'desc_tour_unique' => 'Some tour unique text.',
        ]);

        $method = new ReflectionMethod($request, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        // meeting_point is a separate, short (varchar 255) DB column for the physical
        // address — it must never be silently populated from the long free-text
        // desc_meeting_point paragraph, or saving the draft overflows the column.
        $this->assertNotSame($longMeetingPointDescription, $request->input('meeting_point'));
        $this->assertEmpty($request->input('meeting_point'));

        // desc_meeting_point itself is untouched by the fix.
        $this->assertSame($longMeetingPointDescription, $request->input('desc_meeting_point'));
    }

    public function test_draft_with_zero_duration_hours_saves(): void
    {
        // A draft saved without hours stores duration 0, and the edit form used to prefill "0".
        // Every step save then failed "must be at least 1" from step 1, before the duration step.
        $errors = $this->validationErrors([
            'is_draft' => 1,
            'is_update' => 1,
            'title' => 'Zander tour',
            'duration' => 'half_day',
            'duration_hours' => '0',
        ]);

        $this->assertArrayNotHasKey('duration_hours', $errors);
    }

    public function test_draft_with_empty_duration_days_saves(): void
    {
        $errors = $this->validationErrors([
            'is_draft' => 1,
            'duration' => 'multi_day',
            'duration_days' => '',
        ]);

        $this->assertArrayNotHasKey('duration_days', $errors);
    }

    public function test_publish_still_requires_duration_hours(): void
    {
        $errors = $this->validationErrors([
            'is_draft' => 0,
            'duration' => 'full_day',
            'duration_hours' => '0',
        ]);

        $this->assertArrayHasKey('duration_hours', $errors);
    }

    public function test_draft_still_rejects_out_of_range_hours(): void
    {
        $errors = $this->validationErrors([
            'is_draft' => 1,
            'duration' => 'full_day',
            'duration_hours' => '30',
        ]);

        $this->assertArrayHasKey('duration_hours', $errors);
    }

    private function validationErrors(array $input): array
    {
        $request = StoreNewGuidingRequest::create('/newguiding/save-draft-sync', 'POST', $input);

        $method = new ReflectionMethod($request, 'prepareForValidation');
        $method->invoke($request);

        return Validator::make($request->all(), $request->rules())->errors()->toArray();
    }
}
