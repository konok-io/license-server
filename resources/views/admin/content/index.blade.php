@extends('layouts.admin')
@section('title', 'Content Settings')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-1">Content Settings</h1>
        <p class="text-muted small mb-0">Manage all website content from one place</p>
    </div>
    <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-accent btn-sm">
        <i class="bi bi-box-arrow-up-right me-1"></i>Preview
    </a>
</div>

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-3">
        <div class="list-group">
            @foreach($contentGroups as $key => $group)
                <a href="{{ route('admin.content.index', ['tab' => $key]) }}"
                   class="list-group-item list-group-item-action d-flex align-items-center {{ $activeTab === $key ? 'active' : '' }}">
                    <i class="{{ $group['icon'] }} me-3"></i>
                    {{ $group['name'] }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="col-md-9">
        @php
            $currentGroup = $contentGroups[$activeTab] ?? null;
        @endphp

        @if($currentGroup)
            <form action="{{ route('admin.content.update') }}" method="POST">
                @csrf
                <input type="hidden" name="tab" value="{{ $activeTab }}">

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0">
                        <i class="{{ $currentGroup['icon'] }} me-2"></i>
                        {{ $currentGroup['name'] }}
                    </h5>
                    <button type="submit" class="btn btn-accent">
                        <i class="bi bi-save me-1"></i>Save Changes
                    </button>
                </div>

                @foreach($currentGroup['groups'] as $sectionName => $fields)
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="bi bi-collection me-2"></i>{{ $sectionName }}
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($fields as $field)
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">{{ $field['label'] }}</label>
                                        @if($field['type'] === 'textarea')
                                            <textarea name="{{ $field['key'] }}" class="form-control" rows="3">{{ old($field['key'], $values[$field['key']] ?? '') }}</textarea>
                                        @else
                                            <input type="text" name="{{ $field['key'] }}" class="form-control" value="{{ old($field['key'], $values[$field['key']] ?? '') }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-end mb-5">
                    <button type="submit" class="btn btn-accent">
                        <i class="bi bi-save me-1"></i>Save {{ $currentGroup['name'] }} Changes
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
