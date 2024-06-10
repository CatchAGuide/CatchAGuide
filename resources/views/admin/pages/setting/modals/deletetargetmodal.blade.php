<!-- Modal -->
<div class="modal fade" id="deletetarget{{$target->id}}" tabindex="-1" aria-labelledby="deletetarget{{$target->id}}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletetarget{{$target->id}}Label">Zielfisch {{$target->name}} wirklich löschen?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Vorsicht! Dieser Vorgang kann nicht rückgängig gemacht werden!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zurück</button>
                <a href="{{route('admin.settings.deletetarget', $target->id)}}">
                    <button type="button" class="btn btn-danger">LÖSCHEN</button>
                </a>
            </div>
        </div>
    </div>
</div>
