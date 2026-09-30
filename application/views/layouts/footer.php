        </main>
    </div>
</div>
<?php $this->load->view('partials/toast'); ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.appToast = function (message, success) {
    var toast = document.getElementById('app-toast');
    if (!toast || !window.bootstrap) return;
    toast.classList.remove('text-bg-success', 'text-bg-danger');
    toast.classList.add(success ? 'text-bg-success' : 'text-bg-danger');
    toast.querySelector('.toast-body').textContent = message;
    bootstrap.Toast.getOrCreateInstance(toast).show();
};
</script>
<?php if ($active === 'posts'): ?><script src="https://code.jquery.com/ui/1.14.2/jquery-ui.min.js"></script><?php endif; ?>
<?php if ($active === 'feed'): ?><script src="<?= base_url('assets/js/feed.js') ?>"></script><?php endif; ?>
<?php if ($active === 'posts'): ?><script src="<?= base_url('assets/js/posts.js') ?>"></script><?php endif; ?>
<?php if ($active === 'dashboard'): ?><script src="<?= base_url('assets/js/dashboard.js') ?>"></script><?php endif; ?>
</body>
</html>
