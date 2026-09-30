<?php
$post_platforms = isset($assigned[$post['id']]) ? $assigned[$post['id']] : array();
$limit_warnings = array();
foreach ($post_platforms as $platform) {
    $limit = isset($platform['max_chars']) ? (int) $platform['max_chars'] : 0;
    if ($limit > 0 && (int) $post['char_count'] > $limit) {
        $limit_warnings[] = array(
            'name' => $platform['name'],
            'limit' => $limit,
            'over_by' => (int) $post['char_count'] - $limit,
        );
    }
}
?>
<div class="post-col" data-id="<?= (int) $post['id'] ?>">

    <article class="card h-100 border-0 post-card<?= $limit_warnings ? ' has-limit-warning' : '' ?>">

        <!-- Thumbnail -->
        <div class="post-image-wrap">
            <span class="priority-badge">#<?= (int) $post['priority'] ?></span>

            <?php if (!empty($post['image_url']) && app_safe_url($post['image_url'])): ?>
                <img class="post-image"
                     src="<?= html_escape($post['image_url']) ?>"
                     alt=""
                     loading="lazy">
            <?php else: ?>
                <div class="post-placeholder">
                    <i class="bi bi-image" aria-hidden="true"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Body -->
        <div class="post-body">

            <div class="post-meta">
                <span><i class="bi bi-calendar3"></i><?= html_escape(date('M j, Y', strtotime($post['pub_date']))) ?></span>
                <span class="post-meta-dot"></span>
                <span><i class="bi bi-text-paragraph"></i><?= (int) $post['char_count'] ?> chars</span>
            </div>

            <h2 class="post-title clamp-2" title="<?= html_escape($post['title']) ?>">
                <?= html_escape($post['title']) ?>
            </h2>

            <p class="post-excerpt clamp-2">
                <?= html_escape($post['content']) ?>
            </p>

            <?php if ($limit_warnings): ?>
                <details class="post-limit-alert">
                    <summary><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><span>Limit exceeded</span><span class="post-limit-count"><?= count($limit_warnings) ?> <?= count($limit_warnings) === 1 ? 'platform' : 'platforms' ?></span><i class="bi bi-chevron-down post-limit-chevron" aria-hidden="true"></i></summary>
                    <ul class="content-limit-list">
                        <?php foreach ($limit_warnings as $warning): ?>
                            <li><span><?= html_escape($warning['name']) ?></span><strong><?= (int) $post['char_count'] ?> / <?= (int) $warning['limit'] ?></strong><em><?= (int) $warning['over_by'] ?> over</em></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>

            <?php if ($post_platforms): ?>
                <div class="platform-badges">
                    <?php foreach ($post_platforms as $platform): ?>
                        <?php $over_limit = !empty($platform['max_chars']) && (int) $post['char_count'] > (int) $platform['max_chars']; ?>
                        <span class="badge platform-pill<?= $over_limit ? ' is-over-limit' : '' ?>"
                              <?php if ($over_limit): ?>title="<?= html_escape($platform['name']) ?> limit exceeded: <?= (int) $post['char_count'] ?>/<?= (int) $platform['max_chars'] ?> characters"<?php endif; ?>
                              style="background-color:<?= html_escape($platform['color']) ?>">
                            <i class="bi <?= html_escape($platform['icon']) ?>"></i>
                            <?= html_escape($platform['name']) ?>
                            <?php if ($over_limit): ?><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><span class="visually-hidden">Over character limit</span><?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- Actions -->
        <div class="post-actions">
            <button type="button" class="btn btn-sm btn-outline-primary edit-post">
                <i class="bi bi-pencil"></i><span class="d-none d-sm-inline">Edit</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger delete-post">
                <i class="bi bi-trash"></i><span class="d-none d-sm-inline">Delete</span>
            </button>
            <button type="button"
                    class="btn btn-sm drag-handle"
                    aria-label="Reorder post. Drag or use arrow keys."
                    title="Drag to reorder or use arrow keys">
                <i class="bi bi-grip-vertical"></i>
            </button>
        </div>

    </article>

    <script type="application/json" class="post-data"><?= json_encode(array(
        'id'        => (int) $post['id'],
        'title'     => $post['title'],
        'content'   => $post['content'],
        'platforms' => array_map('intval', array_column(
            isset($assigned[$post['id']]) ? $assigned[$post['id']] : array(),
            'id'
        )),
    ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

</div>
