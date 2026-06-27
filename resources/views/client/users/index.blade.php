@extends('client.layouts.app')
@section('title', __('Team'))
@section('page-title', __('Team'))

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <h1 style="font-size:1.35rem;font-weight:800;">{{ __('Team Members') }}</h1>
    <a href="{{ route('client.users.create') }}" class="btn-primary" style="display:inline-flex;align-items:center;gap:7px;text-decoration:none;">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        {{ __('Add User') }}
    </a>
</div>

@if(session('success'))
    <div class="flash flash-ok" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif

<div class="card" style="padding:0;overflow:hidden;">
    @if($employees->count())
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Job Title') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Joined') }}</th>
                    <th style="text-align:right;">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $employee)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:50%;background:rgba(220,38,38,.15);color:#fca5a5;font-size:.78rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                {{ strtoupper(substr($employee->user->name, 0, 1)) }}
                            </div>
                            <span style="font-weight:600;">{{ $employee->user->name }}</span>
                        </div>
                    </td>
                    <td style="color:var(--text-sub);font-size:.85rem;">
                        @if($employee->user->phone)
                            {{ ($employee->user->phone_country_code ?? '+962') . ' ' . $employee->user->phone }}
                        @else
                            —
                        @endif
                    </td>
                    <td style="color:var(--text-sub);font-size:.85rem;">{{ $employee->user->email ?? '—' }}</td>
                    <td style="font-size:.85rem;">{{ $employee->job_title ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $employee->user->status === 'active' ? 'badge-success' : 'badge-neutral' }}" style="font-size:.72rem;">
                            {{ ucfirst($employee->user->status) }}
                        </span>
                    </td>
                    <td style="color:var(--text-dim);font-size:.82rem;white-space:nowrap;">{{ $employee->created_at->format('d M Y') }}</td>
                    <td style="text-align:right;white-space:nowrap;">
                        <a href="{{ route('client.users.edit', $employee->id) }}" class="btn-secondary" style="font-size:.78rem;padding:5px 12px;text-decoration:none;display:inline-block;">
                            {{ __('Edit') }}
                        </a>
                        <form method="POST" action="{{ route('client.users.destroy', $employee->id) }}" style="display:inline;" onsubmit="return confirm('{{ __('Are you sure you want to delete this user? This action cannot be undone.') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger" style="font-size:.78rem;padding:5px 12px;">{{ __('Remove') }}</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($employees->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--bdr);">
        {{ $employees->links() }}
    </div>
    @endif

    @else
    <div style="padding:60px 20px;text-align:center;">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="color:var(--text-dim);margin-bottom:12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <p style="color:var(--text-dim);font-size:.9rem;margin:0;">{{ __('No team members yet.') }}</p>
        <a href="{{ route('client.users.create') }}" style="display:inline-block;margin-top:12px;color:var(--red-lt);font-size:.85rem;text-decoration:none;">{{ __('Add your first user →') }}</a>
    </div>
    @endif
</div>

@endsection
