<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'language',
        'subject',
        'type',
        'status',
        'target',
        'additional_info',
    ];

    protected $table = 'email_logs';

    public $timestamps = true;

    /**
     * Normalize stored language values (e.g. "Deutsch" / "English") to locale codes.
     */
    public static function normalizeLanguage(?string $language): string
    {
        $value = strtolower(trim((string) $language));

        $map = [
            'de' => 'de',
            'deutsch' => 'de',
            'german' => 'de',
            'en' => 'en',
            'englisch' => 'en',
            'english' => 'en',
            'gb' => 'en',
        ];

        return $map[$value] ?? $value;
    }

    public function getNormalizedLanguageAttribute(): string
    {
        return static::normalizeLanguage($this->language);
    }

    /**
     * Flag icon code for the language column (flag-icons / fi-*).
     */
    public function getLanguageFlagCodeAttribute(): ?string
    {
        return match ($this->normalized_language) {
            'de' => 'de',
            'en' => 'gb',
            default => null,
        };
    }

    public function additionalInfoArray(): array
    {
        if (empty($this->additional_info)) {
            return [];
        }

        $decoded = json_decode($this->additional_info, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function storedBodyHtml(): ?string
    {
        $info = $this->additionalInfoArray();
        $body = $info['body_html'] ?? null;

        return is_string($body) && trim($body) !== '' ? $body : null;
    }

    /**
     * Template catalogue key matching this log type, if configured.
     */
    public function templateKey(): ?string
    {
        foreach (config('email_templates.templates', []) as $key => $meta) {
            if (($meta['log_type'] ?? null) === $this->type) {
                return $key;
            }
        }

        return null;
    }
}
