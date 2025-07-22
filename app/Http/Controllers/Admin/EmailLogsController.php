<?php

namespace App\Http\Controllers\Admin;

use App\Models\EmailLog;
use App\Http\Controllers\Controller;

class EmailLogsController extends Controller
{
    public function index()
    {
        $emailLogs = EmailLog::orderBy('created_at', 'desc')->get();
        return view('admin.pages.email.index', compact('emailLogs'));
    }
}
