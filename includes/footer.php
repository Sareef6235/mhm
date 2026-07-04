<div class="toast-container position-fixed top-0 end-0 p-4">
    <div id="appToast" class="toast app-toast" role="status" aria-live="polite">
        <div class="toast-icon"><i class="bi bi-check2-circle"></i></div>
        <div class="toast-body">Ready</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
</div>
<footer class="app-footer">
    <span>© <?= date('Y') ?> <?= e(setting('website_name', 'SmartID Pro')) ?></span>
    <span>Premium secure identity management</span>
</footer>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
