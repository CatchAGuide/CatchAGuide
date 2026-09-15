@php
    $hasActive = in_array('is_active', $fields, true);
    $hasSort = in_array('sort_order', $fields, true);
    $hasInputType = in_array('input_type', $fields, true);
    $hasPlaceholder = in_array('placeholder', $fields, true);
    $hasPlaceholderEn = in_array('placeholder_en', $fields, true);
    $hasNameDe = in_array('name_de', $fields, true);
    $deField = $hasNameDe ? 'name_de' : 'name';
@endphp

@extends('admin.layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">{{ $pageTitle }}</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">{{ __('admin.listing_attributes.breadcrumb_admin') }}</li>
                        <li class="breadcrumb-item">{{ __('admin.listing_attributes.section') }}</li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                    </ol>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="mb-0">{{ $pageTitle }}</h3>
                            <button type="button"
                                    class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#add-attribute"
                                    title="{{ __('admin.listing_attributes.actions.add') }}">
                                <i class="fe fe-plus"></i> {{ __('admin.listing_attributes.actions.add') }}
                            </button>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="listing-attributes-table" class="table">
                                <thead>
                                <tr>
                                    <th scope="col">{{ __('admin.listing_attributes.columns.name_en') }}</th>
                                    <th scope="col">{{ __('admin.listing_attributes.columns.name_de') }}</th>
                                    @if($hasInputType)
                                        <th scope="col">{{ __('admin.listing_attributes.columns.input_type') }}</th>
                                    @endif
                                    @if($hasPlaceholder)
                                        <th scope="col">{{ __('admin.listing_attributes.columns.placeholder') }}</th>
                                    @endif
                                    @if($hasPlaceholderEn)
                                        <th scope="col">{{ __('admin.listing_attributes.columns.placeholder_en') }}</th>
                                    @endif
                                    @if($hasActive)
                                        <th scope="col">{{ __('admin.listing_attributes.columns.active') }}</th>
                                    @endif
                                    @if($hasSort)
                                        <th scope="col">{{ __('admin.listing_attributes.columns.sort_order') }}</th>
                                    @endif
                                    <th scope="col">{{ __('admin.listing_attributes.columns.actions') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($rows as $row)
                                    @php
                                        $rawEn = $row->getRawOriginal('name_en');
                                        $rawDe = $hasNameDe
                                            ? $row->getRawOriginal('name_de')
                                            : $row->getRawOriginal('name');
                                        $displayName = $rawDe ?: $rawEn;
                                    @endphp
                                    <tr>
                                        <td>{{ $rawEn }}</td>
                                        <td>{{ $rawDe }}</td>
                                        @if($hasInputType)
                                            <td>{{ $row->getRawOriginal('input_type') }}</td>
                                        @endif
                                        @if($hasPlaceholder)
                                            <td>{{ $row->getRawOriginal('placeholder') }}</td>
                                        @endif
                                        @if($hasPlaceholderEn)
                                            <td>{{ $row->getRawOriginal('placeholder_en') }}</td>
                                        @endif
                                        @if($hasActive)
                                            <td>{{ $row->is_active ? __('admin.listing_attributes.yes') : __('admin.listing_attributes.no') }}</td>
                                        @endif
                                        @if($hasSort)
                                            <td>{{ $row->sort_order }}</td>
                                        @endif
                                        <td>
                                            <i style="font-size: 20px; color: red; cursor: pointer"
                                               class="side-menu__icon fe fe-trash"
                                               data-bs-toggle="modal"
                                               data-bs-target="#delete-attribute-{{ $row->id }}"
                                               title="{{ __('admin.listing_attributes.actions.delete') }}"></i>
                                            <i style="font-size: 20px; color: blue; cursor: pointer"
                                               class="side-menu__icon fe fe-edit"
                                               data-bs-toggle="modal"
                                               data-bs-target="#edit-attribute-{{ $row->id }}"
                                               title="{{ __('admin.listing_attributes.actions.edit') }}"></i>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="edit-attribute-{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('admin.listing_attributes.modals.edit_title') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.listing_attributes.actions.close') }}"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.settings.attributes.update', [$type, $row->id]) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        @include('admin.pages.setting.attributes.partials.fields', [
                                                            'fields' => $fields,
                                                            'deField' => $deField,
                                                            'row' => $row,
                                                        ])
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('admin.listing_attributes.actions.cancel') }}</button>
                                                        <button type="submit" class="btn btn-primary">{{ __('admin.listing_attributes.actions.save') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="delete-attribute-{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('admin.listing_attributes.modals.delete_title') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.listing_attributes.actions.close') }}"></button>
                                                </div>
                                                <div class="modal-body">
                                                    {!! __('admin.listing_attributes.modals.delete_confirm', ['name' => e($displayName)]) !!}
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('admin.listing_attributes.actions.cancel') }}</button>
                                                    <form method="POST" action="{{ route('admin.settings.attributes.destroy', [$type, $row->id]) }}" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">{{ __('admin.listing_attributes.actions.delete') }}</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add-attribute" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('admin.listing_attributes.modals.add_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.listing_attributes.actions.close') }}"></button>
                </div>
                <form method="POST" action="{{ route('admin.settings.attributes.store', $type) }}">
                    @csrf
                    <div class="modal-body">
                        @include('admin.pages.setting.attributes.partials.fields', [
                            'fields' => $fields,
                            'deField' => $deField,
                            'row' => null,
                        ])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('admin.listing_attributes.actions.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('admin.listing_attributes.actions.add') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js_after')
<script>
    $('#listing-attributes-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ app()->getLocale() === 'en' ? 'en-gb' : 'de_de' }}.json'
        }
    });
</script>
@endsection
