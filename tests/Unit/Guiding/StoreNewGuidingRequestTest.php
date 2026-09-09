<?php

namespace Tests\Unit\Guiding;

use App\Http\Requests\StoreNewGuidingRequest;
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
}
