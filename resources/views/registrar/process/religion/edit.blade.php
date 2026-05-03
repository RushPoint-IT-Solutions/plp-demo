<div class="modal fade" id="editReligionModal" tabindex="-1" aria-labelledby="editReligionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header border-bottom">
                <h5 class="modal-title mb-2" id="editReligionModalLabel">
                    <i class="ri-edit-line me-2"></i>Edit Religion
                </h5>
                <button type="button" class="btn-close mb-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="editReligionForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_religion_id" name="religion_id">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_religion_name" class="form-label">
                            Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="edit_religion_name"
                               name="name" placeholder="Enter name" required>
                        <div class="invalid-feedback" id="edit_religion_name_error"></div>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary mt-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary mt-2" id="updateReligionBtn">
                        <i class="ri-save-line me-1"></i>Update
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>