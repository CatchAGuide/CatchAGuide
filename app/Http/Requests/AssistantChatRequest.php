<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssistantChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxMessages = (int) config('booking_assistant.max_messages', 16);

        return [
            'messages' => ['required', 'array', 'min:1', 'max:' . $maxMessages],
            'messages.*.role' => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:4000'],
            'page_context' => ['nullable', 'string', 'max:64'],
        ];
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    public function messagesPayload(): array
    {
        $data = $this->validated();
        $out = [];
        foreach ($data['messages'] as $row) {
            $out[] = [
                'role' => $row['role'],
                'content' => strip_tags($row['content']),
            ];
        }

        return $out;
    }
}
