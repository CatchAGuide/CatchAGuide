@extends('admin.layouts.app')

@section('title', 'Newsletter subscribers')

@section('content')
    <div class="side-app">

        <div class="main-container container-fluid">

            <div class="page-header">
                <h1 class="page-title">Newsletter subscribers</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Communications</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Newsletter subscribers</li>
                    </ol>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {!! session('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row row-sm mb-3">
                <div class="col-sm-6 col-md-4 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-order">
                                <h6 class="mb-2">Total subscribers</h6>
                                <h2 class="text-end"><i class="fe fe-mail icon-size float-start text-primary"></i><span>{{ $totalCount }}</span></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h3 class="card-title mb-0">All newsletter emails</h3>
                            <form method="GET" action="{{ route('admin.newsletter-subscribers.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search email…" style="min-width: 200px;">
                                <select name="language" class="form-select form-select-sm" style="min-width: 120px;">
                                    <option value="">All languages</option>
                                    @foreach($languages as $lang)
                                        <option value="{{ $lang }}" @selected(request('language') === $lang)>{{ strtoupper($lang) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                                @if(request()->filled('search') || request()->filled('language'))
                                    <a href="{{ route('admin.newsletter-subscribers.index') }}" class="btn btn-sm btn-light">Reset</a>
                                @endif
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped text-nowrap border-bottom" id="responsive-datatable">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="border-bottom-0">ID</th>
                                            <th width="40%" class="border-bottom-0">Email</th>
                                            <th width="15%" class="border-bottom-0 text-center">Language</th>
                                            <th width="25%" class="border-bottom-0">Subscribed at</th>
                                            <th width="15%" class="border-bottom-0 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($subscribers as $subscriber)
                                            <tr>
                                                <td>{{ $subscriber->id }}</td>
                                                <td>
                                                    <a href="mailto:{{ $subscriber->email }}">{{ $subscriber->email }}</a>
                                                </td>
                                                <td class="text-center">
                                                    @if($subscriber->language === 'de')
                                                        <label><i class="fi fi-de"></i></label>
                                                    @elseif($subscriber->language === 'en')
                                                        <label><i class="fi fi-gb"></i></label>
                                                    @else
                                                        <label>{{ $subscriber->language ?: '—' }}</label>
                                                    @endif
                                                </td>
                                                <td>{{ optional($subscriber->created_at)->format('F j, Y g:i A') }}</td>
                                                <td class="text-center">
                                                    <form method="POST" action="{{ route('admin.newsletter-subscribers.destroy', $subscriber) }}" onsubmit="return confirm('Remove this email from the newsletter list?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Remove">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No newsletter subscribers found</td>
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
