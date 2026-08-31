<?php

namespace App\Models\Concerns;

use App\Models\Language;

trait OverlaysScopedCategoryContent
{
    private ?Language $scopedCmsLanguage = null;

    private bool $usesScopedCms = false;

    public function overlayScopedTranslation(?Language $content): void
    {
        $this->usesScopedCms = true;
        $this->scopedCmsLanguage = $content;

        if ($content === null) {
            return;
        }

        $translation = $this->getCurrentTranslation();
        if ($translation === null) {
            $translation = $this->newTranslationForOverlay(app()->getLocale());
        }

        foreach (['title', 'sub_title', 'introduction', 'content', 'faq_title'] as $field) {
            if (filled($content->{$field})) {
                $translation->{$field} = $content->{$field};
            }
        }

        $this->setCurrentTranslation($translation);
    }

    public function scopedCmsValue(string $field): ?string
    {
        if ($this->usesScopedCms) {
            if ($this->scopedCmsLanguage === null) {
                return null;
            }

            $value = $this->scopedCmsLanguage->{$field} ?? null;

            return filled($value) ? (string) $value : null;
        }

        $translation = $this->getCurrentTranslation();
        if ($translation === null) {
            return null;
        }

        $value = $translation->{$field} ?? null;

        return filled($value) ? (string) $value : null;
    }

    abstract protected function getCurrentTranslation();

    abstract protected function setCurrentTranslation($translation): void;

    abstract protected function newTranslationForOverlay(string $locale);
}
