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

// SB Admin 2 specific assets will be registered in the next step.
// For now, we keep existing Yii asset registration and CDN links,
// but they will likely be replaced or augmented.
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?= Html::encode($this->params['meta_description'] ?? '') ?>">
    <meta name="author" content=""> <!-- Consider making this dynamic -->
    <title><?= Html::encode($this->title) ?></title>

    <?php
    // Register SB Admin 2 Assets
    // Font Awesome (local)
    $this->registerCssFile(Yii::getAlias('@web/sb-admin-2/vendor/fontawesome-free/css/all.min.css'), ['position' => \yii\web\View::POS_HEAD]);

    // Google Fonts for SB Admin 2 (Nunito)
    $this->registerCssFile('https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i', ['position' => \yii\web\View::POS_HEAD]);

    // SB Admin 2 Core CSS
    $this->registerCssFile(Yii::getAlias('@web/sb-admin-2/css/sb-admin-2.min.css'), ['position' => \yii\web\View::POS_HEAD]);

    // Comment out or remove original site-custom.css as it's likely for Bootstrap 5
    // $this->registerCssFile('@web/css/site-custom.css');

    // Yii's AppAsset will handle jQuery. We need to ensure SB Admin 2 scripts are loaded after it.
    // JQuery (local - SB Admin 2 vendor) - Often handled by Yii's AppAsset, ensure correct version if SB Admin 2 requires specific one
    // $this->registerJsFile(Yii::getAlias('@web/sb-admin-2/vendor/jquery/jquery.min.js'), ['position' => \yii\web\View::POS_HEAD]); // Usually loaded by AppAsset

    ?>
    <?php $this->head() ?>
