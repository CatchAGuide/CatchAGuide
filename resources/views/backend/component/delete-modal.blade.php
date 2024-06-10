<div class="modal fade" id="deleteResourceModal" tabindex="-1" role="dialog"
     aria-labelledby="deleteResourceModal"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Bist Du sicher?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Abbrechen</button>
                <button type="button" onclick="confirmDeleteResource();" class="btn btn-danger">Ja</button>
            </div>
        </div>
    </div>
</div>

<form id="form_delete_resource" method="POST" action="">
    @csrf
    @method("DELETE")
</form>

<script type="text/javascript">

    function deleteResource(path) {
        $('#form_delete_resource').attr('action', path);
        $('#deleteResourceModal').modal('show');
    }

    function confirmDeleteResource() {
        $('#form_delete_resource').submit();
    }
</script>
