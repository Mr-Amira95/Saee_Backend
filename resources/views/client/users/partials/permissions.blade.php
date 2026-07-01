@php
    $pageIcons = [
        'orders'          => '📦',
        'support'         => '🎧',
        'payout_invoices' => '🧾',
        'billing'         => '💳',
        'reports'         => '📊',
        'team'            => '👥',
        'account'         => '⚙️',
        'ai_assistant'    => '🤖',
    ];
@endphp

<style>
    .perm-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
    @media (max-width:600px) { .perm-grid { grid-template-columns: 1fr; } }
    .perm-item {
        display: flex; align-items: center; gap: 11px;
        padding: 12px 14px; background: var(--in-bg);
        border: 1px solid var(--in-bdr); border-radius: 10px;
        cursor: pointer; transition: border-color .15s, background .15s; user-select: none;
    }
    .perm-item:hover { border-color: rgba(220,38,38,.3); }
    .perm-item.is-checked { border-color: rgba(220,38,38,.45); background: rgba(220,38,38,.07); }
    .perm-item input[type=checkbox] { accent-color: var(--red); width: 16px; height: 16px; flex-shrink: 0; }
    .perm-item-icon { font-size: 1rem; width: 26px; height: 26px; border-radius: 7px; display: flex; align-items: center; justify-content: center; background: rgba(220,38,38,.1); flex-shrink: 0; }
    .perm-item-lbl { font-size: .87rem; color: var(--text); font-weight: 500; }
</style>

<div class="card" style="margin-bottom:20px; max-width:650px;">
    <div style="font-size:.76rem;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px;">{{ __('Page Permissions') }}</div>
    <p style="font-size:.8rem;color:var(--text-sub);margin-bottom:16px;">{{ __('Choose which pages this team member can access. Access to a page includes everything within it.') }}</p>

    <div class="perm-grid">
        @foreach($permissions as $perm)
        @php $isChecked = $checkedIds->contains($perm->id); @endphp
        <label class="perm-item {{ $isChecked ? 'is-checked' : '' }}">
            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" {{ $isChecked ? 'checked' : '' }}
                   onchange="this.closest('.perm-item').classList.toggle('is-checked', this.checked)">
            <span class="perm-item-icon">{{ $pageIcons[$perm->name] ?? '🔑' }}</span>
            <span class="perm-item-lbl">{{ $perm->display_name }}</span>
        </label>
        @endforeach
    </div>
</div>
