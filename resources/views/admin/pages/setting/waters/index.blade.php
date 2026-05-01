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
                            <h3>Gewässer
                                <i title="Gewässer hinzufügen" style="color: red; cursor: pointer" class="side-menu__icon fe fe-plus-circle" data-bs-toggle="modal" data-bs-target="#addwater"></i>
                                @include('admin.pages.setting.modals.addwatermodal')
                            </h3>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="watertable" class="table">
                                <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Name English</th>
                                    <th scope="col">Aktion</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($waters as $water)
                                    <tr>
                                        <td>
                                            <div>
                                                <span class="fi fi-de"></span><span class="px-2">{{ $water->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <span class="fi fi-gb"></span><span class="px-2">{{ $water->name_en ?: '' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-link p-0 me-2 align-baseline text-danger"
                                                    title="Löschen"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteWaterModal"
                                                    data-delete-href="{{ route('admin.settings.deletewater', $water->id) }}"
                                                    data-label="Gewässer {{ $water->name }} wirklich löschen?">
                                                <i style="font-size: 20px" class="side-menu__icon fe fe-trash"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-link p-0 align-baseline text-primary"
                                                    title="Bearbeiten"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editWaterModal"
                                                    data-form-action="{{ route('admin.settings.updatewater', $water->id) }}"
                                                    data-name-de="{{ $water->getRawOriginal('name') }}"
                                                    data-name-en="{{ $water->getRawOriginal('name_en') }}">
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

        <div class="modal fade" id="editWaterModal" tabindex="-1" aria-labelledby="editWaterModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editWaterModalLabel">Gewässer bearbeiten</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editWaterForm" action="#" method="post">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="editWaterNameDe" class="form-label">Name des Gewässers</label>
                                <input type="text" class="form-control" id="editWaterNameDe" name="name" required>
                                <label for="editWaterNameEn" class="form-label mt-2">Name des Gewässers (en)</label>
                                <input type="text" class="form-control" id="editWaterNameEn" name="name_en">
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

        <div class="modal fade" id="deleteWaterModal" tabindex="-1" aria-labelledby="deleteWaterModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteWaterModalLabel">Gewässer löschen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Vorsicht! Dieser Vorgang kann nicht rückgängig gemacht werden!
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zurück</button>
                        <a id="deleteWaterLink" href="#" class="btn btn-danger">LÖSCHEN</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection


@section('js_after')
<script>
    let watertable = new DataTable('#watertable');

    document.getElementById('editWaterModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;
        document.getElementById('editWaterForm').action = btn.getAttribute('data-form-action');
        document.getElementById('editWaterNameDe').value = btn.getAttribute('data-name-de') || '';
        document.getElementById('editWaterNameEn').value = btn.getAttribute('data-name-en') || '';
    });

    document.getElementById('deleteWaterModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;
        document.getElementById('deleteWaterLink').href = btn.getAttribute('data-delete-href');
        const label = btn.getAttribute('data-label');
        if (label) {
            document.getElementById('deleteWaterModalLabel').textContent = label;
        }
    });
</script>
@endsection
