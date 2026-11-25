<?php

namespace App\Http\Controllers\Guide;

use App\Http\Controllers\Controller;
use App\Models\GuideCertificate;
use App\Services\Media\PrivateDocumentStorage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuideCertificateFileController extends Controller
{
    /** The guide's own uploaded certificate file. */
    public function show(GuideCertificate $certificate, PrivateDocumentStorage $storage): StreamedResponse
    {
        $this->authorize('view', $certificate);

        return $storage->download($certificate->file_disk, $certificate->file_path, $certificate->original_name, $certificate->mime);
    }
}
