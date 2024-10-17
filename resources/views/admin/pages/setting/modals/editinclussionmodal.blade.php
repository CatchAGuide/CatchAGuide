<!-- Modal -->
<div class="modal fade" id="editinclussion{{$inclussion->id}}" tabindex="-1" aria-labelledby="editinclussion{{$inclussion->id}}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editinclussion{{$inclussion->id}}Label">{{$inclussion->name}} bearbeiten?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('admin.settings.updateinclussion', $inclussion->id)}}" method="post">
                    @csrf
                    @method('put')
                    <div class="mb-3">
                        <label for="name" class="form-label">Name des Inbegriffen</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{$inclussion->name}}">
                        <label for="name" class="form-label">Name des Inbegriffen (en)</label>
                        <input type="text" class="form-control" id="name" name="name_en" value="{{$inclussion->name_en}}">
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
