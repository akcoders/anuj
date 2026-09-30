(function ($) {
  'use strict';
  if (!$) return;

  const form = $('#import-form');
  if (!form.length) return;
  const csrfName = $('meta[name="csrf-name"]').attr('content');
  const base = $('body').data('base-url');
  const selected = new Set();
  let preview = null;
  let pendingPreview = null;
  let previewVersion = 0;
  let importing = false;
  let hideDuplicates = false;

  function escapeHtml(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (character) {
      return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[character];
    });
  }

  function toast(message, success) {
    const element = document.getElementById('app-toast');
    if (!element || !window.bootstrap) return;
    element.classList.remove('text-bg-success', 'text-bg-danger');
    element.classList.add(success ? 'text-bg-success' : 'text-bg-danger');
    $(element).find('.toast-body').text(message);
    bootstrap.Toast.getOrCreateInstance(element).show();
  }
  window.appToast = toast;

  function updateToken(response) {
    const hash = response && response.data && response.data.csrf_hash;
    if (hash) $('meta[name="csrf-hash"]').attr('content', hash);
  }

  function request(url, data) {
    data[csrfName] = $('meta[name="csrf-hash"]').attr('content');
    return $.ajax({url: url, method: 'POST', data: data, dataType: 'json'})
      .done(updateToken)
      .fail(function (xhr, status) {
        if (status === 'abort') return;
        updateToken(xhr.responseJSON);
        toast(xhr.responseJSON ? xhr.responseJSON.message : 'Request failed. Refresh and try again.', false);
      });
  }

  function loading(button, active) {
    $(button).prop('disabled', active).find('.spinner-border').toggleClass('d-none', !active);
  }

  function clearPreview() {
    previewVersion++;
    // Keep an in-flight request tracked until its rotated CSRF token arrives.
    if (!pendingPreview) loading($('#feed-preview-button'), false);
    preview = null;
    selected.clear();
    hideDuplicates = false;
    $('#feed-preview, #feed-import-complete').prop('hidden', true);
    $('#feed-preview-list').empty();
    $('#feed-remove-duplicates').attr('aria-pressed', 'false').removeClass('is-active');
  }

  function cardHtml(item, number) {
    const duplicate = Boolean(item.duplicate);
    const content = String(item.content || '');
    const excerpt = content ? Array.from(content).slice(0, 260).join('') : 'No description supplied by this feed.';
    const lengthy = Array.from(content).length > 260;
    const image = item.image_url
      ? '<img src="' + escapeHtml(item.image_url) + '" alt="" loading="lazy">'
      : '<span class="feed-image-placeholder"><i class="bi bi-newspaper" aria-hidden="true"></i></span>';
    const badge = duplicate
      ? '<span class="feed-item-badge feed-item-badge-duplicate"><i class="bi bi-exclamation-diamond" aria-hidden="true"></i> Duplicate · ' + escapeHtml(item.duplicate_reason) + '</span>'
      : '<span class="feed-item-badge feed-item-badge-new"><i class="bi bi-sparkles" aria-hidden="true"></i> New story</span>';
    const action = duplicate
      ? '<span class="feed-item-skipped"><i class="bi bi-shield-check" aria-hidden="true"></i> Skipped on import</span>'
      : '<button type="button" class="feed-pick" aria-pressed="false" aria-label="Select ' + escapeHtml(item.title || 'story') + '"><i class="bi bi-plus-lg" aria-hidden="true"></i><span>Select</span></button>';

    return '<article class="feed-preview-item' + (duplicate ? ' is-duplicate' : '') + '" data-index="' + item.index + '">' +
      '<div class="feed-preview-image">' + image + '</div>' +
      '<div class="feed-preview-body"><div class="feed-preview-item-top"><span class="feed-item-number">' + String(number).padStart(2, '0') + '</span>' + badge + '</div>' +
      '<h3>' + escapeHtml(item.title || 'Untitled story') + '</h3>' +
      '<div class="feed-item-meta"><span><i class="bi bi-calendar3" aria-hidden="true"></i> ' + escapeHtml(item.pub_date) + '</span><span><i class="bi bi-text-paragraph" aria-hidden="true"></i> ' + Number(item.char_count || 0).toLocaleString() + ' characters</span></div>' +
      '<p class="feed-item-excerpt">' + escapeHtml(excerpt) + (lengthy ? '…' : '') + '</p>' +
      (lengthy ? '<p class="feed-item-full" hidden>' + escapeHtml(content) + '</p><button type="button" class="feed-expand" aria-expanded="false">Read full text <i class="bi bi-chevron-down" aria-hidden="true"></i></button>' : '') +
      '</div><div class="feed-item-action">' + action + '</div></article>';
  }

  function updateSelection() {
    if (!preview) return;
    const count = selected.size;
    $('#feed-selected-count').text(count + ' selected');
    $('#feed-selected-button-count').text('(' + count + ')');
    $('#feed-import-selected').prop('disabled', importing || count === 0);
    $('#feed-import-all').prop('disabled', importing || preview.available === 0);
    $('#feed-select-all').prop('disabled', importing || preview.available === 0);
    $('#feed-clear-selection').prop('disabled', importing || count === 0);
    $('#feed-remove-duplicates').prop('disabled', importing || preview.duplicates === 0);
    $('#feed-preview-list .feed-pick').each(function () {
      const index = Number($(this).closest('.feed-preview-item').data('index'));
      const active = selected.has(index);
      $(this).toggleClass('is-selected', active).attr('aria-pressed', String(active))
        .find('i').attr('class', active ? 'bi bi-check-lg' : 'bi bi-plus-lg');
      $(this).find('span').text(active ? 'Selected' : 'Select');
      $(this).closest('.feed-preview-item').toggleClass('is-selected', active);
    });
  }

  function renderPreview(data) {
    preview = data;
    selected.clear();
    hideDuplicates = false;
    $('#preview-total').text(data.total);
    $('#preview-available').text(data.available);
    $('#preview-duplicates').text(data.duplicates);
    $('#feed-all-button-count').text('(' + data.available + ')');
    $('#feed-duplicate-notice').prop('hidden', data.duplicates === 0);
    $('#feed-remove-duplicates').attr('aria-pressed', 'false').removeClass('is-active').html('<i class="bi bi-stars" aria-hidden="true"></i> Remove duplicates');
    $('#feed-preview-list').html(data.items.map(function (item, index) { return cardHtml(item, index + 1); }).join(''));
    $('#feed-preview-list img').on('error', function () {
      $(this).replaceWith('<span class="feed-image-placeholder"><i class="bi bi-newspaper" aria-hidden="true"></i></span>');
    });
    $('#feed-import-complete').prop('hidden', true);
    $('#feed-preview').prop('hidden', false);
    updateSelection();
    document.getElementById('feed-preview').scrollIntoView({behavior: 'smooth', block: 'start'});
  }

  function feedRequestData() {
    const input = $('#feed-url');
    const value = $.trim(input.val());
    input.removeClass('is-invalid');
    if (!/^https?:\/\/.+/i.test(value)) {
      $('#url-error').text(value ? 'URL must start with http:// or https://.' : 'Please enter an RSS feed URL.');
      input.addClass('is-invalid').trigger('focus');
      return null;
    }
    return {feed_url: value, sort_mode: form.find('[name="sort_mode"]:checked').val()};
  }

  function showImportResult(response) {
    const result = response.data || {};
    const count = Number(result.imported || 0);
    const skipped = Number(result.skipped || 0);
    const checked = result.total === undefined ? '' : 'Checked ' + Number(result.total) + ' feed stories. ';
    $('#feed-complete-title').text(count ? count + (count === 1 ? ' post is ready.' : ' posts are ready.') : 'Everything is up to date.');
    $('#feed-complete-detail').text(checked + (skipped
      ? skipped + (skipped === 1 ? ' duplicate was skipped.' : ' duplicates were skipped.')
      : 'New stories have been added to the library.'));
    $('#feed-preview').prop('hidden', true);
    $('#feed-import-complete').prop('hidden', false);
    document.getElementById('feed-import-complete').scrollIntoView({behavior: 'smooth', block: 'start'});
    preview = null;
    selected.clear();
    toast(response.message, true);
  }

  form.on('submit', function (event) {
    event.preventDefault();
    if (importing || pendingPreview) return;
    const data = feedRequestData();
    if (!data) return;
    clearPreview();
    const version = previewVersion;
    const button = $('#feed-preview-button');
    loading(button, true);
    $('#feed-import-direct').prop('disabled', true);
    pendingPreview = request(form.attr('action'), data)
      .done(function (response) {
        if (version === previewVersion) renderPreview(response.data);
      }).always(function () {
        pendingPreview = null;
        loading(button, false);
        $('#feed-import-direct').prop('disabled', importing);
      });
  });

  form.on('input change', '#feed-url, [name="sort_mode"]', function () {
    if (preview || pendingPreview || !$('#feed-import-complete').prop('hidden')) clearPreview();
  });

  $('#feed-import-direct').on('click', function () {
    if (importing || pendingPreview) return;
    const data = feedRequestData();
    if (!data) return;
    clearPreview();
    importing = true;
    const button = $(this);
    form.find('input, button').prop('disabled', true);
    loading(button, true);
    request(button.data('action'), data).done(showImportResult).always(function () {
      importing = false;
      form.find('input, button').prop('disabled', false);
      loading(button, false);
    });
  });

  $('#feed-preview-list').on('click', '.feed-pick', function () {
    if (importing) return;
    const index = Number($(this).closest('.feed-preview-item').data('index'));
    if (selected.has(index)) selected.delete(index);
    else selected.add(index);
    updateSelection();
  }).on('click', '.feed-expand', function () {
    const button = $(this), expanded = button.attr('aria-expanded') === 'true';
    button.attr('aria-expanded', String(!expanded)).html(expanded
      ? 'Read full text <i class="bi bi-chevron-down" aria-hidden="true"></i>'
      : 'Show less <i class="bi bi-chevron-up" aria-hidden="true"></i>');
    button.siblings('.feed-item-excerpt').prop('hidden', !expanded);
    button.siblings('.feed-item-full').prop('hidden', expanded);
  });

  $('#feed-select-all').on('click', function () {
    if (!preview || importing) return;
    preview.items.forEach(function (item) { if (!item.duplicate) selected.add(Number(item.index)); });
    updateSelection();
  });
  $('#feed-clear-selection').on('click', function () { selected.clear(); updateSelection(); });
  $('#feed-remove-duplicates').on('click', function () {
    hideDuplicates = !hideDuplicates;
    $(this).attr('aria-pressed', String(hideDuplicates)).toggleClass('is-active', hideDuplicates)
      .html(hideDuplicates ? '<i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Show duplicates' : '<i class="bi bi-stars" aria-hidden="true"></i> Remove duplicates');
    $('#feed-preview-list .is-duplicate').prop('hidden', hideDuplicates);
    $('#feed-duplicate-notice span').text(hideDuplicates
      ? preview.duplicates + ' duplicate ' + (preview.duplicates === 1 ? 'story is' : 'stories are') + ' hidden from this preview. They are always skipped when importing.'
      : 'Duplicates are highlighted below and skipped when importing. Remove duplicates hides them from this preview.');
  });

  function doImport(mode) {
    if (!preview || importing || (mode === 'selected' && !selected.size) || (mode === 'all' && !preview.available)) return;
    const button = $(mode === 'all' ? '#feed-import-all' : '#feed-import-selected');
    importing = true;
    form.find('input, button').prop('disabled', true);
    updateSelection();
    loading(button, true);
    request(base + 'feed/import', {
      token: preview.token,
      mode: mode,
      selected: mode === 'selected' ? Array.from(selected).sort(function (a, b) { return a - b; }) : []
    }).done(showImportResult).always(function () {
      importing = false;
      form.find('input, button').prop('disabled', false);
      loading(button, false);
      updateSelection();
    });
  }

  $('#feed-import-selected').on('click', function () { doImport('selected'); });
  $('#feed-import-all').on('click', function () { doImport('all'); });
})(window.jQuery);
