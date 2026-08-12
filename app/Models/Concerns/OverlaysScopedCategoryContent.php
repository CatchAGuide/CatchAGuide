<?php

namespace App\Models\Concerns;

use App\Models\Language;

trait OverlaysScopedCategoryContent
{
    public function overlayScopedTranslation(?Language $content): void
    {
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

    abstract protected function getCurrentTranslation();

    abstract protected function setCurrentTranslation($translation): void;

    abstract protected function newTranslationForOverlay(string $locale);
}
