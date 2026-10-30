<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Read-only views of the audit log. There are no endpoints that change it. */
class AuditController extends Controller
{
    /** Brand admins: what happened in the current brand. */
    public function brand(Request $request): JsonResponse
    {
        $tenantId = app(CurrentTenant::class)->id();

        return $this->respond($request, AuditLog::where('tenant_id', $tenantId));
    }

    /** Super admins: everything, or one brand (?tenant=id), or platform-only events (?tenant=platform). */
    public function platform(Request $request): JsonResponse
    {
        $query = AuditLog::with('tenant:id,name,slug')
            ->when($request->tenant === 'platform', fn ($q) => $q->whereNull('tenant_id'))
            ->when(ctype_digit((string) $request->tenant), fn ($q) => $q->where('tenant_id', (int) $request->tenant));

        return $this->respond($request, $query);
    }

    private function respond(Request $request, Builder $query): JsonResponse
    {
        $data = $request->validate([
            'group'    => 'nullable|string|max:30|alpha_dash',  // action prefix: post, member, brand, …
            'user'     => 'nullable|integer',
            'search'   => 'nullable|string|max:100',
            'from'     => 'nullable|date',
            'to'       => 'nullable|date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        // Who appears in this log, for the "person" filter.
        $actors = (clone $query)->reorder()->whereNotNull('user_id')
            ->select('user_id', 'actor_name')->distinct()->orderBy('actor_name')->limit(100)->get()
            ->map(fn ($a) => ['id' => $a->user_id, 'name' => $a->actor_name]);

        $page = $query
            ->when($data['group'] ?? null, fn ($q, $g) => $q->where('action', 'like', addcslashes($g, '%_').'.%'))
            ->when($data['user'] ?? null, fn ($q, $u) => $q->where('user_id', $u))
            ->when($data['search'] ?? null, fn ($q, $s) => $q->where('subject_label', 'like', '%'.addcslashes($s, '%_').'%'))
            ->when($data['from'] ?? null, fn ($q, $d) => $q->where('created_at', '>=', $d))
            ->when($data['to'] ?? null, fn ($q, $d) => $q->where('created_at', '<', now()->parse($d)->addDay()))
            ->latest('id')
            ->paginate((int) ($data['per_page'] ?? 50));

        return response()->json([...$page->toArray(), 'actors' => $actors]);
    }
}
