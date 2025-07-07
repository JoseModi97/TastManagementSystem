<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            // After password validation, check if the user has roles
            ['password', 'validateUserHasRoles', 'skipOnError' => true], // skipOnError ensures this runs only if validatePassword passed for 'password'
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     * It also implicitly authenticates the user by populating $this->_user.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

    /**
     * Validates if the authenticated user has any roles assigned.
     * This method is called after validatePassword() is successful.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateUserHasRoles($attribute, $params)
    {
        // This validator should only run if there are no previous errors AND a user is authenticated.
        // The 'skipOnError' => true in rules() handles the "no previous errors for this attribute" part.
        // We still need to ensure $this->getUser() returns a valid user.
        if (!$this->hasErrors()) { // General check, though skipOnError on the rule is more specific
            $user = $this->getUser();

            // If $user is null here, it means validatePassword failed to find/auth user,
            // or this validator was somehow called out of order.
            // validatePassword already adds an error if $user is null or password mismatch.
            if ($user) {
                $assignments = Yii::$app->authManager->getAssignments($user->getId());
                if (empty($assignments)) {
                    $this->addError($attribute, 'You have not been assigned a role. Please contact an administrator.');
                }
            }
        }
    }
}
