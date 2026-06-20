@extends('admin.layouts.app')

@section('title', 'Open Support Ticket')
@section('page-title', 'Support Tickets')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.support.index') }}" style="color: var(--text-sub);">Support Center</a>
    <span class="sep">/</span>
    <span class="current">Open Ticket</span>
@endsection

@section('head')
<style>
    /* ── Searchable Dropdown ── */
    .sd-wrap {
        position: relative;
    }

    .sd-trigger {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 14px;
        background: var(--in-bg);
        border: 1px solid var(--in-bdr);
        border-radius: 8px;
        color: var(--text);
        font-size: .875rem;
        cursor: pointer;
        transition: border-color .15s;
        text-align: left;
        font-family: inherit;
    }

    .sd-trigger:hover,
    .sd-trigger.open {
        border-color: rgba(220, 38, 38, .45);
    }

    .sd-trigger-label {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sd-trigger-label.placeholder {
        color: var(--text-dim);
    }

    .sd-trigger-sub {
        font-size: .72rem;
        color: var(--text-dim);
        margin-top: 1px;
    }

    .sd-chevron {
        flex-shrink: 0;
        width: 16px;
        height: 16px;
        color: var(--text-dim);
        transition: transform .2s;
    }

    .sd-trigger.open .sd-chevron {
        transform: rotate(180deg);
    }

    .sd-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        background: var(--bg-2, #0d1020);
        border: 1px solid var(--bdr);
        border-radius: 10px;
        z-index: 200;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0,0,0,.45);
        display: none;
    }

    .sd-dropdown.open {
        display: block;
        animation: sdFadeIn .12s ease;
    }

    @keyframes sdFadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .sd-search-wrap {
        padding: 10px 10px 8px;
        border-bottom: 1px solid var(--bdr);
    }

    .sd-search {
        width: 100%;
        background: rgba(255,255,255,.05);
        border: 1px solid var(--bdr);
        border-radius: 6px;
        padding: 7px 12px 7px 32px;
        color: var(--text);
        font-size: .82rem;
        font-family: inherit;
        outline: none;
        transition: border-color .15s;
        box-sizing: border-box;
    }

    .sd-search:focus {
        border-color: rgba(220,38,38,.4);
    }

    .sd-search-icon {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-dim);
        pointer-events: none;
    }

    .sd-list {
        max-height: 220px;
        overflow-y: auto;
        padding: 4px 0;
    }

    .sd-list::-webkit-scrollbar { width: 4px; }
    .sd-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,.08); border-radius: 2px; }

    .sd-option {
        display: flex;
        flex-direction: column;
        padding: 9px 14px;
        cursor: pointer;
        transition: background .1s;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        font-family: inherit;
        color: var(--text-sub);
        font-size: .84rem;
    }

    .sd-option:hover,
    .sd-option.focused {
        background: rgba(220,38,38,.07);
        color: var(--text);
    }

    .sd-option.selected {
        background: rgba(220,38,38,.1);
        color: #fff;
    }

    .sd-option-sub {
        font-size: .7rem;
        color: var(--text-dim);
        margin-top: 2px;
    }

    .sd-option.selected .sd-option-sub {
        color: rgba(255,255,255,.5);
    }

    .sd-clear {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: rgba(255,255,255,.1);
        border: none;
        color: var(--text-dim);
        cursor: pointer;
        font-size: .75rem;
        flex-shrink: 0;
        transition: background .15s;
        padding: 0;
    }

    .sd-clear:hover {
        background: rgba(220,38,38,.3);
        color: #fff;
    }

    .sd-empty {
        padding: 20px;
        text-align: center;
        font-size: .8rem;
        color: var(--text-dim);
    }

    /* hidden real selects for form submission */
    .sd-hidden { display: none !important; }
</style>
@endsection

