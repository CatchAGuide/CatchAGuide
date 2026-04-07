@extends('admin.layouts.app')

@section('title', 'Guiding Einstellungen')

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Verwaltung</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>
            </div>

            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row ">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>Zielfische
                                <i title="Zielfisch hinzufügen" style="color: red; cursor: pointer" class="side-menu__icon fe fe-plus-circle" data-bs-toggle="modal" data-bs-target="#addtarget"></i>
                                @include('admin.pages.setting.modals.addtargetmodal')
                            </h3>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="targettable" class="table">
                                <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Name English</th>
                                    <th scope="col">Aktion</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($targets as $target)
                                    <tr>
                                        <td>
                                            <div>
                                                <span class="fi fi-de"></span><span class="px-2">{{ $target->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <span class="fi fi-gb"></span><span class="px-2">{{ $target->name_en ?: '' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-link p-0 me-2 align-baseline text-danger"
                                                    title="Löschen"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteTargetModal"
                                                    data-delete-href="{{ route('admin.settings.deletetarget', $target->id) }}"
                                                    data-label="Zielfisch {{ $target->name }} wirklich löschen?">
                                                <i style="font-size: 20px" class="side-menu__icon fe fe-trash"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-link p-0 align-baseline text-primary"
                                                    title="Bearbeiten"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editTargetModal"
                                                    data-form-action="{{ route('admin.settings.updatetarget', $target->id) }}"
                                                    data-name-de="{{ $target->getRawOriginal('name') }}"
                                                    data-name-en="{{ $target->getRawOriginal('name_en') }}">
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
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

        <div class="modal fade" id="editTargetModal" tabindex="-1" aria-labelledby="editTargetModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTargetModalLabel">Zielfisch bearbeiten</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editTargetForm" action="#" method="post">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="editTargetNameDe" class="form-label">Name des Zielfisches</label>
                                <input type="text" class="form-control" id="editTargetNameDe" name="name" required>
                                <label for="editTargetNameEn" class="form-label mt-2">Name des Zielfisches (en)</label>
                                <input type="text" class="form-control" id="editTargetNameEn" name="name_en">
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

        <div class="modal fade" id="deleteTargetModal" tabindex="-1" aria-labelledby="deleteTargetModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteTargetModalLabel">Zielfisch löschen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Vorsicht! Dieser Vorgang kann nicht rückgängig gemacht werden!
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zurück</button>
                        <a id="deleteTargetLink" href="#" class="btn btn-danger">LÖSCHEN</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection


@section('js_after')
<script>
    let targettable = new DataTable('#targettable');

    document.getElementById('editTargetModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;
        const form = document.getElementById('editTargetForm');
        form.action = btn.getAttribute('data-form-action');
        document.getElementById('editTargetNameDe').value = btn.getAttribute('data-name-de') || '';
        document.getElementById('editTargetNameEn').value = btn.getAttribute('data-name-en') || '';
    });

    document.getElementById('deleteTargetModal').addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;
        document.getElementById('deleteTargetLink').href = btn.getAttribute('data-delete-href');
        const label = btn.getAttribute('data-label');
        if (label) {
            document.getElementById('deleteTargetModalLabel').textContent = label;
        }
    });
</script>
@endsection
