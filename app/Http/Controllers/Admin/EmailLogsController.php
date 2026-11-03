<?php

namespace App\Http\Controllers\Admin;

use App\Models\EmailLog;
use App\Http\Controllers\Controller;
use App\Services\Email\EmailLogPreviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class EmailLogsController extends Controller
{
    /** The list renders client-side (DataTables), so cap it to keep the page fast. */
    public const LIST_LIMIT = 1500;

    public function index(): View
    {
        // additional_info holds the full sent HTML; the list never shows it, the preview loads it per row.
        $emailLogs = EmailLog::query()
            ->select(['id', 'email', 'subject', 'type', 'language', 'target', 'created_at'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::LIST_LIMIT)
            ->get();

        return view('admin.pages.email.index', [
            'emailLogs' => $emailLogs,
            'listLimit' => self::LIST_LIMIT,
        ]);
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
