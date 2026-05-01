@extends('admin.layouts.app')

@section('title', 'Guiding Einstellungen')

@section('content')
    <div class="side-app">

        <div class="main-container container-fluid">

            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Verwaltung</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>Inbegriffen
                                <i title="Eintrag hinzufügen" style="color: red; cursor: pointer" class="side-menu__icon fe fe-plus-circle" data-bs-toggle="modal" data-bs-target="#addinclussion"></i>
                                @include('admin.pages.setting.modals.addinclussionmodal')
                            </h3>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="inclussion" class="table">
                                <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Name English</th>
                                    <th scope="col">Aktion</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($inclussions as $inclussion)
                                    <tr>
                                        <td>
                                            <div>
                                                <span class="fi fi-de"></span><span class="px-2">{{ $inclussion->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <span class="fi fi-gb"></span><span class="px-2">{{ $inclussion->name_en ?: '' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-link p-0 me-2 align-baseline text-danger"
                                                    title="Löschen"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteInclussionModal"
                                                    data-delete-href="{{ route('admin.settings.deleteinclussion', $inclussion->id) }}"
                                                    data-label="{{ $inclussion->name }} wirklich löschen?">
                                                <i style="font-size: 20px" class="side-menu__icon fe fe-trash"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-link p-0 align-baseline text-primary"
                                                    title="Bearbeiten"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editInclussionModal"
                                                    data-form-action="{{ route('admin.settings.updateinclussion', $inclussion->id) }}"
                                                    data-name-de="{{ $inclussion->getRawOriginal('name') }}"
                                                    data-name-en="{{ $inclussion->getRawOriginal('name_en') }}">
                                                <i style="font-size: 20px" class="side-menu__icon fe fe-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editInclussionModal" tabindex="-1" aria-labelledby="editInclussionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editInclussionModalLabel">Inbegriffen bearbeiten</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editInclussionForm" action="#" method="post">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="editInclussionNameDe" class="form-label">Name des Inbegriffen</label>
                                <input type="text" class="form-control" id="editInclussionNameDe" name="name" required>
                                <label for="editInclussionNameEn" class="form-label mt-2">Name des Inbegriffen (en)</label>
                                <input type="text" class="form-control" id="editInclussionNameEn" name="name_en">
                            </div>
                            <button type="submit" class="btn btn-primary">Speichern</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zurück</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteInclussionModal" tabindex="-1" aria-labelledby="deleteInclussionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteInclussionModalLabel">Inbegriffen löschen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Vorsicht! Dieser Vorgang kann nicht rückgängig gemacht werden!
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zurück</button>
                        <a id="deleteInclussionLink" href="#" class="btn btn-danger">LÖSCHEN</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection


@section('js_after')
<script>
    let inclussion = new DataTable('#inclussion');

    document.getElementById('editInclussionModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;
        document.getElementById('editInclussionForm').action = btn.getAttribute('data-form-action');
        document.getElementById('editInclussionNameDe').value = btn.getAttribute('data-name-de') || '';
        document.getElementById('editInclussionNameEn').value = btn.getAttribute('data-name-en') || '';
    });

    document.getElementById('deleteInclussionModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;
        document.getElementById('deleteInclussionLink').href = btn.getAttribute('data-delete-href');
        const label = btn.getAttribute('data-label');
        if (label) {
            document.getElementById('deleteInclussionModalLabel').textContent = label;
        }
    });
</script>
@endsection
