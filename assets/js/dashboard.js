(function ($) {
  'use strict';
  const container = $('#dashboard-content'); if (!container.length) return;
  function load(url) {
    $.getJSON(url).done(function (response) { container.html(response.data.html); }).fail(function () { window.appToast('Could not load dashboard.', false); });
  }
  container.on('click', '.platform-filter a, .dashboard-page', function (event) {
    event.preventDefault(); const target = new URL(this.href); history.replaceState(null, '', target.href);
    load(target.pathname.replace(/\/dashboard\/?$/, '/dashboard/filter') + target.search);
  });
  container.on('change', '#dashboard-sort select', function () {
    const target = new URL(window.location.href); target.searchParams.set('sort', $('#sort').val()); target.searchParams.set('dir', $('#dir').val()); target.searchParams.delete('page');
    history.replaceState(null, '', target.href); load(target.pathname.replace(/\/dashboard\/?$/, '/dashboard/filter') + target.search);
  });
  $(document).on('postsChanged', function () { load(window.location.pathname.replace(/\/dashboard\/?$/, '/dashboard/filter') + window.location.search); });
})(window.jQuery);
