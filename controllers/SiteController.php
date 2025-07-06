<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\SignupForm;
use app\models\Project; // Added for dashboard
use app\models\Task; // Added for dashboard

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'index'], // Index is now also controlled for dashboard
                'rules' => [
                    [
                        'actions' => ['logout', 'index'], // Logged in users can access index (dashboard) and logout
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    // Login, Signup, About, Contact, Error are implicitly allowed for guests if not listed here
                    // and no default deny rule is present.
                    // For explicit guest access to index (if it were not a dashboard):
                    // [
                    // 'actions' => ['index', 'login', 'signup', 'contact', 'about', 'error', 'captcha'],
                    // 'allow' => true,
                    // 'roles' => ['?'], // Guest users
                    // ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->render('index');
        }

        // For logged-in users, show dashboard
        $userId = Yii::$app->user->id;

        $projectCount = Project::find()->where(['created_by' => $userId])->count();
        $tasksAssignedCount = Task::find()->where(['assigned_to' => $userId])->count();

        // More complex: tasks in user's projects that are not done
        $activeTasksInOwnedProjectsCount = Task::find()
            ->joinWith('project p')
            ->joinWith('status s')
            ->where(['p.created_by' => $userId])
            ->andWhere(['!=', 's.label', 'Done']) // Assuming 'Done' is the label for completed status
            ->count();

        // Recently Due Tasks (e.g., due in next 7 days or overdue, assigned to user)
        $recentlyDueTasks = Task::find()
            ->where(['assigned_to' => $userId])
            ->andWhere(['is not', 'due_date', null])
            ->andWhere(['<=', 'due_date', date('Y-m-d H:i:s', strtotime('+7 days'))])
            ->joinWith('status s') // to exclude done tasks
            ->andWhere(['!=', 's.label', 'Done'])
            ->orderBy(['due_date' => SORT_ASC])
            ->limit(5)
            ->all();

        return $this->render('dashboard', [
            'projectCount' => $projectCount,
            'tasksAssignedCount' => $tasksAssignedCount,
            'activeTasksInOwnedProjectsCount' => $activeTasksInOwnedProjectsCount,
            'recentlyDueTasks' => $recentlyDueTasks,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please login.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }
}
