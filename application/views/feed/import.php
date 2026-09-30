<section class="feed-page" aria-labelledby="feed-page-title">
    <div class="feed-heading">
        <div>
            <p class="feed-eyebrow"><span class="feed-eyebrow-line"></span> Content source</p>
            <h1 id="feed-page-title">Import a feed<span class="feed-heading-dot">.</span></h1>
            <p class="feed-heading-description">Preview every story, spot duplicates, and choose what enters your workspace.</p>
        </div>
    </div>

    <div class="feed-layout">
        <div class="feed-main">
            <div class="feed-card">
                <div class="feed-card-header">
                    <span class="feed-card-symbol" aria-hidden="true"><i class="bi bi-rss"></i></span>
                    <div>
                        <span class="feed-card-kicker">New import</span>
                        <h2>Connect your source</h2>
                        <p>Paste a public RSS or Atom link, then preview its stories or import every new one now.</p>
                    </div>
                </div>

                <form id="import-form" action="<?= site_url('feed/preview') ?>" method="post" novalidate>
                    <div class="feed-form-section">
                        <div class="feed-section-marker" aria-hidden="true">01</div>
                        <div class="feed-section-content">
                            <label for="feed-url" class="feed-label">Feed URL <span class="feed-required">*</span></label>
                            <p class="feed-field-description" id="feed-help">Enter the full link to a public RSS 2.0 or Atom feed.</p>
                            <div class="feed-input-wrap" id="feed-field">
                                <i class="bi bi-link-45deg feed-input-icon" aria-hidden="true"></i>
                                <span class="feed-valid-icon" id="url-check" aria-hidden="true"><i class="bi bi-check-circle-fill"></i></span>
                                <input
                                    id="feed-url"
                                    name="feed_url"
                                    type="url"
                                    class="form-control feed-url-input"
                                    placeholder="https://example.com/feed.xml"
                                    autocomplete="url"
                                    spellcheck="false"
                                    inputmode="url"
                                    pattern="https?://.+"
                                    aria-describedby="feed-help url-error"
                                    required>
                                <div class="invalid-feedback" id="url-error">Please enter a valid RSS feed URL starting with http:// or https://.</div>
                            </div>
                            <div class="feed-formats" aria-label="Supported feed formats">
                                <span><i class="bi bi-check2" aria-hidden="true"></i> RSS 2.0</span>
                                <span><i class="bi bi-check2" aria-hidden="true"></i> Atom</span>
                                <span><i class="bi bi-globe2" aria-hidden="true"></i> Public links</span>
                            </div>
                        </div>
                    </div>

                    <div class="feed-form-divider" aria-hidden="true"></div>

                    <div class="feed-form-section feed-order-section" role="group" aria-labelledby="feed-priority-label">
                        <div class="feed-section-marker" aria-hidden="true">02</div>
                        <div class="feed-section-content">
                            <h3 class="feed-label" id="feed-priority-label">Priority order</h3>
                            <p class="feed-field-description">Choose which imported stories should appear first.</p>
                            <div class="feed-sort-grid">
                                <div class="feed-sort-choice">
                                    <input class="btn-check" type="radio" name="sort_mode" id="newest" value="DESC" checked>
                                    <label class="feed-sort-option" for="newest">
                                        <span class="feed-sort-icon" aria-hidden="true"><i class="bi bi-sort-down"></i></span>
                                        <span class="feed-sort-copy"><strong>Newest first</strong><small>Latest stories lead</small></span>
                                        <i class="bi bi-check-lg feed-sort-check" aria-hidden="true"></i>
                                    </label>
                                </div>
                                <div class="feed-sort-choice">
                                    <input class="btn-check" type="radio" name="sort_mode" id="oldest" value="ASC">
                                    <label class="feed-sort-option" for="oldest">
                                        <span class="feed-sort-icon" aria-hidden="true"><i class="bi bi-sort-up"></i></span>
                                        <span class="feed-sort-copy"><strong>Oldest first</strong><small>Earliest stories lead</small></span>
                                        <i class="bi bi-check-lg feed-sort-check" aria-hidden="true"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="feed-submit-bar">
                        <span class="feed-submit-note"><i class="bi bi-shield-check" aria-hidden="true"></i> Preview to choose posts, or import all new stories immediately.</span>
                        <div class="feed-submit-actions">
                            <button class="btn feed-direct-button" type="button" id="feed-import-direct" data-action="<?= site_url('feed/import_direct') ?>">
                                <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span>
                                <i class="bi bi-cloud-arrow-down" aria-hidden="true"></i>
                                <span class="feed-direct-copy"><strong>Import now</strong><small>Skip preview</small></span>
                            </button>
                            <button class="btn feed-submit-button" type="submit" id="feed-preview-button">
                                <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span>
                                <span class="btn-label">Preview posts</span>
                                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <aside class="feed-side" aria-label="Import guidance">
            <div class="feed-guide-card">
                <span class="feed-side-kicker">How it works</span>
                <h2>From feed to post in three steps.</h2>
                <ol class="feed-guide-list">
                    <li><span class="feed-guide-icon" aria-hidden="true"><i class="bi bi-link-45deg"></i></span><span><strong>Paste a feed link</strong><small>Use a public RSS or Atom URL.</small></span></li>
                    <li><span class="feed-guide-icon" aria-hidden="true"><i class="bi bi-eye"></i></span><span><strong>Review the preview</strong><small>Duplicates are marked before import.</small></span></li>
                    <li><span class="feed-guide-icon" aria-hidden="true"><i class="bi bi-cloud-arrow-down"></i></span><span><strong>Choose your posts</strong><small>Import selected stories or every new one.</small></span></li>
                </ol>
            </div>
            <div class="feed-tip-card">
                <span class="feed-tip-icon" aria-hidden="true"><i class="bi bi-lightning-charge-fill"></i></span>
                <div>
                    <strong>Make it yours</strong>
                    <p>After importing, fine-tune individual post priorities in your content library.</p>
                    <a href="<?= site_url('posts') ?>">Open content library <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </aside>
    </div>

    <section class="feed-preview" id="feed-preview" aria-labelledby="feed-preview-title" hidden>
        <div class="feed-preview-head">
            <div>
                <span class="feed-side-kicker">Your feed at a glance</span>
                <h2 id="feed-preview-title">Review before importing</h2>
                <p>Choose individual posts, select every new post, or import all in one step.</p>
            </div>
            <div class="feed-preview-stats" aria-live="polite">
                <span><strong id="preview-total">0</strong><small>Stories</small></span>
                <span><strong id="preview-available">0</strong><small>New</small></span>
                <span class="feed-stat-duplicate"><strong id="preview-duplicates">0</strong><small>Duplicates</small></span>
            </div>
        </div>
        <div class="feed-preview-toolbar">
            <div class="feed-preview-controls">
                <button type="button" id="feed-select-all" class="feed-control-button"><i class="bi bi-check2-all" aria-hidden="true"></i> Select all new</button>
                <button type="button" id="feed-clear-selection" class="feed-control-button"><i class="bi bi-x-lg" aria-hidden="true"></i> Clear</button>
                <button type="button" id="feed-remove-duplicates" class="feed-control-button feed-duplicate-filter" aria-pressed="false"><i class="bi bi-stars" aria-hidden="true"></i> Remove duplicates</button>
            </div>
            <span class="feed-selected-count" id="feed-selected-count" aria-live="polite">0 selected</span>
        </div>
        <div class="feed-duplicate-notice" id="feed-duplicate-notice" role="status" hidden>
            <i class="bi bi-shield-check" aria-hidden="true"></i>
            <span>Duplicates are highlighted below and skipped when importing. Remove duplicates hides them from this preview.</span>
        </div>
        <div class="feed-preview-list" id="feed-preview-list"></div>
        <div class="feed-import-actions">
            <div class="feed-import-actions-copy"><strong>Ready to bring these stories in?</strong><span>Priority follows the order shown above. Duplicates are skipped.</span></div>
            <div class="feed-import-buttons">
                <button type="button" class="btn feed-import-selected" id="feed-import-selected" disabled><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span><i class="bi bi-check2-circle" aria-hidden="true"></i> Import selected <span id="feed-selected-button-count">(0)</span></button>
                <button type="button" class="btn feed-import-all" id="feed-import-all" disabled><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span><i class="bi bi-cloud-arrow-down" aria-hidden="true"></i> Import all new <span id="feed-all-button-count">(0)</span></button>
            </div>
        </div>
    </section>
    <section class="feed-import-complete" id="feed-import-complete" aria-live="polite" hidden>
        <span class="feed-complete-icon" aria-hidden="true"><i class="bi bi-check2-circle"></i></span>
        <div><span class="feed-side-kicker">Import complete</span><h2 id="feed-complete-title">Your posts are ready.</h2><p id="feed-complete-detail"></p></div>
        <a class="btn feed-complete-link" href="<?= site_url('posts') ?>">View posts <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </section>
</section>
