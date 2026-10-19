<?php

namespace App\Services\Voucher;

use App\Models\VoucherOrder;
use App\Presenters\Voucher\VoucherOrderPresenter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Traits\Localizable;
use Throwable;

/**
 * Fills the Catch a Guide standard voucher (A4, resources/views/vouchers/pdf/cag-standard) for
 * an order, in the order's checkout language, and stores it on the private voucher disk. The
 * price is deliberately not printed: the voucher is a gift.
 */
class VoucherPdfGenerator
{
    use Localizable;

    /** Light logo variant (as in the site footer and mail header), for the dark band. */
    private const LOGO = 'assets/images/logo/CatchAGuide2_Logo_PNG.png';

    /** Guide photo download limit; the voucher renders without it when it's slow or missing. */
    private const PHOTO_TIMEOUT_SECONDS = 4;

    /**
     * The photo prints at 58pt; dompdf stores images uncompressed, so a full-size tour photo
     * made the PDF (and the guide's email attachment) several MB. Shrink it first.
     */
    private const PHOTO_MAX_PX = 240;

    public function __construct(
        private readonly VoucherOrderPresenter $presenter,
    ) {}

    /**
     * Renders and stores the PDF, returning its path on the voucher disk.
     */
    public function generate(VoucherOrder $order): string
    {
        $path = trim((string) config('vouchers.pdf_folder', 'vouchers'), '/').'/'.$order->voucher_code.'.pdf';

        Storage::disk($this->disk())->put($path, $this->render($order));

        return $path;
    }

    public function render(VoucherOrder $order): string
    {
        return $this->withLocale($order->language ?: app()->getLocale(), fn () => Pdf::loadView('vouchers.pdf.cag-standard', [
            'voucher' => $this->presenter->details($order),
            'logo' => $this->localImage(public_path(self::LOGO)),
            'guidePhoto' => $this->guidePhoto($order),
        ])->setPaper('a4')->setOption('isFontSubsettingEnabled', true)->output());
    }

    public function contents(VoucherOrder $order): ?string
    {
        if (! $order->hasPdf() || ! Storage::disk($this->disk())->exists($order->pdf_path)) {
            return null;
        }

        return Storage::disk($this->disk())->get($order->pdf_path);
    }

    public function fileName(VoucherOrder $order): string
    {
        return $this->withLocale($order->language ?: app()->getLocale(), fn () => __('checkout.voucher.pdf.file_name', ['code' => $order->voucher_code]));
    }

    private function disk(): string
    {
        return (string) config('vouchers.pdf_disk', 'local');
    }

    private function localImage(string $file): ?string
    {
        return is_file($file) ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($file)) : null;
    }

    /**
     * The guide's profile photo as a data URI, so dompdf never fetches remote files itself.
     */
    private function guidePhoto(VoucherOrder $order): ?string
    {
        $guide = $order->guide;
        if ($guide === null || empty($guide->profil_image)) {
            return null;
        }

        try {
            $response = Http::timeout(self::PHOTO_TIMEOUT_SECONDS)->get(guide_profile_photo_url($guide));
            $type = strtolower((string) $response->header('Content-Type'));

            if (! $response->successful() || ! preg_match('#^image/(jpeg|png|webp|gif)#', $type)) {
                return null;
            }

            return $this->thumbnail($response->body())
                ?? 'data:'.explode(';', $type)[0].';base64,'.base64_encode($response->body());
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Square-cropped JPEG data URI of at most PHOTO_MAX_PX, or null when GD can't read the image.
     */
    private function thumbnail(string $bytes): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $source = @imagecreatefromstring($bytes);
        if ($source === false) {
            return null;
        }

        $side = min(imagesx($source), imagesy($source));
        $size = min($side, self::PHOTO_MAX_PX);
        $thumb = imagecreatetruecolor($size, $size);
        imagefill($thumb, 0, 0, imagecolorallocate($thumb, 255, 255, 255));
        imagecopyresampled(
            $thumb, $source, 0, 0,
            intdiv(imagesx($source) - $side, 2), intdiv(imagesy($source) - $side, 2),
            $size, $size, $side, $side,
        );

        ob_start();
        imagejpeg($thumb, null, 82);

        return 'data:image/jpeg;base64,'.base64_encode((string) ob_get_clean());
    }
}
