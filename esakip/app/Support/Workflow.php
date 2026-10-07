<?php

namespace App\Support;

use App\Models\ApprovalLog;
use App\Models\AuditLog;
use App\Models\Notif;
use App\Models\PerjanjianKinerja;
use App\Models\Realisasi;
use App\Models\Renja;
use App\Models\Renstra;
use App\Models\Rpjmd;
use App\Models\User;
use Illuminate\Support\Collection;

class Workflow
{
    public const TYPES = [
        'rpjmd' => Rpjmd::class,
        'renstra' => Renstra::class,
        'renja' => Renja::class,
        'pk' => PerjanjianKinerja::class,
        'realisasi' => Realisasi::class,
    ];

    public const LABELS = ['rpjmd' => 'RPJMD', 'renstra' => 'Renstra OPD', 'renja' => 'Renja', 'pk' => 'Perjanjian Kinerja', 'realisasi' => 'Realisasi'];

    public const ACTIONS = [
        'submit' => ['from' => ['draft', 'revisi', 'ditolak'], 'to' => 'diajukan', 'label' => 'Ajukan', 'icon' => 'send', 'tone' => 'primary'],
        'approve' => ['from' => ['diajukan'], 'to' => 'disetujui', 'label' => 'Setujui', 'icon' => 'thumbs-up', 'tone' => 'success'],
        'revise' => ['from' => ['diajukan', 'disetujui'], 'to' => 'revisi', 'label' => 'Minta Revisi', 'icon' => 'rotate-ccw', 'tone' => 'warn'],
        'reject' => ['from' => ['diajukan'], 'to' => 'ditolak', 'label' => 'Tolak', 'icon' => 'ban', 'tone' => 'danger'],
        'establish' => ['from' => ['disetujui'], 'to' => 'ditetapkan', 'label' => 'Tetapkan', 'icon' => 'lock', 'tone' => 'dark'],
        'unlock' => ['from' => ['ditetapkan'], 'to' => 'revisi', 'label' => 'Buka Kunci', 'icon' => 'key-round', 'tone' => 'warn'],
        'archive' => ['from' => ['draft', 'ditolak'], 'to' => 'diarsipkan', 'label' => 'Arsipkan', 'icon' => 'archive', 'tone' => 'ghost'],
    ];

    private const ROLES = [
        'rpjmd' => ['submit' => ['bappeda', 'admin_pemda'], 'review' => ['pimpinan', 'admin_pemda'], 'establish' => ['bappeda', 'admin_pemda'], 'unlock' => ['admin_pemda']],
        'renstra' => ['submit' => ['operator_opd', 'admin_pemda'], 'review' => ['kepala_opd'], 'establish' => ['tim_sakip', 'bappeda'], 'unlock' => ['admin_pemda', 'tim_sakip']],
        'renja' => ['submit' => ['operator_opd', 'admin_pemda'], 'review' => ['kepala_opd'], 'establish' => ['tim_sakip', 'bappeda'], 'unlock' => ['admin_pemda', 'tim_sakip']],
        'pk' => ['submit' => ['operator_opd', 'admin_pemda'], 'review' => ['kepala_opd'], 'establish' => ['tim_sakip'], 'unlock' => ['admin_pemda', 'tim_sakip']],
        'realisasi' => ['submit' => ['operator_opd'], 'review' => ['kepala_opd'], 'establish' => ['tim_sakip'], 'unlock' => ['admin_pemda', 'tim_sakip']],
    ];

    private static function group(string $action, string $status): string
    {
        return match ($action) {
            'submit', 'archive' => 'submit',
            'approve', 'reject' => 'review',
            'revise' => $status === 'disetujui' ? 'establish' : 'review',
            'establish' => 'establish',
            'unlock' => 'unlock',
        };
    }

    public static function rolesFor(string $type, string $group): array
    {
        return self::ROLES[$type][$group] ?? [];
    }

    public static function can(User $u, $model, string $type, string $action): bool
    {
        $a = self::ACTIONS[$action] ?? null;
        if (! $a || ! isset(self::TYPES[$type]) || ! in_array($model->status, $a['from'])) {
            return false;
        }
        $roles = self::rolesFor($type, self::group($action, $model->status));
        if ($u->role !== 'super_admin' && ! in_array($u->role, $roles)) {
            return false;
        }
        if ($u->isOpdScoped() && isset($model->opd_id) && (int) $model->opd_id !== (int) $u->opd_id) {
            return false;
        }
        if (in_array($action, ['approve', 'reject', 'revise', 'establish']) && $model->created_by && (int) $model->created_by === (int) $u->id) {
            return false;
        }

        return true;
    }

    public static function available(User $u, $model, string $type): array
    {
        return array_values(array_filter(array_keys(self::ACTIONS), fn ($a) => self::can($u, $model, $type, $a)));
    }

    public static function transition(User $u, $model, string $type, string $action, ?string $note): void
    {
        $from = $model->status;
        $to = self::ACTIONS[$action]['to'];
        $model->status = $to;
        if ($action === 'unlock' && isset($model->version)) {
            $model->version = $model->version + 1;
        }
        $model->saveQuietly();

        ApprovalLog::create([
            'approvable_type' => $type, 'approvable_id' => $model->id, 'user_id' => $u->id, 'role' => $u->role,
            'action' => $action, 'from_status' => $from, 'to_status' => $to, 'note' => $note,
        ]);
        AuditLog::record($action, $model, ['status' => $from], ['status' => $to, 'catatan' => $note]);
        self::notify($model, $type, $to, $u);
    }

    private static function notify($model, string $type, string $to, User $actor): void
    {
        $group = match ($to) { 'diajukan' => 'review', 'disetujui' => 'establish', default => null };
        if ($group) {
            $q = User::where('is_active', true)->whereIn('role', self::rolesFor($type, $group));
            $users = $q->get()->filter(fn ($x) => ! $x->isOpdScoped() || ! isset($model->opd_id) || (int) $x->opd_id === (int) $model->opd_id);
        } else {
            $users = User::where('id', $model->created_by)->get();
        }
        foreach ($users as $user) {
            if ($user->id === $actor->id) {
                continue;
            }
            Notif::create([
                'user_id' => $user->id,
                'title' => self::LABELS[$type].' '.Esakip::label($to),
                'message' => $model->workflowTitle().' oleh '.$actor->name,
                'link' => $model->workflowUrl(),
            ]);
        }
    }

    public static function pendingFor(User $u): Collection
    {
        $out = collect();
        foreach (self::TYPES as $type => $class) {
            $q = $class::query()->whereIn('status', ['diajukan', 'disetujui']);
            if ($u->isOpdScoped() && $type !== 'rpjmd') {
                $q->where('opd_id', $u->opd_id);
            }
            foreach ($q->latest('updated_at')->limit(100)->get() as $m) {
                $acts = array_intersect(self::available($u, $m, $type), ['approve', 'establish', 'revise', 'reject']);
                if ($acts) {
                    $out->push(['type' => $type, 'model' => $m, 'actions' => array_values($acts)]);
                }
            }
        }

        return $out->sortByDesc(fn ($x) => $x['model']->updated_at)->values();
    }
}
