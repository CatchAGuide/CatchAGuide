<?php

namespace App\Services\Voucher;

use App\Models\VoucherOrder;
use App\Presenters\Voucher\VoucherOrderPresenter;
use App\Services\Sales\SalesLinks;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Traits\Localizable;
use Throwable;

/**
 * Fills the Catch a Guide standard voucher (A4, resources/views/vouchers/pdf/cag-standard) for
 * an order, in the order's checkout language, and stores it on the private voucher disk. The
 * price is deliberately not printed: the voucher is a gift. The QR code opens the tour page on
 * the domain of the order's language.
 */
class VoucherPdfGenerator
{
    use Localizable;

    /** Light logo variant (as in the site footer and mail header), for the dark footer band. */
    private const LOGO = 'assets/images/logo/CatchAGuide2_Logo_PNG.png';

    /** Barlow / Barlow Condensed TTFs (OFL) of the branded design; dompdf can't read woff2. */
    private const FONT_DIR = 'fonts/barlow';

    public function __construct(
        private readonly VoucherOrderPresenter $presenter,
        private readonly SalesLinks $links,
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
        $locale = $order->language ?: app()->getLocale();

        // dompdf caches the Barlow metrics there and fails when the folder is missing.
        File::ensureDirectoryExists((string) config('dompdf.options.font_cache', storage_path('fonts')));

        return $this->withLocale($locale, fn () => Pdf::loadView('vouchers.pdf.cag-standard', [
            'voucher' => $this->presenter->details($order),
            'logo' => $this->localImage(public_path(self::LOGO)),
            'qr' => $this->tourQr($order, $locale),
            'fontDir' => str_replace('\\', '/', resource_path(self::FONT_DIR)),
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

    /**
     * Clean tour URL (no query string) on the order language's domain.
     */
    public function tourUrl(VoucherOrder $order, string $locale): ?string
    {
        $slug = $order->guiding?->slug;

        return $slug ? $this->links->listing('guidings.show', ['slug' => $slug], $locale) : null;
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
     * PNG data URI of a QR code for the tour page, or null without a tour (the slot stays empty).
     */
    private function tourQr(VoucherOrder $order, string $locale): ?string
    {
        $url = $this->tourUrl($order, $locale);
        if ($url === null) {
            return null;
        }

        try {
            return (new QRCode(new QROptions([
                'outputInterface' => QRGdImagePNG::class,
                'eccLevel' => EccLevel::M,
                'scale' => 8,
                'quietzoneSize' => 0,
            ])))->render($url);
        } catch (Throwable) {
            return null;
        }
    }
}