</head>
<body id="page-top">
<?php $this->beginBody() ?>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= Yii::$app->homeUrl ?>">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i> <!-- Placeholder icon -->
                </div>
                <div class="sidebar-brand-text mx-3"><?= Yii::$app->name ?></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="<?= Yii::$app->urlManager->createUrl(['/site/index']) ?>">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Interface
            </div>

            <?php
            // Yii2 Nav widget integration for sidebar
            // This is a simplified example and will need refinement
            // to match the collapsible structure of SB Admin 2 if desired.
            $menuItems = [];
            if (Yii::$app->user->isGuest) {
                $menuItems[] = ['label' => 'Home', 'url' => ['/site/index'], 'icon' => 'fas fa-fw fa-home'];
                $menuItems[] = ['label' => 'About', 'url' => ['/site/about'], 'icon' => 'fas fa-fw fa-info-circle'];
                $menuItems[] = ['label' => 'Contact', 'url' => ['/site/contact'], 'icon' => 'fas fa-fw fa-envelope'];
                $menuItems[] = ['label' => 'Signup', 'url' => ['/site/signup'], 'icon' => 'fas fa-fw fa-user-plus'];
                $menuItems[] = ['label' => 'Login', 'url' => ['/site/login'], 'icon' => 'fas fa-fw fa-sign-in-alt'];
            } else {
                $menuItems[] = ['label' => 'Projects', 'url' => ['/project/index'], 'icon' => 'fas fa-fw fa-project-diagram'];
                $menuItems[] = ['label' => 'Tasks', 'url' => ['/task/index'], 'icon' => 'fas fa-fw fa-tasks'];

                // Reports Menu Item
                $reportMenuItems = [
                    ['label' => 'User Tasks', 'url' => ['/report/user-tasks']],
                    ['label' => 'Task Status', 'url' => ['/report/task-status']],
                    ['label' => 'Task Priority', 'url' => ['/report/task-priority']],
                    ['label' => 'Task History', 'url' => ['/report/task-history']],
                    ['label' => 'User Workload', 'url' => ['/report/user-workload-summary']],
                    ['label' => 'Project Progress', 'url' => ['/report/project-progress-overview']],
                ];
                $menuItems[] = [
                    'label' => 'Reports',
                    'icon' => 'fas fa-fw fa-chart-bar',
                    'url' => '#', // Placeholder for collapsible item
                    'options' => ['class' => 'nav-item'], // Ensure this class is added to the <li>
                    'linkOptions' => [
                        'class' => 'nav-link collapsed',
                        'href' => '#', // Important for collapse functionality
                        'data-toggle' => 'collapse',
                        'data-target' => '#collapseReports', // Unique ID for this collapsible menu
                        'aria-expanded' => 'false', // Start collapsed
                        'aria-controls' => 'collapseReports'
                    ],
                    'items' => $reportMenuItems,
                    'active' => Yii::$app->controller->id === 'report', // Active if current controller is 'report'
                ];


                if (Yii::$app->user->can('admin')) {
                    $menuItems[] = ['label' => 'User Management', 'url' => ['/user-management/index'], 'icon' => 'fas fa-fw fa-users-cog'];
                    $menuItems[] = ['label' => 'Data Management', 'url' => ['/data/index'], 'icon' => 'fas fa-database'];
                }
                // Example of a collapsible menu item (needs JS and specific CSS from SB Admin 2 to work)
                /*
                $menuItems[] = [
                    'label' => 'Components',
                    'icon' => 'fas fa-fw fa-cog',
                    'url' => '#',
                    'options' => ['class' => 'nav-item'],
                    'linkOptions' => [
                        'class' => 'nav-link collapsed',
                        'data-toggle' => 'collapse',
                        'data-target' => '#collapseTwo',
                        'aria-expanded' => 'true',
                        'aria-controls' => 'collapseTwo'
                    ],
                    'items' => [
                        ['label' => 'Buttons', 'url' => ['/site/buttons-page'], 'parent' => '#collapseTwo', 'parentOptions' => ['class' => 'bg-white py-2 collapse-inner rounded']], // Fictional URL
                        ['label' => 'Cards', 'url' => ['/site/cards-page'], 'parent' => '#collapseTwo'], // Fictional URL
                    ],
                ];
                */
            }

            foreach ($menuItems as $item) {
                $icon = $item['icon'] ?? 'fas fa-fw fa-circle';
                $processedUrl = Yii::$app->urlManager->createUrl($item['url']); // Renamed to avoid conflict, used for non-collapsible

                if (isset($item['items'])) { // Collapsible item
                    $isParentExplicitlyActive = isset($item['active']) && $item['active'];

                    $parentLiClasses = ['nav-item'];
                    if ($isParentExplicitlyActive) {
                        $parentLiClasses[] = 'active';
                    }

                    $currentLinkOptions = $item['linkOptions'] ?? [];
                    $currentLinkClassesInput = $currentLinkOptions['class'] ?? 'nav-link';
                    if (is_array($currentLinkClassesInput)) {
                        $currentLinkClasses = $currentLinkClassesInput;
                    } else {
                        $currentLinkClasses = explode(' ', (string)$currentLinkClassesInput);
                    }

                    $submenuDivClasses = ['collapse'];
                    $shouldExpand = $isParentExplicitlyActive;

                    if (!$shouldExpand) {
                        // Check if a child is active to expand the menu
                        foreach ($item['items'] as $subItem) {
                            $subUrl = Yii::$app->urlManager->createUrl($subItem['url']);
                            if (Yii::$app->request->url == $subUrl) {
                                $shouldExpand = true;
                                break;
                            }
                        }
                    }

                    if ($shouldExpand) {
                        $currentLinkOptions['aria-expanded'] = 'true';
                        $currentLinkClasses = array_filter($currentLinkClasses, function($c) { return $c !== 'collapsed'; });
                        if (empty($currentLinkClasses) || !in_array('nav-link', $currentLinkClasses)) { $currentLinkClasses[] = 'nav-link';} // Ensure nav-link is present
                        $submenuDivClasses[] = 'show';
                    } else {
                        if (!in_array('collapsed', $currentLinkClasses)) {
                            $currentLinkClasses[] = 'collapsed';
                        }
                        $currentLinkOptions['aria-expanded'] = 'false';
                    }
                    $currentLinkOptions['class'] = implode(' ', array_unique(array_filter($currentLinkClasses)));

                    $currentLinkOptions['href'] = $item['url'] ?? '#'; // Collapsible parent URL is usually #
                    // Ensure data-toggle and data-target are from the original item definition
                    if(isset($item['linkOptions']['data-toggle'])) $currentLinkOptions['data-toggle'] = $item['linkOptions']['data-toggle']; else $currentLinkOptions['data-toggle'] = 'collapse';
                    if(isset($item['linkOptions']['data-target'])) $currentLinkOptions['data-target'] = $item['linkOptions']['data-target'];
                    // aria-controls should also be preserved or correctly set
                    if(isset($item['linkOptions']['aria-controls'])) $currentLinkOptions['aria-controls'] = $item['linkOptions']['aria-controls'];


                    echo '<li class="' . implode(' ', $parentLiClasses) . '">';
                    echo Html::a('<i class="' . $icon . '"></i><span>' . $item['label'] . '</span>', $currentLinkOptions['href'], $currentLinkOptions);

                    $divId = '';
                    // Ensure divId is derived from data-target, which should be in $item['linkOptions']
                    if (isset($item['linkOptions']['data-target'])) {
                        $divId = str_replace('#','',$item['linkOptions']['data-target']);
                    }
                    $ariaLabelledBy = $item['linkOptions']['aria-controls'] ?? $divId; // Prefer aria-controls if defined in item

                    echo '<div id="' . $divId . '" class="' . implode(' ', $submenuDivClasses) . '" aria-labelledby="'.$ariaLabelledBy.'" data-parent="#accordionSidebar">';
                    echo '<div class="bg-white py-2 collapse-inner rounded">';
                    foreach ($item['items'] as $subItem) {
                        $subUrl = Yii::$app->urlManager->createUrl($subItem['url']);
                        $subActive = (Yii::$app->request->url == $subUrl) ? 'active' : ''; // This is from previous step
                        echo Html::a($subItem['label'], $subUrl, ['class' => 'collapse-item ' . $subActive]);
                    }
                    echo '</div></div>';
                    echo '</li>';
                } else { // Regular item
                    $isActive = (isset($item['active']) && $item['active']) || (Yii::$app->request->url == $processedUrl && $processedUrl !== '#');
                    $liClasses = ['nav-item'];
                    if ($isActive) {
                        $liClasses[] = 'active';
                    }
                    echo '<li class="' . implode(' ', $liClasses) . '">';
                    echo Html::a('<i class="' . $icon . '"></i><span>' . $item['label'] . '</span>', $processedUrl, ['class' => 'nav-link']);
                    echo '</li>';
                }
            }
            ?>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Search (Optional - can be removed or adapted) -->
                    <form
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - User Information -->
                        <?php if (!Yii::$app->user->isGuest): ?>
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= Html::encode(Yii::$app->user->identity->username) ?></span>
                                <!-- <img class="img-profile rounded-circle" src="img/undraw_profile.svg"> Consider adding user profile image logic -->
                                <i class="fas fa-user-circle fa-2x"></i> <!-- Placeholder icon -->
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#"> <!-- Link to user profile page -->
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="#"> <!-- Link to settings page -->
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="#"> <!-- Link to activity log page -->
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
                                </a>
                                <div class="dropdown-divider"></div>
                                <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex dropdown-item']) ?>
                                <?= Html::submitButton(
                                    '<i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout',
                                    ['class' => 'btn btn-link logout p-0 m-0 align-baseline']
                                ) ?>
                                <?= Html::endForm() ?>
                            </div>
                        </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= Yii::$app->urlManager->createUrl(['/site/login']) ?>">
                                    <span class="mr-2 d-none d-lg-inline text-gray-600 small">Login</span>
                                    <i class="fas fa-sign-in-alt"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                     <?php if (!empty($this->params['breadcrumbs'])): ?>
                        <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
                    <?php endif ?>
                    <?= Alert::widget() ?>
                    <?= $content ?>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></span>
                        <span class="ml-4"><?= Yii::powered() ?></span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal (SB Admin 2 specific - can be used or removed) -->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                     <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline'])
                     . Html::submitButton(
                         'Logout',
                         ['class' => 'btn btn-primary']
                     )
                     . Html::endForm() ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Generic AJAX Modal (from original main.php) -->
    <div class="modal fade" id="ajaxModal" tabindex="-1" aria-labelledby="ajaxModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ajaxModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Generic AJAX Modal -->

