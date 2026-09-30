<section class="dashboard-view" aria-labelledby="dashboard-title">
    <header class="dashboard-hero">
        <div class="dashboard-hero-copy">
            <span class="dashboard-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> Publishing overview</span>
            <h1 id="dashboard-title">Your content, at a glance.</h1>
            <p>Explore posts by channel, review what is ready to share, and keep your publishing plans moving.</p>
            <a class="dashboard-hero-action" href="<?= site_url('posts') ?>">Manage posts <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </div>
        <div class="dashboard-hero-art" aria-hidden="true">
            <span class="dashboard-orbit dashboard-orbit-one"></span>
            <span class="dashboard-orbit dashboard-orbit-two"></span>
            <span class="dashboard-hero-icon dashboard-hero-icon-one"><i class="bi bi-grid-1x2-fill"></i></span>
            <span class="dashboard-hero-icon dashboard-hero-icon-two"><i class="bi bi-send-fill"></i></span>
            <span class="dashboard-hero-icon dashboard-hero-icon-three"><i class="bi bi-chat-left-heart-fill"></i></span>
        </div>
    </header>

    <div id="dashboard-content" aria-live="polite">
        <?php $this->load->view('dashboard/content', array('platforms' => $platforms, 'selected' => $selected, 'counts' => $counts, 'assigned_total' => $assigned_total, 'all_total' => $all_total, 'posts' => $posts, 'assigned' => $assigned, 'total' => $total, 'page' => $page, 'pages' => $pages, 'sort' => $sort, 'dir' => $dir)); ?>
    </div>
</section>
