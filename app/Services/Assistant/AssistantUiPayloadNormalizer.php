<?php

namespace App\Services\Assistant;

use App\Contracts\Assistant\AssistantUiPayloadNormalizerInterface;

final class AssistantUiPayloadNormalizer implements AssistantUiPayloadNormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize(?array $ui): ?array
    {
        if ($ui === null) {
            return null;
        }

        if (isset($ui['quick_replies']) && is_array($ui['quick_replies'])) {
            $ui['quick_replies'] = array_values(array_filter(array_map(function ($item) {
                if (is_string($item)) {
                    return trim($item);
                }
                if (is_array($item) && isset($item['text']) && is_string($item['text'])) {
                    return trim($item['text']);
                }

                return null;
            }, $ui['quick_replies']), static fn ($v) => is_string($v) && $v !== ''));
        }

        if (isset($ui['cards']) && is_array($ui['cards'])) {
            $ui['cards'] = array_values(array_filter(array_map(function ($card) {
                if (! is_array($card)) {
                    return null;
                }
                $title = isset($card['title']) && is_string($card['title']) ? trim($card['title']) : '';
                $url = isset($card['url']) && is_string($card['url']) ? trim($card['url']) : '';
                if ($title === '' || $url === '') {
                    return null;
                }

                return [
                    'type' => isset($card['type']) && is_string($card['type']) ? $card['type'] : null,
                    'title' => $title,
                    'url' => $url,
                    'price' => isset($card['price']) && is_numeric($card['price']) ? (float) $card['price'] : null,
                    'min_price' => isset($card['min_price']) && is_numeric($card['min_price']) ? (float) $card['min_price'] : null,
                    'currency' => isset($card['currency']) && is_string($card['currency']) ? $card['currency'] : 'EUR',
                    'snippet' => isset($card['snippet']) && is_string($card['snippet']) ? $card['snippet'] : '',
                    'image' => isset($card['image']) && is_string($card['image']) ? $card['image'] : null,
                ];
            }, $ui['cards'])));
        }

        return $ui;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeCatalogFallback(?array $ui, array $fallbackCards): ?array
    {
        $ui = $ui ?? [];

        $hasCards = isset($ui['cards']) && is_array($ui['cards']) && count($ui['cards']) > 0;
        if (! $hasCards && $fallbackCards !== []) {
            $ui['cards'] = array_slice($fallbackCards, 0, 6);
            $ui['quick_replies'] = $ui['quick_replies'] ?? [
                'Show more options',
                'Different destination',
                'Different dates',
                'Budget under €200',
            ];
        }

        return $ui === [] ? null : $ui;
    }
}
