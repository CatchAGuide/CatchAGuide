<?php

namespace App\Services\Guide\Area;

use App\Enums\CertificateStatus;
use App\Mail\Guide\GuideCertificateRejectedMail;
use App\Mail\Guide\GuideCertificateSubmittedAdminMail;
use App\Mail\Guide\GuideCertificateVerifiedMail;
use App\Models\GuideCertificate;
use App\Models\User;
use App\Services\AdminNotificationService;
use App\Services\Media\PrivateDocumentStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GuideCertificateService
{
    public function __construct(
        private readonly PrivateDocumentStorage $storage,
        private readonly AdminNotificationService $adminNotifications,
    ) {}

    /** @return array<int, string> */
    public function types(): array
    {
        return array_values((array) config('guide_area.certificates.types', []));
    }

    /**
     * Rows for the profile page: every recommended type (its newest upload, or an empty
     * "Not added" row), then every other upload, newest first.
     *
     * @return Collection<int, array{type: string, certificate: ?GuideCertificate}>
     */
    public function rows(User $user): Collection
    {
        $certificates = $user->guideCertificates()->latest('id')->get();
        $rows = collect();
        $used = [];

        foreach ((array) config('guide_area.certificates.recommended', []) as $type) {
            $certificate = $certificates->firstWhere('type', $type);
            if ($certificate) {
                $used[] = $certificate->id;
            }
            $rows->push(['type' => $type, 'certificate' => $certificate]);
        }

        foreach ($certificates as $certificate) {
            if (! in_array($certificate->id, $used, true)) {
                $rows->push(['type' => $certificate->type, 'certificate' => $certificate]);
            }
        }

        return $rows;
    }

    /** Upload a new certificate, or replace the file of an existing one (back to review). */
    public function upload(
        User $user,
        string $type,
        ?string $title,
        UploadedFile $file,
        ?string $validUntil = null,
        ?GuideCertificate $replace = null,
    ): GuideCertificate {
        if ($replace !== null && $replace->user_id !== $user->id) {
            throw new \InvalidArgumentException('Certificate belongs to another guide.');
        }

        $path = $this->storage->store($file, config('guide_area.certificates.folder', 'guide-certificates').'/'.$user->id);

        $attributes = [
            'type' => $replace?->type ?? $type,
            'title' => $type === 'other' || $replace?->type === 'other' ? ($title ?: $replace?->title) : null,
            'file_disk' => $this->storage->diskName(),
            'file_path' => $path,
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 250),
            'mime' => $file->getMimeType(),
            'size' => (int) $file->getSize(),
            'valid_until' => $validUntil ? Carbon::parse($validUntil)->toDateString() : null,
            'status' => CertificateStatus::InReview,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
        ];

        if ($replace !== null) {
            $oldDisk = $replace->file_disk;
            $oldPath = $replace->file_path;
            $replace->update($attributes);
            $this->deleteFile($oldDisk, $oldPath);
            $certificate = $replace;
        } else {
            $certificate = $user->guideCertificates()->create($attributes);
        }

        $this->notifyAdmins($certificate);

        return $certificate;
    }

    public function verify(GuideCertificate $certificate, int $employeeId): void
    {
        $certificate->update([
            'status' => CertificateStatus::Verified,
            'reviewed_by' => $employeeId,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        Mail::to($certificate->user)->queue(
            (new GuideCertificateVerifiedMail($certificate))->locale($this->locale($certificate->user))
        );
    }

    public function reject(GuideCertificate $certificate, int $employeeId, ?string $reason): void
    {
        $certificate->update([
            'status' => CertificateStatus::Rejected,
            'reviewed_by' => $employeeId,
            'reviewed_at' => now(),
            'rejection_reason' => $reason ? trim($reason) : null,
        ]);

        Mail::to($certificate->user)->queue(
            (new GuideCertificateRejectedMail($certificate))->locale($this->locale($certificate->user))
        );
    }

    private function notifyAdmins(GuideCertificate $certificate): void
    {
        $user = $certificate->user;
        $name = trim($user->firstname.' '.$user->lastname) ?: $user->email;

        $this->adminNotifications->notify(
            'guide_certificate_submitted',
            __('guide_area.admin.notification_title', ['name' => $name], 'en'),
            $certificate->label(),
            ['link' => route('admin.guide-certificates.index'), 'meta' => ['certificate_id' => $certificate->id, 'user_id' => $user->id], 'created_by' => null]
        );

        $recipients = GuideCertificateSubmittedAdminMail::recipients();
        Mail::to(array_shift($recipients))->cc($recipients)->queue(
            (new GuideCertificateSubmittedAdminMail($certificate))->locale('de')
        );
    }

    private function deleteFile(string $disk, string $path): void
    {
        try {
            $this->storage->delete($disk, $path);
        } catch (\Throwable $e) {
            Log::warning('Guide certificate file delete failed', ['path' => $path, 'error' => $e->getMessage()]);
        }
    }

    private function locale(User $user): string
    {
        return in_array($user->language, ['de', 'en'], true) ? $user->language : 'de';
    }
}
