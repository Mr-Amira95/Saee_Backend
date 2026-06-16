@extends('admin.layouts.app')

@section('title', 'Add Admin')

@section('page-title', 'Add Admin')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.admins.index') }}">Admins</a>
    <span>/</span>
    <span>Add Admin</span>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.admins.store') }}" novalidate>
    @csrf

    {{-- Account --}}
    <div class="form-section">
        <div class="form-section-title">Account Details</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="name">Full Name <span class="req">*</span></label>
                <input class="form-input @error('name') is-error @enderror" id="name" type="text" name="name" value="{{ old('name') }}" required>
                @error('name')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email <span class="req">*</span></label>
                <input class="form-input @error('email') is-error @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required>
                @error('email')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password <span class="req">*</span></label>
                <input class="form-input @error('password') is-error @enderror" id="password" type="password" name="password" required>
                @error('password')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm Password <span class="req">*</span></label>
                <input class="form-input" id="password_confirmation" type="password" name="password_confirmation" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="phone">Phone</label>
                <input class="form-input" id="phone" type="text" name="phone" value="{{ old('phone') }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="department">Department</label>
                <input class="form-input" id="department" type="text" name="department" value="{{ old('department') }}" placeholder="e.g. Operations, Finance…">
            </div>
            <div class="form-group" style="grid-column: span 2">
                <label class="form-label" for="notes">Internal Notes</label>
                <textarea class="form-textarea" id="notes" name="notes" rows="3" placeholder="Notes visible only to superadmins…">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Permissions --}}
    <div class="form-section">
        <div class="form-section-title">Permissions</div>
        <p style="font-size: .82rem; color: var(--text-sub); margin-bottom: 16px;">Grant specific permissions to this admin. Superadmins always have full access regardless.</p>
        <div class="perm-groups">
            @foreach($permissions as $group => $perms)
            <div>
                <div class="perm-group-title">{{ ucwords(str_replace('_', ' ', $group)) }}</div>
                <div class="perm-grid">
                    @foreach($perms as $perm)
                    <label class="perm-item">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                            {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}>
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

    <div class="form-actions">
        <a href="{{ route('admin.admins.index') }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">Create Admin</button>
    </div>
</form>
@endsection
