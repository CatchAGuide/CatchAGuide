<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Security\ThreatOverviewService;
use Illuminate\Http\Request;

class SecurityThreatsController extends Controller
{
    public const WINDOWS = [1, 24, 72, 168];

    public function index(Request $request, ThreatOverviewService $threats)
    {
        $validated = $request->validate([
            'hours' => ['nullable', 'integer', 'in:'.implode(',', self::WINDOWS)],
            'ip' => ['nullable', 'string', 'max:45'],
        ]);

        $hours = (int) ($validated['hours'] ?? 24);
        $ip = trim((string) ($validated['ip'] ?? '')) ?: null;

        return view('admin.pages.security.threats', [
            'hours' => $hours,
            'windows' => self::WINDOWS,
            'ip' => $ip,
            'overview' => $threats->overview($hours, $ip),
        ]);
    }
}
