@extends('layouts.admin')
@section('title', 'Site Settings')

@section('content')

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="text-muted small">
        <i class="bi bi-info-circle me-1"></i>
        Everything here controls the public homepage. Changes are live immediately after saving.
    </div>
    <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-accent btn-sm">
        <i class="bi bi-box-arrow-up-right me-1"></i>Preview Homepage
    </a>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf
    @method('PUT')

    @foreach($groups as $groupName => $fields)
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-collection me-2"></i>{{ $groupName }}
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($fields as $field)
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">{{ $field['label'] }}</label>
                            @if($field['type'] === 'textarea')
                                <textarea name="{{ $field['key'] }}" class="form-control" rows="2">{{ old($field['key'], $values[$field['key']] ?? '') }}</textarea>
                            @else
                                <input type="text" name="{{ $field['key'] }}" class="form-control"
                                       value="{{ old($field['key'], $values[$field['key']] ?? '') }}">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-accent">
            <i class="bi bi-save me-1"></i>Save Settings
        </button>
    </div>
</form>

@endsection
