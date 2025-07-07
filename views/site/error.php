<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception|\yii\web\HttpException $exception */

use yii\helpers\Html;
use yii\helpers\Url;

// Check if it's a 404 error
$is404 = ($exception instanceof \yii\web\NotFoundHttpException) || (isset($exception->statusCode) && $exception->statusCode == 404);

if ($is404) {
    $this->title = 'Page Not Found';
    // We will output the 404 page structure directly.
    // We need to ensure paths to assets (CSS, JS, images) are correct.
    // Assuming the assets are in `Yii::getAlias('@web/startbootstrap-sb-admin-2-gh-pages/')`
    // This path might need adjustment based on your actual web alias for that directory.
    // For simplicity, I'm constructing them relative to the web root.
    // A more robust Yii way would be to publish these as assets in an AssetBundle.

    $baseUrl = Url::to('@web/startbootstrap-sb-admin-2-gh-pages');

?>
    <!-- Custom fonts for this template-->
    <link href="<?= Html::encode($baseUrl . '/vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="<?= Html::encode($baseUrl . '/css/sb-admin-2.min.css') ?>" rel="stylesheet">

    <!-- The 404 page content is rendered outside the main layout for full control -->
    <?php $this->beginPage() ?>
    <?php $this->head() // Minimal head, main assets are linked above ?>
    <body id="page-top" class="bg-gradient-primary"> <!-- Assuming you want the body tag from template -->

    <div id="wrapper_error_404"> <!-- Changed ID to avoid conflict if main layout has #wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <div class="container-fluid mt-5">
                    <div class="text-center">
                        <div class="error mx-auto" data-text="404">404</div>
                        <p class="lead text-gray-800 mb-5">Page Not Found</p>
                        <p class="text-gray-500 mb-0">It looks like you found a glitch in the matrix...</p>
                        <a href="<?= Url::home() ?>">&larr; Back to Dashboard</a>
                    </div>
                </div>
            </div>
            <footer class="sticky-footer bg-white mt-5"> <!-- Ensure footer is visible -->
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Your Website <?= date('Y') ?></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="<?= Html::encode($baseUrl . '/vendor/jquery/jquery.min.js') ?>"></script>
    <script src="<?= Html::encode($baseUrl . '/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= Html::encode($baseUrl . '/vendor/jquery-easing/jquery.easing.min.js') ?>"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= Html::encode($baseUrl . '/js/sb-admin-2.min.js') ?>"></script>

    <?php $this->endBody() ?>
    </body>
    <?php $this->endPage() ?>

<?php } else {
    // Default error handling for non-404 errors
    $this->title = $name;
?>
    <div class="site-error">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="alert alert-danger">
            <?= nl2br(Html::encode($message)) ?>
        </div>
        <p>
            The above error occurred while the Web server was processing your request.
        </p>
        <p>
            Please contact us if you think this is a server error. Thank you.
        </p>
    </div>
<?php } ?>
