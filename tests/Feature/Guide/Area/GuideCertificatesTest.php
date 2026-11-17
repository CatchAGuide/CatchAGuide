<?php

namespace Tests\Feature\Guide\Area;

use App\Enums\CertificateStatus;
use App\Enums\GuideStatus;
use App\Http\Livewire\Guide\Profile;
use App\Mail\Guide\GuideCertificateRejectedMail;
use App\Mail\Guide\GuideCertificateSubmittedAdminMail;
use App\Mail\Guide\GuideCertificateVerifiedMail;
use App\Models\AdminNotification;
use App\Models\Employee;
use App\Models\GuideCertificate;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class GuideCertificatesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost', 'guide_area.enabled' => true, 'media_storage.private_disk' => 'local']);
        URL::forceRootUrl('http://localhost');
        Storage::fake('local');
        Mail::fake();
    }

    private function guide(): User
    {
        $guide = User::factory()->create(['is_guide' => 1, 'guide_status' => GuideStatus::VERIFIED, 'language' => 'en']);
        $guide->forceFill(['guide_area_beta' => true])->save();

        return $guide->fresh(['information']);
    }

    private function upload(User $guide, string $type = 'boat_licence'): GuideCertificate
    {
        Livewire::actingAs($guide)
            ->test(Profile::class)
            ->call('openCertificateForm', $type)
            ->set('certificateFile', UploadedFile::fake()->create('licence.pdf', 200, 'application/pdf'))
            ->set('certificateValidUntil', now()->addYears(3)->toDateString())
            ->call('uploadCertificate')
            ->assertHasNoErrors()
            ->assertSet('certificateFormOpen', false);

        return GuideCertificate::where('user_id', $guide->id)->latest('id')->firstOrFail();
    }

    public function test_upload_stores_privately_and_notifies_admins(): void
    {
        $guide = $this->guide();
        $certificate = $this->upload($guide);

        $this->assertSame(CertificateStatus::InReview, $certificate->status);
        $this->assertSame('local', $certificate->file_disk);
        $this->assertStringStartsWith('guide-certificates/'.$guide->id.'/', $certificate->file_path);
        Storage::disk('local')->assertExists($certificate->file_path);

        Mail::assertQueued(GuideCertificateSubmittedAdminMail::class);
        $this->assertTrue(AdminNotification::where('type', 'guide_certificate_submitted')->where('meta->certificate_id', $certificate->id)->exists());
    }

    public function test_other_type_requires_a_name_and_file_type_is_checked(): void
    {
        Livewire::actingAs($this->guide())
            ->test(Profile::class)
            ->call('openCertificateForm', 'other')
            ->set('certificateFile', UploadedFile::fake()->create('script.exe', 10))
            ->call('uploadCertificate')
            ->assertHasErrors(['certificateTitle', 'certificateFile']);
    }

    public function test_replace_puts_the_document_back_in_review_and_removes_the_old_file(): void
    {
        $guide = $this->guide();
        $certificate = $this->upload($guide);
        $certificate->update(['status' => CertificateStatus::Verified]);
        $oldPath = $certificate->file_path;

        Livewire::actingAs($guide)
            ->test(Profile::class)
            ->call('openCertificateForm', '', $certificate->id)
            ->set('certificateFile', UploadedFile::fake()->create('new.pdf', 100, 'application/pdf'))
            ->call('uploadCertificate')
            ->assertHasNoErrors();

        $certificate->refresh();
        $this->assertSame(CertificateStatus::InReview, $certificate->status);
        $this->assertNotSame($oldPath, $certificate->file_path);
        Storage::disk('local')->assertMissing($oldPath);
        $this->assertSame(1, GuideCertificate::where('user_id', $guide->id)->count());
    }

    public function test_guides_cannot_touch_another_guides_certificate(): void
    {
        $owner = $this->guide();
        $certificate = $this->upload($owner);
        $other = $this->guide();

        $this->actingAs($other)->get(route('guide.area.certificates.file', $certificate))->assertForbidden();
        $this->actingAs($owner)->get(route('guide.area.certificates.file', $certificate))->assertOk();

        // Looked up through the guide's own relation: another guide's id is "not found" (404 over HTTP).
        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($other)
            ->test(Profile::class)
            ->call('openCertificateForm', '', $certificate->id);
    }

    public function test_admin_verify_and_reject_mail_the_guide(): void
    {
        $guide = $this->guide();
        $certificate = $this->upload($guide);
        $employee = Employee::query()->first() ?? Employee::factory()->create();

        $this->actingAs($employee, 'employees')
            ->get(route('admin.guide-certificates.index'))
            ->assertOk()
            ->assertSee($certificate->original_name);

        $this->actingAs($employee, 'employees')
            ->post(route('admin.guide-certificates.verify', $certificate))
            ->assertRedirect();
        $this->assertSame(CertificateStatus::Verified, $certificate->fresh()->status);
        Mail::assertQueued(GuideCertificateVerifiedMail::class, fn ($mail) => $mail->hasTo($guide->email));

        $this->actingAs($employee, 'employees')
            ->post(route('admin.guide-certificates.reject', $certificate), ['rejection_reason' => 'Unreadable scan'])
            ->assertRedirect();
        $this->assertSame(CertificateStatus::Rejected, $certificate->fresh()->status);
        $this->assertSame('Unreadable scan', $certificate->fresh()->rejection_reason);
        Mail::assertQueued(GuideCertificateRejectedMail::class);
    }

    public function test_guests_cannot_reach_admin_review(): void
    {
        $certificate = $this->upload($this->guide());

        $this->post(route('admin.guide-certificates.verify', $certificate))->assertRedirect();
        $this->assertSame(CertificateStatus::InReview, $certificate->fresh()->status);
    }
}
