<?php
$nav_items = array(
    array('key' => 'feed', 'label' => 'Import feed', 'icon' => 'bi-rss', 'url' => site_url('feed')),
    array('key' => 'posts', 'label' => 'Posts', 'icon' => 'bi-grid-1x2', 'url' => site_url('posts')),
    array('key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-bar-chart', 'url' => site_url('dashboard')),
);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-name" content="<?= html_escape($this->security->get_csrf_token_name()) ?>">
    <meta name="csrf-hash" content="<?= html_escape($this->security->get_csrf_hash()) ?>">
    <title><?= html_escape($title) ?> · RSS Post Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
    <?php if ($active === 'feed'): ?><link href="<?= base_url('assets/css/feed.css') ?>" rel="stylesheet"><?php endif; ?>
    <?php if ($active === 'posts'): ?><link href="<?= base_url('assets/css/posts.css') ?>" rel="stylesheet">
    <script>(function(){var view='card';try{view=localStorage.getItem('postsView')||'card';}catch(error){}if(['card','list'].indexOf(view)<0)view='card';document.documentElement.setAttribute('data-posts-view',view);}());</script><?php endif; ?>
    <?php if ($active === 'dashboard'): ?><link href="<?= base_url('assets/css/dashboard.css') ?>" rel="stylesheet"><?php endif; ?>
</head>
<body data-base-url="<?= html_escape(base_url()) ?>">
<div class="app-shell">
    <aside class="offcanvas-lg offcanvas-start app-sidebar" tabindex="-1" id="appSidebar" aria-labelledby="appSidebarTitle">
        <div class="app-sidebar-head">
            <a class="app-brand" href="<?= site_url() ?>" aria-label="RSS Post Manager home">
                <span class="app-brand-icon"><i class="bi bi-broadcast-pin" aria-hidden="true"></i></span>
                <span class="app-brand-copy"><strong id="appSidebarTitle">RSS Post Manager</strong><small>Content workspace</small></span>
            </a>
            <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Close menu"></button>
        </div>
        <div class="app-sidebar-body">
            <p class="app-sidebar-label">Workspace</p>
            <nav class="app-nav" aria-label="Main navigation">
                <?php foreach ($nav_items as $item): ?>
                    <a class="app-nav-link <?= $active === $item['key'] ? 'is-active' : '' ?>" href="<?= $item['url'] ?>" <?= $active === $item['key'] ? 'aria-current="page"' : '' ?>>
                        <span class="app-nav-icon"><i class="bi <?= $item['icon'] ?>" aria-hidden="true"></i></span>
                        <span><?= html_escape($item['label']) ?></span>
                        <?php if ($active === $item['key']): ?><i class="bi bi-chevron-right app-nav-arrow" aria-hidden="true"></i><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="app-sidebar-note">
                <span class="app-sidebar-note-icon"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i></span>
                <strong>Keep your content moving</strong>
                <p>Import stories, arrange their priority, and prepare posts for each platform.</p>
            </div>
        </div>
        <div class="app-sidebar-foot"><span class="app-sidebar-foot-dot"></span> Your publishing workspace</div>
    </aside>

    <div class="app-main">
        <header class="app-topbar">
            <div class="app-topbar-leading">
                <button class="app-menu-button d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Open menu"><i class="bi bi-list" aria-hidden="true"></i></button>
                <div class="app-topbar-context"><span>Workspace <i class="bi bi-chevron-right" aria-hidden="true"></i> <?= html_escape($title) ?></span><strong><?= html_escape($title) ?></strong></div>
            </div>
            <div class="app-topbar-actions">
                <span class="app-topbar-status d-none d-md-inline-flex"><span></span> Ready to create</span>
                <a class="btn btn-primary app-topbar-cta" href="<?= $active === 'feed' ? site_url('posts') : site_url('feed') ?>"><i class="bi <?= $active === 'feed' ? 'bi-grid-1x2' : 'bi-plus-lg' ?>" aria-hidden="true"></i><span><?= $active === 'feed' ? 'View posts' : 'Import feed' ?></span></a>
            </div>
        </header>
        <main id="main-content" class="app-content">
