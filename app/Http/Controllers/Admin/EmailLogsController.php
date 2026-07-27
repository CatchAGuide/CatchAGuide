<?php

namespace App\Http\Controllers\Admin;

use App\Models\EmailLog;
use App\Http\Controllers\Controller;
use App\Services\Email\EmailLogPreviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class EmailLogsController extends Controller
{
    public function index(): View
    {
        $emailLogs = EmailLog::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.pages.email.index', compact('emailLogs'));
    }

    public function show(EmailLog $emailLog, EmailLogPreviewService $preview): JsonResponse
    {
        $result = $preview->render($emailLog);

        return response()->json([
            'success' => $result['html'] !== null,
            'id' => $emailLog->id,
            'email' => $emailLog->email,
            'subject' => $emailLog->subject,
            'type' => $emailLog->type,
            'language' => $emailLog->normalized_language,
            'target' => $emailLog->target,
            'created_at' => optional($emailLog->created_at)->format('F j, Y g:i A'),
            'html' => $result['html'],
            'source' => $result['source'],
            'error' => $result['error'],
            'has_content' => $result['html'] !== null,
        ], $result['html'] !== null ? 200 : 422);
    }
}
