@extends('admin.layouts.app')

@section('title', __('admin.vacation_testimonials.page_title'))

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">{{ __('admin.vacation_testimonials.page_title') }}</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('admin.vacation_testimonials.breadcrumb') }}</li>
                    </ol>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="card-title mb-1">{{ __('admin.vacation_testimonials.table_title') }}</h3>
                                <p class="text-muted mb-0 small">{{ __('admin.vacation_testimonials.subtitle') }}</p>
                            </div>
                            <a href="{{ route('admin.vacation-testimonials.create') }}" class="btn btn-primary">
                                {{ __('admin.vacation_testimonials.add') }}
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('admin.vacation_testimonials.th_id') }}</th>
                                            <th>{{ __('admin.vacation_testimonials.th_quote') }}</th>
                                            <th>{{ __('admin.vacation_testimonials.th_author') }}</th>
                                            <th>{{ __('admin.vacation_testimonials.th_rating') }}</th>
                                            <th>{{ __('admin.vacation_testimonials.th_listing') }}</th>
                                            <th>{{ __('admin.vacation_testimonials.th_sort') }}</th>
                                            <th>{{ __('admin.vacation_testimonials.th_published') }}</th>
                                            <th style="width:140px">{{ __('admin.vacation_testimonials.th_actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($testimonials as $testimonial)
                                            <tr>
                                                <td>#{{ $testimonial->id }}</td>
                                                <td>{{ \Illuminate\Support\Str::limit($testimonial->quote, 60) }}</td>
                                                <td>{{ $testimonial->author }}</td>
                                                <td>{{ number_format((float) $testimonial->rating, 1) }}</td>
                                                <td>
                                                    @if($testimonial->listing_title)
                                                        {{ \Illuminate\Support\Str::limit($testimonial->listing_title, 30) }}
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ $testimonial->sort_order }}</td>
                                                <td>
                                                    @if($testimonial->is_published)
                                                        <span class="badge bg-success">{{ __('admin.vacation_testimonials.published') }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ __('admin.vacation_testimonials.unpublished') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.vacation-testimonials.edit', $testimonial) }}" class="btn btn-sm btn-primary">Edit</a>
                                                    <form action="{{ route('admin.vacation-testimonials.destroy', $testimonial) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('admin.vacation_testimonials.delete_confirm') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">{{ __('admin.vacation_testimonials.empty') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
