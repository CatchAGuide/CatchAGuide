@extends('admin.layouts.app')

@section('title', 'Finance Analytics')

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
            </div>

            <div class="row row-sm">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-muted">
                                Analytics is coming next. For now, please use the Invoice overview for consolidated finance tracking.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

