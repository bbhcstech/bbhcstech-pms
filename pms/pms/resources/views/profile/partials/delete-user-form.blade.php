<section class="mb-5">
    <h5 class="text-danger fw-semibold mb-2">Delete Account</h5>

    <p class="text-muted">
        Once your account is deleted, all of its data will be permanently removed. Please make sure to download any information you wish to retain.
    </p>

    <!-- Trigger Modal Button -->
    <div class="col-12 d-flex justify-content-end mt-3">
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
            Delete Account
        </button>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')

                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Account Deletion</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p>
                            Are you sure you want to delete your account? All your data will be permanently deleted.
                        </p>

                        <div class="mb-3">
                            <label for="password" class="form-label">Enter Password</label>
                            <input type="password" name="password" class="form-control @error('userDeletion.password') is-invalid @enderror" placeholder="Password" required>
                            @error('userDeletion.password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
