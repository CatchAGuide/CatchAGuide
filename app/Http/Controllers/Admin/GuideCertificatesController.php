<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CertificateStatus;
use App\Http\Controllers\Controller;
use App\Models\GuideCertificate;
use App\Services\Guide\Area\GuideCertificateService;
use App\Services\Media\PrivateDocumentStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuideCertificatesController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', CertificateStatus::InReview->value);
        if (! in_array($status, array_column(CertificateStatus::cases(), 'value'), true) && $status !== 'all') {
            $status = CertificateStatus::InReview->value;
        }

        $certificates = GuideCertificate::query()
            ->with('user:id,firstname,lastname,email')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest('updated_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.pages.guide-certificates.index', [
            'certificates' => $certificates,
            'status' => $status,
            'inReviewCount' => GuideCertificate::inReview()->count(),
        ]);
    }

    public function file(GuideCertificate $certificate, PrivateDocumentStorage $storage): StreamedResponse
    {
        return $storage->download($certificate->file_disk, $certificate->file_path, $certificate->original_name, $certificate->mime);
    }

    public function verify(GuideCertificate $certificate, GuideCertificateService $service): RedirectResponse
    {
        $service->verify($certificate, (int) auth('employees')->id());

        return back()->with('message', __('guide_area.admin.verified_flash'));
    }

    public function reject(Request $request, GuideCertificate $certificate, GuideCertificateService $service): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $service->reject($certificate, (int) auth('employees')->id(), $data['rejection_reason']);

        return back()->with('message', __('guide_area.admin.rejected_flash'));
    }
}
