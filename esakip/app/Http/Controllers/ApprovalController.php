<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLog;
use App\Support\Workflow;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index(Request $r)
    {
        $u = $r->user();
        $pending = Workflow::pendingFor($u);
        $history = ApprovalLog::with('user', 'approvable')->where('user_id', $u->id)->latest('id')->limit(15)->get();

        return view('approval.index', compact('pending', 'history'));
    }

    public function transition(Request $r, string $type, int $id, string $action)
    {
        abort_unless(isset(Workflow::TYPES[$type]), 404);
        $model = Workflow::TYPES[$type]::findOrFail($id);
        $needNote = in_array($action, ['revise', 'reject', 'unlock']);
        $d = $r->validate(['note' => ($needNote ? 'required' : 'nullable').'|string|max:2000'], ['note.required' => 'Catatan wajib diisi untuk aksi ini.']);
        if (! Workflow::can($r->user(), $model, $type, $action)) {
            $msg = in_array($action, ['approve', 'reject', 'revise', 'establish']) && (int) $model->created_by === (int) $r->user()->id
                ? 'Anda tidak dapat menyetujui/meninjau data yang Anda buat sendiri.'
                : 'Aksi tidak diizinkan untuk peran atau status data saat ini.';

            return back()->with('err', $msg);
        }
        if ($type === 'renstra' && $action === 'submit' && ! $model->tujuans()->exists()) {
            return back()->with('err', 'Renstra belum memiliki tujuan/sasaran. Lengkapi terlebih dahulu sebelum diajukan.');
        }
        Workflow::transition($r->user(), $model, $type, $action, $d['note'] ?? null);

        return back()->with('ok', Workflow::LABELS[$type].': '.Workflow::ACTIONS[$action]['label'].' berhasil.');
    }
}
