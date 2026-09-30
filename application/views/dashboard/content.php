<section class="dashboard-overview" aria-labelledby="channels-heading">
    <div class="dashboard-section-heading">
        <div>
            <span class="dashboard-kicker">Your channels</span>
            <h2 id="channels-heading">Choose a destination</h2>
            <p>See the posts assigned to each social platform.</p>
        </div>
        <span class="dashboard-summary"><i class="bi bi-layers" aria-hidden="true"></i> <?= (int) $assigned_total ?> of <?= (int) $all_total ?> posts assigned</span>
    </div>

    <div class="platform-filter dashboard-platform-grid" aria-label="Filter by platform">
        <a class="dashboard-platform dashboard-platform-all<?= !$selected ? ' is-active' : '' ?>"
           href="<?= site_url('dashboard') ?>" data-platform=""<?= !$selected ? ' aria-current="page"' : '' ?>>
            <span class="dashboard-platform-top">
                <span class="dashboard-platform-icon"><i class="bi bi-grid-fill" aria-hidden="true"></i></span>
                <i class="bi bi-arrow-up-right dashboard-platform-arrow" aria-hidden="true"></i>
            </span>
            <span class="dashboard-platform-label">All assigned</span>
            <span class="dashboard-platform-bottom"><strong><?= (int) $assigned_total ?></strong><span>posts</span></span>
        </a>
        <?php foreach ($platforms as $platform): ?>
            <?php $is_active = $selected && (int) $selected['id'] === (int) $platform['id']; ?>
            <a class="dashboard-platform<?= $is_active ? ' is-active' : '' ?>"
               href="<?= site_url('dashboard') ?>?platform=<?= rawurlencode($platform['slug']) ?>"
               data-platform="<?= html_escape($platform['slug']) ?>"
               style="--platform-color: <?= html_escape($platform['color']) ?>;"<?= $is_active ? ' aria-current="page"' : '' ?>>
                <span class="dashboard-platform-top">
                    <span class="dashboard-platform-icon"><i class="bi <?= html_escape($platform['icon']) ?>" aria-hidden="true"></i></span>
                    <i class="bi bi-arrow-up-right dashboard-platform-arrow" aria-hidden="true"></i>
                </span>
                <span class="dashboard-platform-label"><?= html_escape($platform['name']) ?></span>
                <?php if (!empty($platform['max_chars'])): ?><span class="dashboard-platform-limit"><?= number_format((int) $platform['max_chars']) ?> character limit</span><?php endif; ?>
                <span class="dashboard-platform-bottom"><strong><?= isset($counts[$platform['id']]) ? (int) $counts[$platform['id']] : 0 ?></strong><span>posts</span></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="dashboard-results" aria-labelledby="dashboard-results-heading">
    <div class="dashboard-results-toolbar">
        <div class="dashboard-results-title">
            <span class="dashboard-kicker">Content preview</span>
            <h2 id="dashboard-results-heading"><?= $selected ? html_escape($selected['name']) : 'All assigned posts' ?> <span class="dashboard-result-count"><?= (int) $total ?></span></h2>
            <p><?= $selected ? 'Posts selected for this channel.' : 'Every post with at least one channel assigned.' ?></p>
        </div>

        <?php if ($all_total && $assigned_total): ?>
            <form id="dashboard-sort" class="dashboard-sort" action="<?= site_url('dashboard') ?>" method="get">
                <?php if ($selected): ?><input type="hidden" name="platform" value="<?= html_escape($selected['slug']) ?>"><?php endif; ?>
                <div class="dashboard-sort-field">
                    <label for="sort">Sort by</label>
                    <select id="sort" name="sort">
                        <option value="priority" <?= $sort === 'priority' ? 'selected' : '' ?>>Priority</option>
                        <option value="date" <?= $sort === 'date' ? 'selected' : '' ?>>Date</option>
                        <option value="characters" <?= $sort === 'characters' ? 'selected' : '' ?>>Characters</option>
                        <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Title</option>
                    </select>
                </div>
                <div class="dashboard-sort-field">
                    <label for="dir">Order</label>
                    <select id="dir" name="dir">
                        <option value="asc" <?= $dir === 'asc' ? 'selected' : '' ?>>Ascending</option>
                        <option value="desc" <?= $dir === 'desc' ? 'selected' : '' ?>>Descending</option>
                    </select>
                </div>
                <noscript><button class="dashboard-sort-submit" type="submit">Apply</button></noscript>
            </form>
        <?php endif; ?>
    </div>

    <?php if (!$all_total): ?>
        <div class="dashboard-empty">
            <span class="dashboard-empty-icon"><i class="bi bi-rss" aria-hidden="true"></i></span>
            <h3>No Posts Yet</h3>
            <p>Import your first feed to start building a content library.</p>
            <a class="dashboard-empty-action" href="<?= site_url('feed') ?>">Import a feed <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
    <?php elseif ($selected && !$posts): ?>
        <div class="dashboard-empty">
            <span class="dashboard-empty-icon"><i class="bi bi-send" aria-hidden="true"></i></span>
            <h3>No Posts Assigned to <?= html_escape($selected['name']) ?></h3>
            <p>Assign a post to <?= html_escape($selected['name']) ?> to see it here.</p>
            <a class="dashboard-empty-action" href="<?= site_url('posts') ?>">Manage posts <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
    <?php elseif (!$assigned_total): ?>
        <div class="dashboard-empty">
            <span class="dashboard-empty-icon"><i class="bi bi-share" aria-hidden="true"></i></span>
            <h3>No Posts Assigned to Any Platform</h3>
            <p>(<?= (int) $all_total ?> imported posts waiting)</p>
            <a class="dashboard-empty-action" href="<?= site_url('posts') ?>">Manage posts <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
    <?php else: ?>
        <div class="dashboard-card-grid">
            <?php foreach ($posts as $post): ?>
                <?php
                $post_platforms = isset($assigned[$post['id']]) ? $assigned[$post['id']] : array();
                $limit_warnings = array();
                foreach ($post_platforms as $platform) {
                    $limit = isset($platform['max_chars']) ? (int) $platform['max_chars'] : 0;
                    if ($limit > 0 && (int) $post['char_count'] > $limit) {
                        $limit_warnings[(int) $platform['id']] = array(
                            'name' => $platform['name'],
                            'limit' => $limit,
                            'over_by' => (int) $post['char_count'] - $limit,
                        );
                    }
                }
                ?>
                <article class="dashboard-post-card<?= $limit_warnings ? ' has-limit-warning' : '' ?>">
                    <div class="dashboard-post-media">
                        <?php if ($post['image_url'] && app_safe_url($post['image_url'])): ?>
                            <img src="<?= html_escape($post['image_url']) ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <span class="dashboard-post-placeholder"><i class="bi bi-image" aria-hidden="true"></i></span>
                        <?php endif; ?>
                        <span class="dashboard-priority"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i> Priority <?= (int) $post['priority'] ?></span>
                        <?php if ($limit_warnings): ?><span class="dashboard-media-warning"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i> Over limit</span><?php endif; ?>
                    </div>
                    <div class="dashboard-post-body">
                        <div class="dashboard-post-meta">
                            <span><i class="bi bi-calendar3" aria-hidden="true"></i> <?= html_escape(date('M j, Y', strtotime($post['pub_date']))) ?></span>
                            <span><i class="bi bi-text-paragraph" aria-hidden="true"></i> <?= (int) $post['char_count'] ?> chars</span>
                        </div>
                        <h3 class="dashboard-post-title"><?= html_escape($post['title']) ?></h3>
                        <p class="dashboard-post-excerpt"><?= html_escape($post['content']) ?></p>
                        <?php if ($limit_warnings): ?>
                            <div class="content-limit-alert" role="status" aria-label="Platform character limit warning">
                                <div class="content-limit-alert-heading"><i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i> Character limit exceeded</div>
                                <ul class="content-limit-list">
                                    <?php foreach ($limit_warnings as $warning): ?>
                                        <li><span><?= html_escape($warning['name']) ?></span><strong><?= (int) $post['char_count'] ?> / <?= (int) $warning['limit'] ?></strong><em><?= (int) $warning['over_by'] ?> over</em></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <div class="dashboard-post-channels" aria-label="Assigned platforms">
                            <?php foreach ($post_platforms as $platform): ?>
                                <?php $is_over_limit = isset($limit_warnings[(int) $platform['id']]); ?>
                                <span class="dashboard-channel-badge<?= $is_over_limit ? ' is-over-limit' : '' ?>" style="--platform-color: <?= html_escape($platform['color']) ?>;"<?= $is_over_limit ? ' title="Character limit exceeded"' : '' ?>><i class="bi <?= html_escape($platform['icon']) ?>" aria-hidden="true"></i> <?= html_escape($platform['name']) ?><?php if ($is_over_limit): ?> <i class="bi bi-exclamation-triangle-fill dashboard-channel-warning-icon" aria-label="Over character limit"></i><?php endif; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($pages > 1): ?>
            <nav class="dashboard-pagination-wrap" aria-label="Dashboard pages">
                <span class="dashboard-page-summary">Page <?= (int) $page ?> of <?= (int) $pages ?></span>
                <ul class="dashboard-pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <li><a class="dashboard-page<?= $i === $page ? ' is-active' : '' ?>"
                               href="<?= site_url('dashboard') ?>?<?= html_escape(http_build_query(array('platform' => $selected ? $selected['slug'] : '', 'sort' => $sort, 'dir' => $dir, 'page' => $i))) ?>"
                               data-page="<?= $i ?>"<?= $i === $page ? ' aria-current="page"' : '' ?>><?= $i ?></a></li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
