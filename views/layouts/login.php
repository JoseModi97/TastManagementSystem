<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;
use app\assets\AppAsset; // We might not need AppAsset if SB Admin 2 handles all assets

// It's generally better to create a dedicated asset bundle for SB Admin 2 login page assets
// For simplicity here, we'll link them directly.
// Consider creating an LoginAssetBundle for better organization.

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? Yii::$app->name . ' Login']);
$this->registerMetaTag(['name' => 'author', 'content' => 'Your Application Name']); // Replace as needed
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]); // Assuming you have a favicon

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

    <!-- Custom fonts for this template-->
    <link href="<?= Yii::getAlias('@web/startbootstrap-sb-admin-2-gh-pages/vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="<?= Yii::getAlias('@web/startbootstrap-sb-admin-2-gh-pages/css/sb-admin-2.min.css') ?>" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
<?php $this->beginBody() ?>

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <?= $content ?>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="<?= Yii::getAlias('@web/startbootstrap-sb-admin-2-gh-pages/vendor/jquery/jquery.min.js') ?>"></script>
    <script src="<?= Yii::getAlias('@web/startbootstrap-sb-admin-2-gh-pages/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= Yii::getAlias('@web/startbootstrap-sb-admin-2-gh-pages/vendor/jquery-easing/jquery.easing.min.js') ?>"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= Yii::getAlias('@web/startbootstrap-sb-admin-2-gh-pages/js/sb-admin-2.min.js') ?>"></script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
