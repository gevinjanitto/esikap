<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $r)
    {
        $rows = AuditLog::with('user')
            ->when($r->get('user'), fn ($q, $u) => $q->where('user_id', $u))
            ->when($r->get('action'), fn ($q, $a) => $q->where('action', $a))
            ->when($r->get('model'), fn ($q, $m) => $q->where('model_type', $m))
            ->when($r->get('dari'), fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($r->get('sampai'), fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest('id')->paginate(20)->withQueryString();
        $users = User::orderBy('name')->pluck('name', 'id');
        $models = AuditLog::select('model_type')->distinct()->pluck('model_type')->filter()->sort()->values();
        $actions = AuditLog::select('action')->distinct()->pluck('action')->sort()->values();

        return view('audit.index', compact('rows', 'users', 'models', 'actions'));
    }
}

