<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\EmailLogsController;
use App\Models\EmailLog;
use App\Models\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Admin › Email Logs renders the whole list client-side, so it is capped and leaves out the stored HTML.
 */
class EmailLogsIndexTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    public function test_index_lists_newest_logs_without_the_stored_body(): void
    {
        $employee = Employee::query()->first();
        if (! $employee) {
            $this->markTestSkipped('No employee available for admin auth.');
        }

        $log = EmailLog::query()->create([
            'email' => 'newest-'.uniqid().'@example.com',
            'language' => 'en',
            'subject' => 'Newest log',
            'type' => 'test',
            'status' => 1,
            'target' => 'test',
            'additional_info' => json_encode(['body_html' => '<p>Body</p>']),
        ]);
        $log->forceFill(['created_at' => now()->addMinute()])->save();

        $response = $this->actingAs($employee, 'employees')->get(route('admin.email-logs.index'));

        $response->assertOk();
        $response->assertSee($log->email);

        $emailLogs = $response->viewData('emailLogs');
        $this->assertLessThanOrEqual(EmailLogsController::LIST_LIMIT, $emailLogs->count());
        $this->assertSame($log->id, $emailLogs->first()->id);
        $this->assertArrayNotHasKey('additional_info', $emailLogs->first()->getAttributes());
    }
}
