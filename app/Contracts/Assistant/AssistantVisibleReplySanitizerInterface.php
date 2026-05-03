<?php

namespace App\Contracts\Assistant;

interface AssistantVisibleReplySanitizerInterface
{
    /**
     * @param  array<string, mixed>|null  $ui
     */
    public function sanitize(string $content, ?array $ui): string;
}
