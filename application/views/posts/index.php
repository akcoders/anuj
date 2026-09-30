<section class="posts-page" aria-labelledby="posts-heading">
    <div class="posts-hero">
        <div class="posts-hero-copy">
            <span class="posts-eyebrow"><i class="bi bi-collection" aria-hidden="true"></i> Content library</span>
            <h1 id="posts-heading">Your posts <span id="posts-total" class="posts-count"><?= (int) $total ?></span></h1>
            <p>Organize your feed stories, choose platforms, and set what comes first.</p>
        </div>
        <a href="<?= site_url('feed') ?>" class="btn btn-light posts-import"><i class="bi bi-plus-lg" aria-hidden="true"></i> Import feed</a>
    </div>

    <div class="posts-toolbar">
        <div class="posts-toolbar-label"><i class="bi bi-grip-vertical" aria-hidden="true"></i><span>Drag a handle into a drop target to reorder posts</span></div>
        <fieldset class="view-switcher" aria-label="Post view">
            <legend class="visually-hidden">Post view</legend>
            <input type="radio" class="btn-check" name="posts-view" id="view-card" value="card" autocomplete="off">
            <label for="view-card" title="Card view"><i class="bi bi-grid-fill" aria-hidden="true"></i><span class="visually-hidden">Card view</span></label>
            <input type="radio" class="btn-check" name="posts-view" id="view-list" value="list" autocomplete="off">
            <label for="view-list" title="List view"><i class="bi bi-list-ul" aria-hidden="true"></i><span class="visually-hidden">List view</span></label>
        </fieldset>
    </div>

    <div id="posts-list" data-page="<?= (int) $page ?>">
        <?php $this->load->view('posts/list', array('posts' => $posts, 'assigned' => $assigned, 'pagination' => $pagination, 'total' => $total, 'page' => $page, 'pages' => $pages)); ?>
    </div>
    <p id="reorder-status" class="visually-hidden" role="status" aria-live="polite"></p>

    <?php $this->load->view('partials/modal_edit', array('platforms' => $platforms)); ?>
    <?php $this->load->view('partials/modal_delete'); ?>
</section>
