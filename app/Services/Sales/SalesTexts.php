<?php

namespace App\Services\Sales;

use App\Models\Employee;
use App\Models\SalesTextTemplate;

/**
 * The offer builder's fixed customer texts (spec §8.5): the admin-edited version from
 * sales_text_templates when there is one, else the default in lang/{de,en}/sales.php.
 */
class SalesTexts
{
    /** Editable keys, all under sales.customer.* in the language files. */
    public const KEYS = [
        'intro_offer',
        'intro_confirmation',
        'mail_offer',
        'mail_confirmation',
        'signature',
        'thanks',
    ];

    public const LANGUAGES = ['de', 'en'];

    /** @var array<string, string>|null key.language => body, loaded once per request */
    private ?array $overrides = null;

    public function get(string $key, string $locale): string
    {
        $override = $this->overrides()[$key.'.'.$locale] ?? '';

        return $override !== '' ? $override : $this->default($key, $locale);
    }

    public function default(string $key, string $locale): string
    {
        return __('sales.customer.'.$key, [], $locale);
    }

    /**
     * @return array<string, string> key.language => body (only edited texts)
     */
    public function overrides(): array
    {
        return $this->overrides ??= SalesTextTemplate::query()
            ->get(['key', 'language', 'body'])
            ->mapWithKeys(fn (SalesTextTemplate $text) => [$text->key.'.'.$text->language => (string) $text->body])
            ->all();
    }

    /**
     * Saves the edited texts; an empty text (or one equal to the default) restores the default.
     *
     * @param  array<string, array<string, ?string>>  $texts  key => [language => body]
     */
    public function save(array $texts, ?Employee $actor): void
    {
        foreach (self::KEYS as $key) {
            foreach (self::LANGUAGES as $language) {
                $body = trim((string) ($texts[$key][$language] ?? ''));

                if ($body === '' || $body === $this->default($key, $language)) {
                    SalesTextTemplate::query()->where(['key' => $key, 'language' => $language])->delete();

                    continue;
                }

                SalesTextTemplate::query()->updateOrCreate(
                    ['key' => $key, 'language' => $language],
                    ['body' => $body, 'updated_by' => $actor?->id],
                );
            }
        }

        $this->overrides = null;
    }
}
