<!-- Modal -->
<div class="modal fade" id="edittarget{{$target->id}}" tabindex="-1" aria-labelledby="edittarget{{$target->id}}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edittarget{{$target->id}}Label">Zielfisch {{$target->name}} bearbeiten?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('admin.settings.updatetarget', $target->id)}}" method="post">
                    @csrf
                    @method('put')
                    <div class="mb-3">
                        <label for="name" class="form-label">Name des Zielfisches</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{$target->name}}">
                        <label for="name" class="form-label">Name des Zielfisches (en)</label>
                        <input type="text" class="form-control" id="name" name="name_en" value="{{$target->name_en}}">
                        <div id="nameHelf" style="color:red" class="form-text">Diese taucht dann in den Suchen und als Option für Guides auf.</div>
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