@section('content')
<div style="max-width: 640px; margin: 0 auto;">

    <div class="card" style="padding: 32px;">
        <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--bdr);">
            Open Support Ticket
        </h2>

        <form action="{{ route('admin.support.store') }}" method="POST">
            @csrf

            {{-- ── User Searchable Dropdown ── --}}
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Select Client / Driver <span class="req">*</span></label>

                <select name="user_id" id="user_id" class="sd-hidden" required>
                    <option value="">-- Choose User --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}"
                            data-sub="{{ str_replace('_', ' ', ucfirst($u->role)) }}"
                            {{ old('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>

                <div class="sd-wrap" id="sd-user">
                    <button type="button" class="sd-trigger" id="sd-user-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="sd-trigger-label placeholder" id="sd-user-label">Choose a user…</span>
                        <svg class="sd-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6l4 4 4-4"/></svg>
                    </button>
                    <div class="sd-dropdown" id="sd-user-dropdown" role="listbox">
                        <div class="sd-search-wrap" style="position:relative;">
                            <svg class="sd-search-icon" width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="6.5" cy="6.5" r="4.5"/><path d="M10.5 10.5l3 3"/></svg>
                            <input type="text" class="sd-search" id="sd-user-search" placeholder="Search by name or role…" autocomplete="off">
                        </div>
                        <div class="sd-list" id="sd-user-list" role="presentation">
                            @foreach($users as $u)
                                <button type="button" class="sd-option {{ old('user_id') == $u->id ? 'selected' : '' }}"
                                    data-value="{{ $u->id }}"
                                    data-label="{{ $u->name }}"
                                    data-sub="{{ str_replace('_', ' ', ucfirst($u->role)) }}"
                                    data-search="{{ strtolower($u->name . ' ' . $u->role) }}"
                                    role="option">
                                    {{ $u->name }}
                                    <span class="sd-option-sub">{{ str_replace('_', ' ', ucfirst($u->role)) }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                @error('user_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- ── Order Searchable Dropdown ── --}}
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Related Order <span style="color: var(--text-dim); font-weight: 400;">(Optional)</span></label>

                <select name="order_id" id="order_id" class="sd-hidden">
                    <option value="">-- Select Order --</option>
                    @foreach($orders as $o)
                        <option value="{{ $o->id }}"
                            data-sub="{{ $o->receiver_name }} · {{ $o->receiver_phone }}"
                            {{ old('order_id') == $o->id ? 'selected' : '' }}>
                            #{{ $o->order_number }}
                        </option>
                    @endforeach
                </select>

                <div class="sd-wrap" id="sd-order">
                    <button type="button" class="sd-trigger" id="sd-order-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="sd-trigger-label placeholder" id="sd-order-label">No order selected</span>
                        <svg class="sd-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6l4 4 4-4"/></svg>
                    </button>
                    <div class="sd-dropdown" id="sd-order-dropdown" role="listbox">
                        <div class="sd-search-wrap" style="position:relative;">
                            <svg class="sd-search-icon" width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="6.5" cy="6.5" r="4.5"/><path d="M10.5 10.5l3 3"/></svg>
                            <input type="text" class="sd-search" id="sd-order-search" placeholder="Search by order number or name…" autocomplete="off">
                        </div>
                        <div class="sd-list" id="sd-order-list" role="presentation">
                            <button type="button" class="sd-option {{ old('order_id') == '' ? 'selected' : '' }}"
                                data-value=""
                                data-label=""
                                data-sub=""
                                data-search=""
                                role="option">
                                <span style="color: var(--text-dim); font-style: italic;">No order</span>
                            </button>
                            @foreach($orders as $o)
                                <button type="button" class="sd-option {{ old('order_id') == $o->id ? 'selected' : '' }}"
                                    data-value="{{ $o->id }}"
                                    data-label="#{{ $o->order_number }}"
                                    data-sub="{{ $o->receiver_name }} · {{ $o->receiver_phone }}"
                                    data-search="{{ strtolower('#' . $o->order_number . ' ' . $o->receiver_name . ' ' . $o->receiver_phone) }}"
                                    role="option">
                                    #{{ $o->order_number }}
                                    <span class="sd-option-sub">{{ $o->receiver_name }} · {{ $o->receiver_phone }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Category & Priority ── --}}
            <div class="form-grid-2" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label">Category <span class="req">*</span></label>
                    <select name="category" class="form-input" required>
                        <option value="general"        {{ old('category') === 'general'        ? 'selected' : '' }}>General Inquiry</option>
                        <option value="delivery_issue" {{ old('category') === 'delivery_issue' ? 'selected' : '' }}>Delivery Issue</option>
                        <option value="financial"      {{ old('category') === 'financial'      ? 'selected' : '' }}>Financial / COD</option>
                        <option value="complaint"      {{ old('category') === 'complaint'      ? 'selected' : '' }}>Complaint</option>
                    </select>
                    @error('category')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Priority <span class="req">*</span></label>
                    <select name="priority" class="form-input" required>
                        <option value="low"    {{ old('priority') === 'low'    ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high"   {{ old('priority') === 'high'   ? 'selected' : '' }}>High</option>
                    </select>
                    @error('priority')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Subject Title <span class="req">*</span></label>
                <input type="text" name="title" class="form-input" placeholder="e.g. Shipping Delay Inquiry" value="{{ old('title') }}" required>
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group" style="margin-bottom: 28px;">
                <label class="form-label">Initial Message <span class="req">*</span></label>
                <textarea name="message" class="form-input" rows="5" placeholder="Describe the issue or start the conversation..." required style="height: auto; resize: none;">{{ old('message') }}</textarea>
                @error('message')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid var(--bdr);">
                <a href="{{ route('admin.support.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create Ticket</button>
            </div>
        </form>
    </div>

</div>
@endsection

@section('scripts')
<script>
(function () {
    // Restore labels for old() repopulation on validation error
    function initDropdown(cfg) {
        const trigger   = document.getElementById(cfg.triggerId);
        const dropdown  = document.getElementById(cfg.dropdownId);
        const search    = document.getElementById(cfg.searchId);
        const list      = document.getElementById(cfg.listId);
        const labelEl   = document.getElementById(cfg.labelId);
        const hidden    = document.getElementById(cfg.hiddenId);
        const options   = Array.from(list.querySelectorAll('.sd-option'));

        // Sync label from any server-side old() selection
        const preSelected = list.querySelector('.sd-option.selected');
        if (preSelected && preSelected.dataset.value) {
            labelEl.textContent = preSelected.dataset.label;
            labelEl.classList.remove('placeholder');
        }

        function open() {
            trigger.classList.add('open');
            dropdown.classList.add('open');
            trigger.setAttribute('aria-expanded', 'true');
            search.value = '';
            filterOptions('');
            setTimeout(() => search.focus(), 30);
        }

        function close() {
            trigger.classList.remove('open');
            dropdown.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        function select(opt) {
            options.forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
            hidden.value = opt.dataset.value;

            if (opt.dataset.value) {
                labelEl.textContent = opt.dataset.label;
                labelEl.classList.remove('placeholder');
            } else {
                labelEl.textContent = cfg.placeholder;
                labelEl.classList.add('placeholder');
            }
            close();
        }

        function filterOptions(q) {
            const term = q.toLowerCase();
            let visible = 0;
            options.forEach(opt => {
                const match = !term || (opt.dataset.search || '').includes(term);
                opt.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            let empty = list.querySelector('.sd-empty');
            if (!visible) {
                if (!empty) {
                    empty = document.createElement('div');
                    empty.className = 'sd-empty';
                    empty.textContent = 'No results found';
                    list.appendChild(empty);
                }
            } else if (empty) {
                empty.remove();
            }
        }

        trigger.addEventListener('click', () => {
            dropdown.classList.contains('open') ? close() : open();
        });

        search.addEventListener('input', () => filterOptions(search.value));

        options.forEach(opt => {
            opt.addEventListener('click', () => select(opt));
        });

        // Close on outside click
        document.addEventListener('click', e => {
            if (!trigger.closest('.sd-wrap').contains(e.target)) close();
        });

        // Keyboard: Escape closes
        trigger.closest('.sd-wrap').addEventListener('keydown', e => {
            if (e.key === 'Escape') close();
        });
    }

    initDropdown({
        triggerId:  'sd-user-trigger',
        dropdownId: 'sd-user-dropdown',
        searchId:   'sd-user-search',
        listId:     'sd-user-list',
        labelId:    'sd-user-label',
        hiddenId:   'user_id',
        placeholder: 'Choose a user…',
    });

    initDropdown({
        triggerId:  'sd-order-trigger',
        dropdownId: 'sd-order-dropdown',
        searchId:   'sd-order-search',
        listId:     'sd-order-list',
        labelId:    'sd-order-label',
        hiddenId:   'order_id',
        placeholder: 'No order selected',
    });
})();
</script>
@endsection
