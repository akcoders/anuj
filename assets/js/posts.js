(function ($) {
  'use strict';
  if (!$) return;

  const base = $('body').data('base-url');
  const csrfName = $('meta[name="csrf-name"]').attr('content');
  const views = ['card', 'list'];
  let savingOrder = false;
  let deleteId = null;

  function token() { return $('meta[name="csrf-hash"]').attr('content'); }
  function toast(message, success) {
    const el = document.getElementById('app-toast');
    if (!el) return;
    el.classList.remove('text-bg-success', 'text-bg-danger');
    el.classList.add(success ? 'text-bg-success' : 'text-bg-danger');
    $(el).find('.toast-body').text(message);
    bootstrap.Toast.getOrCreateInstance(el).show();
  }
  window.appToast = toast;

  function request(url, data) {
    if (Array.isArray(data)) data.push({name: csrfName, value: token()});
    else data[csrfName] = token();
    return $.ajax({url: url, method: 'POST', data: data, dataType: 'json'})
      .done(function (response) {
        if (response.data && response.data.csrf_hash) $('meta[name="csrf-hash"]').attr('content', response.data.csrf_hash);
      }).fail(function (xhr) {
        if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.csrf_hash) $('meta[name="csrf-hash"]').attr('content', xhr.responseJSON.data.csrf_hash);
        toast(xhr.responseJSON ? xhr.responseJSON.message : 'Request failed. Refresh the page and try again.', false);
      });
  }
  function loading(button, on) { $(button).prop('disabled', on).find('.spinner-border').toggleClass('d-none', !on); }

  function syncView() {
    const active = document.documentElement.getAttribute('data-posts-view') || 'card';
    $('input[name="posts-view"]').prop('checked', false).filter('[value="' + active + '"]').prop('checked', true);
  }
  syncView();
  $(document).on('change', 'input[name="posts-view"]', function () {
    if (!this.checked || views.indexOf(this.value) === -1) return;
    document.documentElement.setAttribute('data-posts-view', this.value);
    try { localStorage.setItem('postsView', this.value); } catch (error) {}
    const grid = $('#post-grid');
    if (grid.data('ui-sortable')) grid.sortable('refreshPositions');
  });

  function renumberPreview() {
    const first = (Number($('#posts-list').data('page')) - 1) * 10 + 1;
    $('#post-grid > .post-col').not('.ui-sortable-placeholder,.ui-sortable-helper').each(function (index) {
      $(this).find('.priority-badge').text('#' + (first + index));
    });
  }
  function refreshPosts(focusId) {
    const holder = $('#posts-list'); if (!holder.length) return $.Deferred().resolve().promise();
    return $.getJSON(base + 'posts/list', {page: holder.data('page')}).done(function (response) {
      holder.html(response.data.html);
      $('#posts-total').text(response.data.total);
      initSort();
      if (focusId) holder.find('.post-col[data-id="' + focusId + '"] .drag-handle').trigger('focus');
    }).fail(function () { toast('Could not refresh posts.', false); });
  }
  function saveOrder(focusId) {
    if (savingOrder) return;
    const grid = $('#post-grid');
    const ids = grid.children('.post-col').not('.ui-sortable-placeholder,.ui-sortable-helper').map(function () { return $(this).data('id'); }).get();
    if (!ids.length) return;
    savingOrder = true;
    renumberPreview();
    grid.addClass('is-saving');
    if (grid.data('ui-sortable')) grid.sortable('disable');
    $('#reorder-status').text('Saving post priority order.');
    request(base + 'posts/reorder', {ids: ids, page: $('#posts-list').data('page')})
      .done(function () { toast('Priority order saved.', true); $('#reorder-status').text('Priority order saved.'); })
      .always(function () {
        savingOrder = false;
        refreshPosts(focusId);
      });
  }
  function initSort() {
    const grid = $('#post-grid');
    if (!grid.length || !$.fn.sortable) return;
    grid.sortable({
      items: '> .post-col',
      handle: '.drag-handle',
      cancel: 'button:not(.drag-handle),input,textarea,a',
      placeholder: 'post-drop-target',
      forcePlaceholderSize: false,
      tolerance: 'pointer',
      scroll: true,
      scrollSensitivity: 80,
      scrollSpeed: 16,
      revert: 150,
      helper: function (event, item) {
        const size = item[0].getBoundingClientRect();
        item.data('drag-size', {width: size.width, height: size.height});
        return item.clone().css({width: size.width + 'px', height: size.height + 'px'});
      },
      appendTo: '#posts-list',
      start: function (event, ui) {
        grid.addClass('is-sorting');
        const size = ui.item.data('drag-size');
        ui.placeholder.css({height: size.height + 'px', minHeight: '0', width: '100%', boxSizing: 'border-box'});
        $('#reorder-status').text('Choose a drop target to set the post priority.');
      },
      stop: function () { grid.removeClass('is-sorting'); },
      update: function (event, ui) { saveOrder(ui.item.data('id')); }
    });
  }
  initSort();

  $('#posts-list').on('keydown', '.drag-handle', function (event) {
    if (event.key !== 'ArrowUp' && event.key !== 'ArrowDown') return;
    event.preventDefault();
    if (savingOrder) return;
    const card = $(this).closest('.post-col');
    const other = event.key === 'ArrowUp' ? card.prev('.post-col') : card.next('.post-col');
    if (!other.length) return;
    if (event.key === 'ArrowUp') card.insertBefore(other);
    else card.insertAfter(other);
    saveOrder(card.data('id'));
  });
  $('#posts-list').on('click', '.pagination a', function (event) {
    event.preventDefault();
    const page = new URL(this.href).searchParams.get('page') || 1;
    $('#posts-list').data('page', page);
    history.replaceState(null, '', this.href);
    refreshPosts();
  });

  $('#posts-list').on('click', '.edit-post', function () {
    const card = $(this).closest('.post-col');
    const post = JSON.parse(card.find('.post-data').text());
    $('#edit-id').val(post.id); $('#edit-post-title').val(post.title); $('#edit-content').val(post.content);
    $('.platform-check').each(function () { $(this).prop('checked', post.platforms.includes(Number(this.value))); });
    $('#edit-form .is-invalid').removeClass('is-invalid'); updateCount();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('edit-modal')).show();
  });
  function updateCount() {
    const count = Array.from($('#edit-content').val()).length;
    $('#edit-count').text(count);
    const overLimit = [];
    $('.platform-check').each(function () {
      const counter = $(this).closest('label').find('.platform-counter');
      const limit = Number(this.dataset.max);
      const over = this.checked && count > limit;
      counter.text(this.checked ? count + ' / ' + limit : limit + ' max').toggleClass('over-limit', over);
      $(this).closest('.platform-option').toggleClass('is-over-limit', over);
      if (over) overLimit.push(this.dataset.name + ' (' + (count - limit) + ' over)');
    });
    $('#platform-limit-alert').toggleClass('d-none', !overLimit.length).find('span').text(overLimit.length ? 'Character limit exceeded for ' + overLimit.join(', ') + '. You can still save this post.' : '');
  }
  $('#edit-content').on('input', updateCount);
  $('.platform-check').on('change', updateCount);
  $('#edit-form').on('submit', function (event) {
    event.preventDefault();
    const form = this, title = $('#edit-post-title'), content = $('#edit-content');
    title.toggleClass('is-invalid', !title.val().trim());
    content.toggleClass('is-invalid', !content.val().trim());
    if (!title.val().trim() || !content.val().trim()) return;
    const button = $(form).find('[type="submit"]'); loading(button, true);
    request(base + 'posts/update', $(form).serializeArray()).done(function (response) {
      const id = $('#edit-id').val();
      $('#posts-list .post-col[data-id="' + id + '"]').replaceWith(response.data.html);
      bootstrap.Modal.getOrCreateInstance(document.getElementById('edit-modal')).hide();
      toast(response.data.warnings && response.data.warnings.length ? 'Saved. Limit exceeded for ' + response.data.warnings.join(', ') + '.' : 'Post updated.', true);
      $(document).trigger('postsChanged');
    }).always(function () { loading(button, false); });
  });

  $('#posts-list').on('click', '.delete-post', function () {
    deleteId = $(this).closest('.post-col').data('id');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('delete-modal')).show();
  });
  $('#confirm-delete').on('click', function () {
    const button = this; loading(button, true);
    request(base + 'posts/delete', {id: deleteId}).done(function (response) {
      bootstrap.Modal.getOrCreateInstance(document.getElementById('delete-modal')).hide();
      $('#posts-list .post-col[data-id="' + deleteId + '"]').fadeOut(180, function () { refreshPosts(); });
      $('#posts-total').text(response.data.total);
      toast('Post deleted.', true);
      $(document).trigger('postsChanged');
    }).always(function () { loading(button, false); });
  });
})(window.jQuery);
