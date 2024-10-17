<!-- Modal -->
<div class="modal fade" id="addlevel" tabindex="-1" aria-labelledby="addlevelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Neue Ausgelegt für</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('admin.settings.storelevel')}}" method="post">
                    @csrf
                    @method('post')
                    <div class="mb-3">
                        <label for="name" class="form-label">Name der Ausgelegt für</label>
                        <input type="text" class="form-control" id="name" name="name">
                        <label for="name" class="form-label">Name des Ausgelegt für (en)</label>
                        <input type="text" class="form-control" id="name" name="name_en">
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
