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
                            <h3>Angelmethoden
                                <i title="Angelmethode hinzufügen" style="color: red; cursor: pointer" class="side-menu__icon fe fe-plus-circle" data-bs-toggle="modal" data-bs-target="#addmethod"></i>
                            </h3>
                            @include('admin.pages.setting.modals.addmethodmodal')
                        </div>
                        <div class="card-body table-responsive">
                            <table id="methodtable" class="table">
                                <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Name English</th>
                                    <th scope="col">Aktion</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($methods as $method)
                                    <tr>
                                        <td>
                                            <div>
                                                <span class="fi fi-de"></span><span class="px-2">{{ $method->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <span class="fi fi-gb"></span><span class="px-2">{{ $method->name_en ?: '' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-link p-0 me-2 align-baseline text-danger"
                                                    title="Löschen"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteMethodModal"
                                                    data-delete-href="{{ route('admin.settings.deletemethod', $method->id) }}"
                                                    data-label="Angelmethode {{ $method->name }} wirklich löschen?">
                                                <i style="font-size: 20px" class="side-menu__icon fe fe-trash"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-link p-0 align-baseline text-primary"
                                                    title="Bearbeiten"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editMethodModal"
                                                    data-form-action="{{ route('admin.settings.updatemethod', $method->id) }}"
                                                    data-name-de="{{ $method->getRawOriginal('name') }}"
                                                    data-name-en="{{ $method->getRawOriginal('name_en') }}">
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

        <div class="modal fade" id="editMethodModal" tabindex="-1" aria-labelledby="editMethodModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editMethodModalLabel">Angelmethode bearbeiten</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editMethodForm" action="#" method="post">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="editMethodNameDe" class="form-label">Name der Methode</label>
                                <input type="text" class="form-control" id="editMethodNameDe" name="name" required>
                                <label for="editMethodNameEn" class="form-label mt-2">Name der Methode (en)</label>
                                <input type="text" class="form-control" id="editMethodNameEn" name="name_en">
                                <div class="form-text text-danger">Diese taucht dann in den Suchen und als Option für Guides auf.</div>
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

        <div class="modal fade" id="deleteMethodModal" tabindex="-1" aria-labelledby="deleteMethodModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteMethodModalLabel">Angelmethode löschen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Vorsicht! Dieser Vorgang kann nicht rückgängig gemacht werden!
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zurück</button>
                        <a id="deleteMethodLink" href="#" class="btn btn-danger">LÖSCHEN</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection


@section('js_after')
<script>
    let methodtable = new DataTable('#methodtable');

    document.getElementById('editMethodModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;
        document.getElementById('editMethodForm').action = btn.getAttribute('data-form-action');
        document.getElementById('editMethodNameDe').value = btn.getAttribute('data-name-de') || '';
        document.getElementById('editMethodNameEn').value = btn.getAttribute('data-name-en') || '';
    });

    document.getElementById('deleteMethodModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;
        document.getElementById('deleteMethodLink').href = btn.getAttribute('data-delete-href');
        const label = btn.getAttribute('data-label');
        if (label) {
            document.getElementById('deleteMethodModalLabel').textContent = label;
        }
    });
</script>
@endsection
