<?php
/**
 * @author bmxnt <bmxnt@mail.ru>
 */

namespace api\controllers;

use api\modules\core\base\ApiController;
use common\forms\auth\Login as LoginForm;

use common\helpers\ArrayHelper;
use Yii;

class AuthController extends ApiController {
    const ERROR_INCORRECT_FIELDS = 1000;
    const ERROR_INCORRECT_USER_ID = 1001;
    protected $errorMessages = [
        1000 => 'Некорректно заполненные поля',
        1001 => 'Некорректный идентификатор пользователя',
    ];

    //-----------------------------------------------------
    // Login
    //-----------------------------------------------------

    public function actionLogin() {
        $loginData = ArrayHelper::mapRecursive('htmlspecialchars', Yii::$app->request->post());
        $loginForm = new LoginForm();
        $loginForm->load($loginData, '');

        if ($loginForm->validate()) {
            if ($loginForm->login()) {
                $responseData = [
                    'action' => 'login-success',
                ];
                $responseData['redirect'] = '/';
                $responseData['keep'] = true;
                $this->addData($responseData);
            }
        } else {
            $errors = $loginForm->errors;
            foreach ($errors as $name => $error) {
                if (is_array($error)) {
                    $errors[$name] = reset($error);
                }
            }
            return $this->errorAction(static::ERROR_INCORRECT_FIELDS, null, ['errors' => $errors]);
        }
    }

    //-----------------------------------------------------
    // Forgot
    //-----------------------------------------------------

    public function actionForgot() {
        $forgotData = ArrayHelper::mapRecursive('htmlspecialchars', Yii::$app->request->post());
        $forgotForm = new ForgotForm();
        $forgotForm->load($forgotData, '');

        if ($forgotForm->validate()) {
            if ($forgotForm->sendEmail()) {
                $user = $forgotForm->getUser();
                $responseData = [
                    'action' => 'forgot-success',
                    'email' => $user->email
                ];
                $responseData['popup'] = $this->renderFile('@frontend/views/popup/auth/forgot-success.php', $responseData);
                $this->addData($responseData);
            }
        } else {
            $errors = $forgotForm->errors;
            foreach ($errors as $name => $error) {
                if (is_array($error)) {
                    $errors[$name] = reset($error);
                }
            }
            return $this->errorAction(static::ERROR_INCORRECT_FIELDS, null, ['errors' => $errors]);
        }
    }

    public function actionResetPassword() {
        $tokenErrorMessage = false;
        $resetPasswordData = ArrayHelper::mapRecursive('htmlspecialchars', Yii::$app->request->post());
        $resetPasswordToken = (!empty($resetPasswordData['token'])) ? $resetPasswordData['token'] : '';
        
        try {
            $resetPasswordForm = new ResetPasswordForm($resetPasswordToken);
        } catch (InvalidParamException $e) {
            $tokenErrorMessage = $e->getMessage();
        }
        
        if(empty($tokenErrorMessage)) {
            $resetPasswordForm->load($resetPasswordData, '');
            if ($resetPasswordForm->validate()) {
                if ($user = $resetPasswordForm->resetPassword()) {
                    Yii::$app->getUser()->login($user, 3600 * 24 * 30);
                    $responseData = [
                        'action' => 'reset-password-success'
                    ];
                    $responseData['redirect'] = '/';
                    $responseData['keep'] = true;
                    $this->addData($responseData);
                }
            } else {
                $errors = $resetPasswordForm->errors;
                foreach ($errors as $name => $error) {
                    if (is_array($error)) {
                        $errors[$name] = reset($error);
                    }
                }
                return $this->errorAction(static::ERROR_INCORRECT_FIELDS, null, ['errors' => $errors]);
            }
        } else {
            return $this->errorAction(static::ERROR_INCORRECT_FIELDS, null, ['token' => $tokenErrorMessage]);
        }
    }
}