@extends('admin.layouts.app')

@section('title', 'Terms & Conditions')

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Content</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>
            </div>

            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.terms.create') }}" class="btn btn-primary">Section erstellen</a>
                            </div>
                            <div class="text-muted small">
                                Frontend flag:
                                <strong>{{ config('terms.dynamic_enabled') ? 'ON' : 'OFF' }}</strong>
                                (DYNAMIC_TERMS_ENABLED)
                            </div>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                                    <thead>
                                    <tr>
                                        <th class="wd-10p border-bottom-0">Order</th>
                                        <th class="wd-15p border-bottom-0">Languages</th>
                                        <th class="wd-35p border-bottom-0">Title (DE)</th>
                                        <th class="wd-10p border-bottom-0">Active</th>
                                        <th class="wd-30p border-bottom-0">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($sections as $section)
                                        @php
                                            $de = $section->translations->firstWhere('language', 'de');
                                            $en = $section->translations->firstWhere('language', 'en');
                                        @endphp
                                        <tr>
                                            <td>{{ $section->sort_order }}</td>
                                            <td>
                                                @if($de)
                                                    <span class="fi fi-de" title="German"></span>
                                                @endif
                                                @if($en)
                                                    <span class="fi fi-gb ms-1" title="English"></span>
                                                @endif
                                            </td>
                                            <td>{{ $de->title ?? ($en->title ?? '—') }}</td>
                                            <td>
                                                @if($section->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <form action="{{ route('admin.terms.reorder') }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $section->id }}">
                                                        <input type="hidden" name="direction" value="up">
                                                        <button type="submit" class="btn btn-sm btn-light" title="Move up">
                                                            <i class="fa fa-arrow-up"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.terms.reorder') }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $section->id }}">
                                                        <input type="hidden" name="direction" value="down">
                                                        <button type="submit" class="btn btn-sm btn-light" title="Move down">
                                                            <i class="fa fa-arrow-down"></i>
                                                        </button>
                                                    </form>
                                                    <a href="{{ route('admin.terms.edit', $section) }}" class="btn btn-sm btn-secondary">
                                                        <i class="fa fa-pen"></i>
                                                    </a>
                                                    <form action="{{ route('admin.terms.destroy', $section) }}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('Delete this section?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-primary">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No T&C sections yet. Create one or run TermsSectionSeeder.</td>
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
