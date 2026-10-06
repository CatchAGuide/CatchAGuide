<?php

namespace App\Services\Sales;

use App\Models\SalesDocument;

/**
 * Absolute links in a sales document's language: the customer page and the public listing
 * pages it links to live on catchaguide.de (DE) or catchaguide.com (EN).
 */
class SalesLinks
{
    public function customerUrl(SalesDocument $document): string
    {
        return $this->absolute(route('sales-offers.show', ['lang' => $document->locale(), 'token' => $document->public_token], false), $document->locale());
    }

    /**
     * @param  array<string, mixed>|string  $parameters
     */
    public function listing(string $routeName, array|string $parameters, string $locale): string
    {
        return $this->absolute(route($routeName, $parameters, false), $locale);
    }

    /**
     * Links and site name for <x-mail.cag-shell> in the document language.
     *
     * @return array{site: string, homeUrl: string, imprintUrl: string, privacyUrl: string, termsUrl: string}
     */
    public function shell(string $locale): array
    {
        $home = $this->absolute('/', $locale);

        return [
            'site' => preg_replace('/^www\./', '', (string) parse_url($home, PHP_URL_HOST)),
            'homeUrl' => $home,
            'imprintUrl' => $this->absolute(route('law.imprint', [], false), $locale),
            'privacyUrl' => $this->absolute(route('law.data-protection', [], false), $locale),
            'termsUrl' => $this->absolute(route('law.agb', [], false), $locale),
        ];
    }

    public function absolute(string $path, string $locale): string
    {
        if (! config('sales_documents.language_domains')) {
            return url($path);
        }

        $base = (string) config($locale === 'en' ? 'cag.en_app_url' : 'cag.de_app_url');

        return rtrim($base, '/').'/'.ltrim($path, '/');
    }
}
