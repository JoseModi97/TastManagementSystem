<?php

namespace tests\unit\models;

use Yii;
use app\models\SignupForm;
use app\models\User;
use tests\unit\fixtures\UserFixture; // For existing users check
use Codeception\Test\Unit;

class SignupFormTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

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
        // Ensure RBAC 'user' role exists for assignment during signup.
        // This is crucial because SignupForm attempts to assign this role.
        $auth = Yii::$app->authManager;
        if ($auth && !$auth->getRole('user')) { // Check if authManager is configured
            $userRole = $auth->createRole('user');
            $userRole->description = 'Basic authenticated user'; // Match description from RBAC migration
            try {
                $auth->add($userRole);
            } catch (\Exception $e) {
                // Ignore if role already exists from a previous test run's _before without full cleanup
                // This can happen if tests are run multiple times without full DB reset between suite runs.
                // A better approach is full DB cleanup/re-migration by Codeception's Db module.
                if (!$auth->getRole('user')) throw $e; // Rethrow if it still doesn't exist
            }
        }
    }

    protected function _after()
    {
        // Clean up any users created during tests if not handled by fixtures/transactions
        $usernamesToClean = ['newsignup', 'uniquesignup', 'mismatchuser'];
        foreach ($usernamesToClean as $username) {
            $user = User::findByUsername($username);
            if ($user) {
                $auth = Yii::$app->authManager;
                if ($auth) { // Check if authManager is configured
                    $auth->revokeAll($user->id);
                }
                $user->delete();
            }
        }
    }

    public function testSignupCorrect()
    {
        $model = new SignupForm([
            'username' => 'newsignup',
            'email' => 'newsignup@example.com',
            'password' => 'password123',
            'password_repeat' => 'password123',
        ]);

        $user = $model->signup();

        $this->assertInstanceOf(User::class, $user, 'Signup should return a User object.');
        if ($user) { // Proceed only if user object was returned
            $this->assertEquals('newsignup', $user->username);
            $this->assertEquals('newsignup@example.com', $user->email);
            $this->assertTrue($user->validatePassword('password123'));
            $this->assertNotEmpty($user->auth_key);

            // Check if user was saved to DB
            $savedUser = User::findByUsername('newsignup');
            $this->assertInstanceOf(User::class, $savedUser, 'User should be findable in DB after signup.');

            // Check role assignment
            $authManager = Yii::$app->authManager;
            if ($authManager) {
                $assignedRoles = $authManager->getRolesByUser($user->id);
                $this->assertArrayHasKey('user', $assignedRoles, "User should have 'user' role assigned.");
            } else {
                $this->markTestSkipped('AuthManager not available, skipping role assignment check.');
            }
        }
    }

    public function testSignupUsernameTaken()
    {
        $fixtureUser = $this->tester->grabFixture('users', 'user1'); // 'testuser1'
        $model = new SignupForm([
            'username' => $fixtureUser['username'],
            'email' => 'unique_email@example.com',
            'password' => 'password123',
            'password_repeat' => 'password123',
        ]);

        $this->assertNull($model->signup(), 'Signup should fail if username is taken.');
        $this->assertTrue($model->hasErrors('username'));
        $this->assertStringContainsString('has already been taken', $model->getFirstError('username'));
    }

    public function testSignupEmailTaken()
    {
        $fixtureUser = $this->tester->grabFixture('users', 'user1'); // 'testuser1@example.com'
        $model = new SignupForm([
            'username' => 'uniquesignup',
            'email' => $fixtureUser['email'],
            'password' => 'password123',
            'password_repeat' => 'password123',
        ]);

        $this->assertNull($model->signup(), 'Signup should fail if email is taken.');
        $this->assertTrue($model->hasErrors('email'));
        $this->assertStringContainsString('has already been taken', $model->getFirstError('email'));
    }

    public function testSignupPasswordMismatch()
    {
        $model = new SignupForm([
            'username' => 'mismatchuser',
            'email' => 'mismatch@example.com',
            'password' => 'password123',
            'password_repeat' => 'password456',
        ]);

        $this->assertNull($model->signup(), 'Signup should fail if passwords do not match.');
        $this->assertTrue($model->hasErrors('password_repeat'));
        $this->assertStringContainsString("Passwords don't match", $model->getFirstError('password_repeat'));
    }

    public function testSignupValidationRules()
    {
        $model = new SignupForm();

        $model->username = 'u'; // Too short
        $model->email = 'not-an-email';
        // Assuming min password length is 8 from SignupForm (Yii::$app->params['user.passwordMinLength'] ?? 8)
        $model->password = 'short';
        $model->password_repeat = 'short';

        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('username'), "Username validation failed for short input.");
        $this->assertTrue($model->hasErrors('email'), "Email validation failed for invalid format.");
        $this->assertTrue($model->hasErrors('password'), "Password validation failed for short input.");

        // Test required fields
        $model = new SignupForm();
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('username'), "Username required validation failed.");
        $this->assertTrue($model->hasErrors('email'), "Email required validation failed.");
        $this->assertTrue($model->hasErrors('password'), "Password required validation failed.");
        $this->assertTrue($model->hasErrors('password_repeat'), "Password repeat required validation failed.");
    }
}
