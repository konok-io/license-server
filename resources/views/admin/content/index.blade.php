@extends('layouts.admin')
@section('title', 'Content Management')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-1">Content Management</h1>
        <p class="text-muted small mb-0">Manage all website content sections</p>
    </div>
    <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-accent btn-sm">
        <i class="bi bi-box-arrow-up-right me-1"></i>Preview
    </a>
</div>

<div class="row g-4">
    @foreach($sections as $key => $section)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('admin.content.show', $key) }}" class="text-decoration-none">
                <div class="card h-100 content-card">
                    <div class="card-body text-center">
                        <div class="content-icon mb-3">
                            <i class="{{ $section['icon'] }}"></i>
                        </div>
                        <h5 class="card-title mb-2">{{ $section['name'] }}</h5>
                        <p class="text-muted small mb-0">
                            {{ count($section['groups']) }} section(s)
                        </p>
                    </div>
                    <div class="card-footer bg-transparent text-center">
                        <span class="btn btn-sm btn-accent">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </span>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<style>
.content-card {
    transition: all 0.2s ease;
    border: 1px solid var(--border-color);
}
.content-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: var(--accent-gold);
}
.content-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--accent-gold-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}
.content-icon i {
    font-size: 1.5rem;
    color: var(--accent-gold);
}
</style>
@endsection
