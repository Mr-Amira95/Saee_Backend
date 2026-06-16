@extends('admin.layouts.app')

@section('title', 'Edit – '.$admin->name)

@section('page-title', 'Edit Admin')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.admins.index') }}">Admins</a>
    <span>/</span>
    <a href="{{ route('admin.admins.show', $admin) }}">{{ $admin->name }}</a>
    <span>/</span>
    <span>Edit</span>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.admins.update', $admin) }}" novalidate>
    @csrf
    @method('PUT')

    {{-- Account --}}
    <div class="form-section">
        <div class="form-section-title">Account Details</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="name">Full Name <span class="req">*</span></label>
                <input class="form-input @error('name') is-error @enderror" id="name" type="text" name="name" value="{{ old('name', $admin->name) }}" required>
                @error('name')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email <span class="req">*</span></label>
                <input class="form-input @error('email') is-error @enderror" id="email" type="email" name="email" value="{{ old('email', $admin->email) }}" required>
                @error('email')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="password">New Password <span class="opt">(leave blank to keep)</span></label>
                <input class="form-input @error('password') is-error @enderror" id="password" type="password" name="password">
                @error('password')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm New Password</label>
                <input class="form-input" id="password_confirmation" type="password" name="password_confirmation">
            </div>
            <div class="form-group">
                <label class="form-label" for="phone">Phone</label>
                <input class="form-input" id="phone" type="text" name="phone" value="{{ old('phone', $admin->phone) }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="status">Account Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="active"    {{ old('status', $admin->status) === 'active'    ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ old('status', $admin->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="pending"   {{ old('status', $admin->status) === 'pending'   ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="department">Department</label>
                <input class="form-input" id="department" type="text" name="department" value="{{ old('department', $admin->adminProfile?->department) }}">
            </div>
            <div class="form-group" style="grid-column: span 2">
                <label class="form-label" for="notes">Internal Notes</label>
                <textarea class="form-textarea" id="notes" name="notes" rows="3">{{ old('notes', $admin->adminProfile?->notes) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Permissions (only for non-superadmin) --}}
    @if($admin->role !== 'superadmin')
    <div class="form-section">
        <div class="form-section-title">Permissions</div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <p style="font-size: .82rem; color: var(--text-sub);">Check all permissions this admin should have. Existing permissions will be replaced.</p>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn-secondary" style="font-size: .75rem; padding: 5px 10px;" onclick="document.querySelectorAll('.perm-groups input[type=checkbox]').forEach(c=>c.checked=true)">Select All</button>
                <button type="button" class="btn-secondary" style="font-size: .75rem; padding: 5px 10px;" onclick="document.querySelectorAll('.perm-groups input[type=checkbox]').forEach(c=>c.checked=false)">Clear All</button>
            </div>
        </div>
        <div class="perm-groups">
            @foreach($allPermissions as $group => $perms)
            <div>
                <div class="perm-group-title">{{ ucwords(str_replace('_', ' ', $group)) }}</div>
                <div class="perm-grid">
                    @foreach($perms as $perm)
                    <label class="perm-item">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                            {{ in_array($perm->id, old('permissions', $grantedIds)) ? 'checked' : '' }}>
                        <div>
                            <div class="perm-name">{{ $perm->display_name }}</div>
                            @if($perm->description)
                                <div class="perm-desc">{{ $perm->description }}</div>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="form-actions">
        <a href="{{ route('admin.admins.show', $admin) }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">Save Changes</button>
    </div>
</form>
@endsection
