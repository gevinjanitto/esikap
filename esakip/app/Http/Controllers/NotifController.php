<?php

namespace App\Http\Controllers;

use App\Models\Notif;
use Illuminate\Http\Request;

class NotifController extends Controller
{
    public function index(Request $r)
    {
        $rows = $r->user()->notifs()->paginate(20);

        return view('notif.index', compact('rows'));
    }

    public function open(Request $r, Notif $notif)
    {
        abort_unless($notif->user_id === $r->user()->id, 403);
        $notif->update(['read_at' => now()]);

        return redirect($notif->link ?: route('dashboard'));
    }

    public function readAll(Request $r)
    {
        $r->user()->notifs()->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('ok', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
