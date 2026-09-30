<?php if (!$posts): ?>

    <div class="text-center py-5 posts-empty">
        <div class="posts-empty-icon mb-3">
            <i class="bi bi-inbox"></i>
        </div>
        <h2 class="h4 fw-bold mb-2">No Posts Yet</h2>
        <p class="text-muted mb-4">Import a feed to start building your content library.</p>
        <a href="<?= site_url('feed') ?>" class="btn btn-primary">
            <i class="bi bi-rss me-1"></i>Import Feed
        </a>
    </div>

<?php else: ?>

    <div id="post-grid" class="post-grid">
        <?php foreach ($posts as $post) {
            $this->load->view('posts/_card', array(
                'post'     => $post,
                'assigned' => $assigned,
            ));
        } ?>
    </div>

    <div class="posts-pagination">
        <p class="posts-page-summary">
            <span>Showing <?= (($page - 1) * 10) + 1 ?>&ndash;<?= min($page * 10, $total) ?> of <?= $total ?> posts</span>
            <span>Page <strong><?= (int) $page ?></strong> of <?= (int) $pages ?></span>
        </p>
        <?= $pagination ?>
    </div>

<?php endif; ?>