<?php
// SB Admin 2 Core JavaScript
// YiiAsset (from AppAsset) already provides jQuery.
// Bootstrap core JavaScript (SB Admin 2 uses Bootstrap 4)
$this->registerJsFile(Yii::getAlias('@web/sb-admin-2/vendor/bootstrap/js/bootstrap.bundle.min.js'), ['position' => \yii\web\View::POS_END, 'depends' => [\yii\web\YiiAsset::class]]);

// Core plugin JavaScript (jQuery Easing)
$this->registerJsFile(Yii::getAlias('@web/sb-admin-2/vendor/jquery-easing/jquery.easing.min.js'), ['position' => \yii\web\View::POS_END, 'depends' => [\yii\web\YiiAsset::class]]);

// Custom scripts for all pages (SB Admin 2 theme scripts)
$this->registerJsFile(Yii::getAlias('@web/sb-admin-2/js/sb-admin-2.min.js'), ['position' => \yii\web\View::POS_END, 'depends' => [\yii\web\YiiAsset::class]]); // Depends on jQuery and expects Bootstrap JS to be loaded

// Page level plugins (Example: Chart.js - optional, include if charts are used)
// $this->registerJsFile(Yii::getAlias('@web/sb-admin-2/vendor/chart.js/Chart.min.js'), ['position' => \yii\web\View::POS_END, 'depends' => [\yii\web\YiiAsset::class]]);
// Page level custom scripts (Example: chart demos - optional)
// $this->registerJsFile(Yii::getAlias('@web/sb-admin-2/js/demo/chart-area-demo.js'), ['position' => \yii\web\View::POS_END, 'depends' => [Yii::getAlias('@web/sb-admin-2/vendor/chart.js/Chart.min.js')]]);
// $this->registerJsFile(Yii::getAlias('@web/sb-admin-2/js/demo/chart-pie-demo.js'), ['position' => \yii\web\View::POS_END, 'depends' => [Yii::getAlias('@web/sb-admin-2/vendor/chart.js/Chart.min.js')]]);

$this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
