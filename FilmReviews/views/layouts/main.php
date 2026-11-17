<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $viewHelper->e($title ?? 'Tokusatsu Universe') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $viewHelper->asset('public/css/index.css?v=3.0') ?>">
</head>
<body class="d-flex flex-column min-vh-100 home-page">

<header>
    <h1 class="main-title">Tokusatsu Universe</h1>
</header>

<nav>
    <ul>
        <li><a href="<?= $viewHelper->routeUrl('film/home', [], true) ?>">Home</a></li>
        <li><a href="<?= $viewHelper->routeUrl('film/list', [], true) ?>">Tokusatsu films</a></li>
    </ul>
</nav>

<main class="flex-grow-1">
    <?= $output ?? ($content ?? '') ?>
</main>

<footer class="site-footer">
    <div class="container text-center">
        <p class="mb-1">&copy; <?= $viewHelper->e($currentYear) ?> <strong class="text-white">Tokusatsu Universe Cinema Reviews</strong></p>
        <p class="small text-secondary mb-0">Course: COMP1842 - Web Programming 2 | University of Greenwich</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $viewHelper->asset('public/js/tab-session.js') ?>"></script>
<script src="<?= $viewHelper->asset('public/js/app.js') ?>"></script>
</body>
</html>
