@extends('layouts.admin')
@section('title', 'Edit ' . $sectionData['name'])

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="h4 mb-1">
                <i class="{{ $sectionData['icon'] }} me-2"></i>
                {{ $sectionData['name'] }}
            </h1>
            <p class="text-muted small mb-0">Edit {{ strtolower($sectionData['name']) }} content</p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-accent btn-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i>Preview
        </a>
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('admin.content.update', $section) }}" method="POST">
    @csrf
    @method('PUT')

    @foreach($sectionData['groups'] as $groupName => $fields)
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-collection me-2"></i>{{ $groupName }}
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($fields as $field)
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">{{ $field['label'] }}</label>
                            @if($field['type'] === 'textarea')
                                <textarea name="{{ $field['key'] }}" 
                                          class="form-control" 
                                          rows="3">{{ old($field['key'], $values[$field['key']] ?? '') }}</textarea>
                            @else
                                <input type="text" 
                                       name="{{ $field['key'] }}" 
                                       class="form-control"
                                       value="{{ old($field['key'], $values[$field['key']] ?? '') }}">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-end mb-5">
        <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
        <button type="submit" class="btn btn-accent">
            <i class="bi bi-save me-1"></i>Save {{ $sectionData['name'] }}
        </button>
    </div>
</form>
@endsection
