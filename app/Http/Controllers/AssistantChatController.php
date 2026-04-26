<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssistantChatRequest;
use App\Services\Assistant\BookingAssistantOrchestrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class AssistantChatController extends Controller
{
    public function __invoke(AssistantChatRequest $request, BookingAssistantOrchestrator $orchestrator): JsonResponse
    {
        if (!config('booking_assistant.enabled')) {
            return response()->json([
                'message' => null,
                'error' => 'disabled',
            ], 503);
        }

        try {
            $result = $orchestrator->run(
                $request->messagesPayload(),
                $request->validated('page_context') ?? null,
            );

            return response()->json([
                'message' => [
                    'role' => 'assistant',
                    'content' => $result['content'],
                ],
                'ui' => $result['ui'] ?? null,
                'error' => $result['error'],
            ]);
        } catch (Throwable $e) {
            Log::warning('Booking assistant chat failed', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => null,
                'error' => 'provider_error',
            ], 502);
        }
    }
}
