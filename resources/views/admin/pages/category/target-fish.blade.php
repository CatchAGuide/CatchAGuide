@extends('admin.layouts.app')

@section('title', __('admin.category_pages.dimensions.target_fish'))

@section('custom_style')
<link rel="stylesheet" href="{{ asset('css/admin-category-pages.css') }}">
@endsection

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">{{ __('admin.category_pages.dimensions.target_fish') }}</h1>
                <div>
                    <a href="{{ route('admin.category.hub') }}" class="btn btn-outline-primary btn-sm">{{ __('admin.category_pages.editor.back_to_hub') }}</a>
                </div>
            </div>

            <p class="text-muted">{{ __('admin.category_pages.index.intro') }}</p>

            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <table class="table blog-table table-bordered table-striped text-nowrap border-bottom">
                                <thead>
                                <tr>
                                    <th class="border-bottom-0 text-center">{{ __('admin.category_pages.index.languages') }}</th>
                                    <th class="border-bottom-0">{{ __('admin.category_pages.index.name') }}</th>
                                    <th class="border-bottom-0 text-center">{{ __('admin.category_pages.scopes_label') }}</th>
                                    <th class="border-bottom-0">{{ __('admin.category_pages.index.actions') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $row)
                                        <tr class="{{ empty($row->has_any_content) ? 'category-index__row--empty' : '' }}">
                                            @include('admin.pages.category.partials.locale-flag-cells', ['locales' => $row->filled_locales ?? []])
                                            <td>{{ $row->name }}</td>
                                            @include('admin.pages.category.partials.scope-status-chips', [
                                                'scopes' => $scopes,
                                                'completeness' => $row->scope_completeness,
                                            ])
                                            <td>
                                                <a href="{{ route('admin.category.target-fish.edit', $row->id) }}" class="btn btn-sm btn-secondary"><i class="fa fa-edit"></i></a>
                                                @if($row->categoryPage && $row->categoryPage->is_favorite == 1)
                                                    <button class="btn btn-sm btn-warning toggle-favorite" data-id="{{ $row->id }}" data-status="1"><i class="fa fa-star text-white"></i></button>
                                                @elseif($row->categoryPage)
                                                    <button class="btn btn-sm btn-light toggle-favorite" data-id="{{ $row->id }}" data-status="0"><i class="fa fa-star"></i></button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $rows->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_after')
<script>
    $(document).ready(function() {
        $('.toggle-favorite').on('click', function() {
            const button = $(this);
            const id = button.data('id');
            const currentStatus = button.data('status');
            const newStatus = currentStatus === 1 ? 0 : 1;

            $.ajax({
                url: "{{ route('admin.category.target-fish.toggle-favorite') }}",
                type: "POST",
                data: {
                    id: id,
                    status: newStatus,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        if (newStatus === 1) {
                            button.removeClass('btn-light').addClass('btn-warning');
                            button.html('<i class="fa fa-star text-white"></i>');
                        } else {
                            button.removeClass('btn-warning').addClass('btn-light');
                            button.html('<i class="fa fa-star"></i>');
                        }
                        button.data('status', newStatus);
                    }
                },
            });
        });
    });
</script>
@endsection
