<div class="modal fade" id="edit-modal" tabindex="-1" aria-labelledby="edit-title">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="edit-form" novalidate>
                <div class="modal-header">
                    <div><span class="modal-eyebrow">Content workspace</span><h2 id="edit-title" class="modal-title h5">Edit post</h2></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3"><label class="form-label" for="edit-post-title">Title</label><input class="form-control" name="title" id="edit-post-title" maxlength="500" required><div class="invalid-feedback">Title is required.</div></div>
                    <div class="mb-3"><label class="form-label" for="edit-content">Content</label><textarea class="form-control" name="content" id="edit-content" rows="5" required></textarea><div class="invalid-feedback">Content is required.</div></div>
                    <div class="edit-count-bar"><span><i class="bi bi-text-paragraph" aria-hidden="true"></i> Character count</span><strong id="edit-count">0</strong></div>
                    <fieldset class="platform-fieldset"><legend>Choose platforms</legend><p>Switch on one or more destinations for this post.</p>
                        <div class="platform-option-grid">
                            <?php foreach ($platforms as $platform): ?>
                                <label class="platform-option" style="--platform-color:<?= html_escape($platform['color']) ?>">
                                    <span class="platform-option-main">
                                        <span class="platform-option-icon"><i class="bi <?= html_escape($platform['icon']) ?>" aria-hidden="true"></i></span>
                                        <span class="platform-option-copy"><strong><?= html_escape($platform['name']) ?></strong><small class="platform-counter"><?= (int) $platform['max_chars'] ?> max</small></span>
                                    </span>
                                    <input class="platform-check" type="checkbox" role="switch" aria-label="Assign to <?= html_escape($platform['name']) ?>" name="platforms[]" value="<?= (int) $platform['id'] ?>" data-name="<?= html_escape($platform['name']) ?>" data-max="<?= (int) $platform['max_chars'] ?>">
                                    <span class="platform-toggle" aria-hidden="true"></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                    <div id="platform-limit-alert" class="platform-limit-alert d-none" role="alert" aria-live="polite"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><span></span></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><span class="spinner-border spinner-border-sm d-none me-1" aria-hidden="true"></span>Save changes</button></div>
            </form>
        </div>
    </div>
</div>
