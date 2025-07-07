<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php
    // Removed integrity and crossorigin attributes to avoid SRI hash mismatch errors
    $this->registerCssFile("https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css", [
        // 'integrity' => 'sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr',
        // 'crossorigin' => 'anonymous',
    ]);
    $this->registerJsFile("https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js", [
        // 'integrity' => 'sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q',
        // 'crossorigin' => 'anonymous',
        'depends' => [\yii\web\JqueryAsset::class],
    ]);
    $this->registerCssFile('@web/css/site-custom.css');
    ?>
    <?php $this->head() ?>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= Yii::$app->homeUrl ?>"><?= Yii::$app->name ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <?php
            $navItems = [];
            if (Yii::$app->user->isGuest) {
                $navItems[] = ['label' => 'Home', 'url' => ['/site/index']];
                $navItems[] = ['label' => 'About', 'url' => ['/site/about']];
                $navItems[] = ['label' => 'Contact', 'url' => ['/site/contact']];
                $navItems[] = ['label' => 'Signup', 'url' => ['/site/signup']];
                $navItems[] = ['label' => 'Login', 'url' => ['/site/login']];
            } else {
                $navItems[] = ['label' => 'Dashboard', 'url' => ['/site/index']];
                $navItems[] = ['label' => 'Projects', 'url' => ['/project/index']];
                $navItems[] = ['label' => 'Tasks', 'url' => ['/task/index']];
                if (Yii::$app->user->can('admin')) {
                    $navItems[] = ['label' => 'User Management', 'url' => ['/user-management/index']];
                }
                $navItems[] = ['label' => 'About', 'url' => ['/site/about']];
                $navItems[] = ['label' => 'Contact', 'url' => ['/site/contact']];
                $navItems[] = '<li class="nav-item">'
                    . Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
                    . Html::submitButton(
                        'Logout (' . Yii::$app->user->identity->username . ')',
                        ['class' => 'nav-link btn btn-link logout']
                    )
                    . Html::endForm()
                    . '</li>';
            }
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav me-auto mb-2 mb-lg-0'],
                'items' => $navItems,
            ]);
            ?>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if (!empty($this->params['breadcrumbs'])): ?>
        <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
    <?php endif ?>
    <?= Alert::widget() ?>
    <?= $content ?>
</div>

<footer id="footer" class="mt-auto py-3 bg-light">
    <div class="container">
        <div class="row text-muted">
            <div class="col-md-6 text-center text-md-start">&copy; My Company <?= date('Y') ?></div>
            <div class="col-md-6 text-center text-md-end"><?= Yii::powered() ?></div>
        </div>
    </div>
</footer>

<!-- Generic AJAX Modal -->
<div class="modal fade" id="ajaxModal" tabindex="-1" aria-labelledby="ajaxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- Or modal-xl, modal-sm or default -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ajaxModalLabel">Modal title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded here by AJAX -->
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <!-- Additional buttons like 'Save changes' can be added here or dynamically by JS if needed -->
            </div>
        </div>
    </div>
</div>
<!-- End Generic AJAX Modal -->

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
