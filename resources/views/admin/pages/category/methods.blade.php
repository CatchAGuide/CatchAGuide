@extends('admin.layouts.app')

@section('title', 'Methods')

@section('content')
    <style>
        .frm-btn-delete {
            display: contents;
        }
    </style>
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Methods</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="#">Categories</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Methods</li>
                    </ol>
                </div>

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <table class="table blog-table table-bordered table-striped text-nowrap border-bottom">
                                <thead>
                                <tr>
                                    <th width="10%" class="border-bottom-0 text-center">Language</th>
                                    <th width="30%" class="border-bottom-0">Name</th>
                                    <th width="15%" class="border-bottom-0">Aktionen</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $row)
                                        <tr>
                                            <td class="text-center">
                                                @if($row->categoryPage && $row->categoryPage->language)
                                                    @foreach($row->categoryPage->language as $language)
                                                        @if($language->language == 'de')
                                                            <label><i class="fi fi-de"></i></label> 
                                                        @elseif($language->language == 'en')
                                                            <label><i class="fi fi-gb"></i></label>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{ $row->name }}</td>
                                            <td>
                                                <a href="{{ route('admin.category.methods.edit', $row->id) }}" class="btn btn-sm btn-secondary"><i class="fa fa-edit"></i></a>
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
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

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
                url: "{{ route('admin.category.methods.toggle-favorite') }}",
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
                error: function(xhr) {
                    console.error('Error updating favorite status');
                }
            });
        });
    });
</script>
@endsection
