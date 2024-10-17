<!-- Modal -->
<div class="modal fade" id="addfishingfrom" tabindex="-1" aria-labelledby="addfishingfromLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Neue Angeln von</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('admin.settings.storefishingfrom')}}" method="post">
                    @csrf
                    @method('post')
                    <div class="mb-3">
                        <label for="name" class="form-label">Name der Angeln von</label>
                        <input type="text" class="form-control" id="name" name="name">
                        <label for="name" class="form-label">Name des Angeln von (en)</label>
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
