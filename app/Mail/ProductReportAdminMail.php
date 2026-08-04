<?php

namespace App\Mail;

use App\Models\ProductReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProductReportAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $copyNamespace = 'emails.product_report_admin';
    public string $type = 'product_report_admin_mail';
    public string $language;
    public string $target = 'product_report_admin_mail';

    public function __construct(public ProductReport $report)
    {
        $this->language = app()->getLocale();
    }

    public function build()
    {
        return $this->view('mails.product-report-admin')
            ->to(config('mail.admin_email'))
            ->subject(__($this->copyNamespace . '.subject'));
    }
}
