<?php

namespace tests\unit\models;

use Yii;
use app\models\LoginForm;
use app\models\User; // Needed for interacting with User model if not using fixtures directly in LoginForm model
use tests\unit\fixtures\UserFixture; // Correct path to fixture
use Codeception\Test\Unit;

class LoginFormTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    private $model;

    public function _fixtures()
    {
        return [
            'users' => [
                'class' => UserFixture::class,
                // dataFile will be loaded from UserFixture::init()
            ],
        ];
    }

    protected function _before()
    {
        // It's good practice to ensure Yii::$app->user is clean before each test
        // or if relying on its state, set it up.
        // Codeception's Yii2 module often handles parts of this.
        // For unit tests, directly instantiating LoginForm is typical.
    }

    protected function _after()
    {
        // Logout user after each test if login was successful to ensure clean state for next test
        if (Yii::$app->user && !Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
        }
    }

    public function testLoginNoUser()
    {
        $this->model = new LoginForm([
            'username' => 'not_existing_username',
            'password' => 'any_password',
        ]);

        $this->assertFalse($this->model->login(), 'Model should not login with non-existing username.');
        $this->assertTrue(Yii::$app->user->isGuest, 'User should be guest after failed login.');
        // LoginForm adds error to password field for "Incorrect username or password."
        $this->assertTrue($this->model->hasErrors('password'), 'Model should have error on password field for non-existing user.');
    }

    public function testLoginWrongPassword()
    {
        // Access fixture data
        $fixtureUser = $this->tester->grabFixture('users', 'user1');

        $this->model = new LoginForm([
            'username' => $fixtureUser['username'], // 'testuser1'
            'password' => 'wrong_password',
        ]);

        $this->assertFalse($this->model->login(), 'Model should not login with wrong password.');
        $this->assertTrue(Yii::$app->user->isGuest, 'User should be guest after failed login with wrong password.');
        $this->assertTrue($this->model->hasErrors('password'), 'Model should have error on password field for wrong password.');
        $this->assertStringContainsString('Incorrect username or password.', $this->model->getFirstError('password'));
    }

    public function testLoginCorrect()
    {
        $fixtureUser = $this->tester->grabFixture('users', 'user1'); // 'testuser1' with 'password123'

        $this->model = new LoginForm([
            'username' => $fixtureUser['username'],
            'password' => 'password123', // Correct password for fixture user
        ]);

        $this->assertTrue($this->model->login(), 'Model should login with correct credentials.');
        $this->assertFalse(Yii::$app->user->isGuest, 'User should not be guest after successful login.');
        $this->assertFalse($this->model->hasErrors('password'), 'Model should not have error on password field for correct login.');
        $this->assertEquals($fixtureUser['id'], Yii::$app->user->id, 'Logged in user ID should match fixture user ID.');
    }

    public function testLoginUsernameRequired()
    {
        $this->model = new LoginForm(['password' => 'anypassword']);
        $this->assertFalse($this->model->validate(['username']));
        $this->assertTrue($this->model->hasErrors('username'));
    }

    public function testLoginPasswordRequired()
    {
        $this->model = new LoginForm(['username' => 'anyuser']);
        $this->assertFalse($this->model->validate(['password']));
        $this->assertTrue($this->model->hasErrors('password'));
    }
}
