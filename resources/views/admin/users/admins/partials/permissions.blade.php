{{--
    Renders one checkbox card per admin-panel page. Checking the page checkbox
    reveals its action sub-checkboxes (if any); unchecking hides and clears
    them. Checking any action auto-checks its page checkbox, since an action
    grant is meaningless without page access.

    Expects:
    - $permissions: Permission::where('scope','admin')->get()->groupBy('group')
    - $grantedIds:  array of permission IDs already granted (old() already merged in by caller)
--}}
@php
    $pageOrder = [
        'clients', 'drivers', 'admins', 'orders', 'support', 'ai_conversations',
        'reports', 'cities', 'rejection_reasons', 'finances', 'cms', 'attendance', 'notifications',
    ];
@endphp
<div class="perm-groups perm-page-groups">
    @foreach($pageOrder as $slug)
        @continue(!isset($permissions[$slug]))
        @php
            $perms = $permissions[$slug];
            $pagePerm = $perms->firstWhere('name', $slug);
            $actionPerms = $perms->reject(fn($p) => $p->id === $pagePerm?->id);
        @endphp
        @continue(!$pagePerm)
        <div class="perm-page-card">
            <label class="perm-item perm-page-header">
                <input type="checkbox" class="perm-page-checkbox" name="permissions[]" value="{{ $pagePerm->id }}"
                    {{ in_array($pagePerm->id, $grantedIds) ? 'checked' : '' }}>
                <div>
                    <div class="perm-name">{{ $pagePerm->display_name }}</div>
                    @if($actionPerms->isNotEmpty())
                        <div class="perm-desc">Grants read-only access until specific actions below are checked.</div>
                    @endif
                </div>
            </label>
            @if($actionPerms->isNotEmpty())
            <div class="perm-actions-panel perm-grid">
                @foreach($actionPerms as $perm)
                <label class="perm-item perm-action-item">
                    <input type="checkbox" class="perm-action-checkbox" name="permissions[]" value="{{ $perm->id }}"
                        {{ in_array($perm->id, $grantedIds) ? 'checked' : '' }}>
                    <div class="perm-name">{{ $perm->display_name }}</div>
                </label>
                @endforeach
            </div>
            @endif
        </div>
    @endforeach
</div>

<style>
.perm-page-card { border: 1px solid var(--in-bdr); border-radius: 10px; padding: 12px 14px; }
.perm-actions-panel { margin: 10px 0 0 28px; padding-top: 10px; border-top: 1px solid var(--in-bdr); }
.perm-actions-panel.is-hidden { display: none; }
</style>

<script>
(function() {
    function syncCard(card) {
        var pageCb = card.querySelector('.perm-page-checkbox');
        var panel = card.querySelector('.perm-actions-panel');
        if (!panel) return;
        panel.classList.toggle('is-hidden', !pageCb.checked);
        if (!pageCb.checked) {
            panel.querySelectorAll('input[type=checkbox]').forEach(function(cb) { cb.checked = false; });
        }
    }

    document.querySelectorAll('.perm-page-card').forEach(function(card) {
        var pageCb = card.querySelector('.perm-page-checkbox');
        syncCard(card);
        pageCb.addEventListener('change', function() { syncCard(card); });
        card.querySelectorAll('.perm-action-checkbox').forEach(function(actionCb) {
            actionCb.addEventListener('change', function() {
                if (actionCb.checked) pageCb.checked = true;
                syncCard(card);
            });
        });
    });

    window.syncAllPermCards = function() {
        document.querySelectorAll('.perm-page-card').forEach(syncCard);
    };
})();
</script>
