<div class="modal fade" id="addReligionModal" tabindex="-1" aria-labelledby="addReligionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header border-bottom">
                <h5 class="modal-title mb-2" id="addReligionModalLabel">
                    <i class="ri-add-line me-2"></i>Add Religion
                </h5>
                <button type="button" class="btn-close mb-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="addReligionForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="religion_name" class="form-label">
                            Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="religion_name"
                               name="name" placeholder="Enter name" required>
                        <div class="invalid-feedback" id="religion_name_error"></div>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary mt-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary mt-2" id="saveReligionBtn">
                        <i class="ri-save-line me-1"></i>Save
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>