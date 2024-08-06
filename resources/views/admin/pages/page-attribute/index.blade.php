@extends('admin.layouts.app')

@section('title', 'Page Attribute')

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAttribute"><i class="fa fa-plus" ></i> Page Attribute</button>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="attributeTable" class="table">
                                <thead>
                                    <tr>
                                        <th class="wd-15p border-bottom-0">Page</th>
                                        <th class="wd-10p border-bottom-0">Domain</th>
                                        <th class="wd-10p border-bottom-0">Uri</th>
                                        <th class="wd-10p border-bottom-0">Type</th>
                                        <th class="wd-10p border-bottom-0">Content</th>
                                        <th class="wd-10p border-bottom-0">Deleted At</th>
                                        <th class="wd-25p border-bottom-0">Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pageattributes as $index => $attribute)
                                    <tr>
                                        <td>{{$attribute->page}}</td>
                                        <td>{{$attribute->domain}}</td>
                                        <td>{{$attribute->uri}}</td>
                                        <td>{{$attribute->meta_type}}</td>
                                        <td>{{$attribute->content}}</td>
                                        <td>{{$attribute->deleted_at_format}}</td>
                                        <td class="text-center">
                                            @php
                                            $button_disabled = '';
                                            $button_delete_url = route('admin.page-attribute.destroy',$attribute);
                                            $button_delete_url_disabled = '';
                                            if (!is_null($attribute->deleted_at)) {
                                                $button_disabled = 'disabled="disabled"';
                                                $button_delete_url = 'javascript:void(0)';
                                                $button_delete_url_disabled = 'disabled';
                                            }
                                            @endphp
                                            <div class="btn-group">
                                                <button {!! $button_disabled !!} class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#updateAttribute{{$index}}"><i class="fa fa-pencil"></i></button>
                                            </div>
                                            <div class="btn-group">
                                                <a {!! $button_disabled !!} href="{{ $button_delete_url }}" class="btn btn-sm btn-danger {{ $button_delete_url_disabled }}"><i class="fa fa-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Creating -->
            <div class="modal fade" id="addAttribute" tabindex="-1" aria-labelledby="addAttributeLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Neues Gewässer hinzufügen?</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{route('admin.page-attribute.store')}}" method="post">
                                @csrf
                                @method('post')
                            
                                @include('admin.pages.page-attribute.partials.fields', ['row' => new \App\Models\PageAttribute])
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zurück</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Creating -->
            @foreach($pageattributes as $index => $attribute)
            <!-- Updating -->
            <div class="modal fade" id="updateAttribute{{$index}}" tabindex="-1" aria-labelledby="addAttributeLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Neues Gewässer hinzufügen?</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{route('admin.page-attribute.update',$attribute)}}" method="post">
                                @csrf
                                @method('post')
                                @include('admin.pages.page-attribute.partials.fields',['row' => $attribute])
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zurück</button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            <!-- End Updating -->
            

            <!-- End Row -->
        </div>

    </div>
@endsection
@section('js_after')
<script>
    let attributetable = new DataTable('#attributeTable');
</script>
@endsection

