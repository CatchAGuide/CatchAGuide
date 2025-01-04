<div class="modal fade" id="editVacationModal" tabindex="-1" aria-labelledby="editVacationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVacationModalLabel">Edit Vacation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editVacationForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <!-- Same form fields as add modal, but with id="edit_fieldname" -->
                <!-- The JavaScript will populate these fields -->
            </form>
        </div>
    </div>
</div> 