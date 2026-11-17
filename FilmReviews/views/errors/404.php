<div class="container py-5 text-center">
    <div class="card bg-dark bg-opacity-75 text-white border border-danger shadow-lg mx-auto" style="max-width: 560px; border-radius: 20px 0 20px 0 !important;">
        <div class="card-body p-5">
            <h1 class="display-4 fw-bold text-danger mb-3">404</h1>
            <h3 class="text-warning fw-bold mb-3">Page Not Found</h3>
            <p class="text-light opacity-75 mb-4">
                The requested route (<code class="text-warning bg-black bg-opacity-50 px-2 py-1 rounded"><?= $viewHelper->e($route ?? '') ?></code>) does not exist inside Tokusatsu Universe.
            </p>
            <a href="<?= $viewHelper->routeUrl('film/list') ?>" class="btn btn-warning fw-bold px-4">
                Return to Film Catalog
            </a>
        </div>
    </div>
</div>
