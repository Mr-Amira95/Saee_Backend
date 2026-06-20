@extends('admin.layouts.app')

@section('title', 'Open Support Ticket')
@section('page-title', 'Support Tickets')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.support.index') }}" style="color: var(--text-sub);">Support Center</a>
    <span class="sep">/</span>
    <span class="current">Open Ticket</span>
@endsection

@section('content')
<div style="max-width: 640px; margin: 0 auto;">

    <div class="card" style="padding: 32px;">
        <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--bdr);">
            Open Support Ticket
        </h2>

        <form action="{{ route('admin.support.store') }}" method="POST">
            @csrf

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Select Client / Driver <span class="req">*</span></label>
                <select name="user_id" class="form-input" required>
                    <option value="">-- Choose User --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ str_replace('_', ' ', $u->role) }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Related Order <span style="color: var(--text-dim); font-weight: 400;">(Optional)</span></label>
                <select name="order_id" class="form-input">
                    <option value="">-- Select Order --</option>
                    @foreach($orders as $o)
                        <option value="{{ $o->id }}" {{ old('order_id') == $o->id ? 'selected' : '' }}>
                            #{{ $o->order_number }} — {{ $o->receiver_name }} ({{ $o->receiver_phone }})
                        </option>
                    @endforeach
                </select>
            </div>

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
