<?php

namespace tests\unit\models;

use Yii;
use app\models\User;
use tests\unit\fixtures\UserFixture; // Correct path to fixture
use Codeception\Test\Unit;

class UserTest extends Unit
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
                // Data file is specified in UserFixture::init()
            ],
        ];
    }

    public function _before()
    {
        // It's good practice to ensure the application component is configured for tests
        // especially for things like security component used in User model.
        // This is usually handled by Codeception's Yii2 module loading test.php config.
    }

    // Test validation rules
    public function testUsernameIsRequired()
    {
        $model = new User();
        $model->email = 'test@example.com';
        $model->setPassword('password123');
        $this->assertFalse($model->validate(['username']));
        $this->assertTrue($model->hasErrors('username'));
    }

    public function testEmailIsRequired()
    {
        $model = new User();
        $model->username = 'newuser';
        $model->setPassword('password123');
        $this->assertFalse($model->validate(['email']));
        $this->assertTrue($model->hasErrors('email'));
    }

    public function testEmailIsUnique()
    {
        $this->tester->grabFixture('users', 'user1'); // Ensure fixtures are loaded
        $model = new User();
        $model->username = 'anotheruser';
        $model->email = 'testuser1@example.com'; // Email from fixture user1
        $model->setPassword('password123');
        $this->assertFalse($model->validate(['email']));
        $this->assertTrue($model->hasErrors('email'));
        $this->assertStringContainsString('has already been taken', $model->getFirstError('email'));
    }

    public function testUsernameIsUnique()
    {
        $this->tester->grabFixture('users', 'user1');
        $model = new User();
        $model->username = 'testuser1'; // Username from fixture user1
        $model->email = 'another@example.com';
        $model->setPassword('password123');
        $this->assertFalse($model->validate(['username']));
        $this->assertTrue($model->hasErrors('username'));
        $this->assertStringContainsString('has already been taken', $model->getFirstError('username'));
    }

    public function testEmailFormat()
    {
        $model = new User();
        $model->email = 'not-an-email';
        $this->assertFalse($model->validate(['email']));
        $this->assertTrue($model->hasErrors('email'));
        $this->assertStringContainsString('is not a valid email address', $model->getFirstError('email'));

        $model->email = 'valid@example.com';
        $model->validate(['email']); // Validate only email
        $this->assertFalse($model->hasErrors('email'), 'Email validation failed for a valid email: ' . print_r($model->getErrors('email'), true));

    }

    // Test password hashing and validation
    public function testPasswordSettingAndValidation()
    {
        $model = new User();
        $password = 'mySecretPassword';
        $model->setPassword($password);
        $this->assertNotEmpty($model->password_hash);
        $this->assertNotEquals($password, $model->password_hash);
        $this->assertTrue(Yii::$app->security->validatePassword($password, $model->password_hash));
        $this->assertTrue($model->validatePassword($password));
        $this->assertFalse($model->validatePassword('wrongPassword'));
    }

    // Test IdentityInterface methods
    public function testFindIdentity()
    {
        $fixtureUser = $this->tester->grabFixture('users', 'user1');
        $foundUser = User::findIdentity($fixtureUser['id']);
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($fixtureUser['username'], $foundUser->username);

        $this->assertNull(User::findIdentity(999)); // Non-existent ID
    }

    public function testFindByUsername()
    {
        $fixtureUser = $this->tester->grabFixture('users', 'user1');
        $foundUser = User::findByUsername($fixtureUser['username']);
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($fixtureUser['email'], $foundUser->email);

        $this->assertNull(User::findByUsername('nonexistentuser'));
    }

    public function testGetId()
    {
        $fixtureUser = $this->tester->grabFixture('users', 'user1');
        $model = User::findOne($fixtureUser['id']);
        $this->assertEquals($fixtureUser['id'], $model->getId());
    }

    public function testAuthKey()
    {
        $model = new User();
        $model->generateAuthKey();
        $this->assertNotEmpty($model->auth_key);
        $this->assertTrue($model->validateAuthKey($model->auth_key));
        $this->assertFalse($model->validateAuthKey('invalidAuthKey'));
    }

    // Test findIdentityByAccessToken (currently throws NotSupportedException)
    public function testFindIdentityByAccessToken()
    {
        $this->expectException(\yii\base\NotSupportedException::class);
        User::findIdentityByAccessToken('anytoken');
    }

    // Test getRoles and getRoleNames (basic test, assumes RBAC setup)
    // More thorough RBAC testing is better suited for functional/integration tests
    // or tests that specifically set up authManager assignments.
    public function testGetRoleNamesNoRoles()
    {
        // Create a new user that won't have roles assigned by default for this test
        $user = new User([
            'username' => 'norolesuser',
            'email' => 'noroles@example.com'
        ]);
        $user->setPassword('password123');
        $user->generateAuthKey();
        $this->assertTrue($user->save(), 'Failed to save user for role test: ' . print_r($user->errors, true));

        $this->assertEquals('No roles assigned', $user->getRoleNames());
        $user->delete(); // Clean up
    }

    public function testGetRoleNamesWithUserRole()
    {
        $user = new User([
            'username' => 'someroleuser',
            'email' => 'somerole@example.com'
        ]);
        $user->setPassword('password123');
        $user->generateAuthKey();
        $this->assertTrue($user->save());

        $auth = Yii::$app->authManager;
        $userRole = $auth->getRole('user'); // Assumes 'user' role exists from migrations
        if ($userRole) {
            $auth->assign($userRole, $user->id);
        } else {
            $this->markTestSkipped("Role 'user' not found, skipping role name test.");
        }

        // Refresh user model to get updated relations if needed, though getRoles() queries directly
        $userWithRole = User::findOne($user->id);
        $this->assertStringContainsString($userRole->description ?: 'user', $userWithRole->getRoleNames());

        $auth->revokeAll($user->id); // Clean up assignment
        $userWithRole->delete(); // Clean up user
    }
}
