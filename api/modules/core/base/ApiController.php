<?php
/**
 * @author bmxnt <bmxnt@mail.ru>
 */

namespace api\modules\core\base;

use Yii;
use yii\base\Controller;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class ApiController extends Controller {
    public $defaultAction = '';
    protected $outputFormat;
    protected $outputResult;
    protected $errorMessages;

    public function init() {
        parent::init();

        $this->outputResult = array(
            'status' => array(
                'code' => 200,
                'message' => 'OK'
            ),
            'data' => array(),
        );

        if (empty($this->outputFormat)) {
            $this->outputFormat = ArrayHelper::getValue(Yii::$app->params, 'apiConfig.outputFormat', 'json');
        }

        if (empty($this->errorMessages)) {
            $this->errorMessages = ArrayHelper::getValue(Yii::$app->params, 'apiConfig.errorMessages', []);
        } else {
            $this->errorMessages = ArrayHelper::merge(ArrayHelper::getValue(Yii::$app->params, 'apiConfig.errorMessages', []), $this->errorMessages);
        }
    }

    /**
     * @param $code
     * @param null $message
     * @param array $data
     * @return bool
     */
    public function errorAction($code, $message = NULL, $data = array()) {
        $this->outputResult['status']['code'] = $code;
        if (!is_null($message)) {
            $this->outputResult['status']['message'] = $message;
        } else {
            $this->outputResult['status']['message'] = ArrayHelper::getValue($this->errorMessages, $code, 'Unknown error');
        }
        if ($data) $this->addData($data);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function successAction($data = array()) {
        $this->outputResult['status']['code'] = 200;
        $this->outputResult['status']['message'] = $this->errorMessages[200];
        if ($data) $this->addData($data);
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action) {

        try {
            $parent = parent::beforeAction($action);
        } catch(\Exception $e) {
            $this->errorAction(403);
            Yii::$app->response->setStatusCode(403);
            Yii::$app->response->data = $this->afterAction($action, []);
            Yii::$app->response->send();
            return false;
        }

        if (!$parent) return false;
        return true;
    }

    /**
     * @param \yii\base\Action $action
     * @param mixed $result
     * @return mixed
     */
    public function afterAction($action, $result) {
        switch ($this->outputFormat) {
            case 'html':
                return '<pre>' . print_r($this->outputResult, true) . '</pre>';

            case 'xml':
                Yii::$app->response->format = Response::FORMAT_XML;
                return $this->outputResult;

            case 'json':
            default:
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $this->outputResult;
        }
    }

    /**
     * @return mixed
     */
    public function getData() {
        return $this->outputResult['data'];
    }

    /**
     * @param $data
     * @return $this
     */
    public function setData($data) {
        if (is_array($data)) {
            $this->outputResult['data'] = $data;
        }
        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function addData($data) {
        if (is_array($data)) {
            foreach ($data as $_key => $_value) {
                $this->outputResult['data'][$_key] = $_value;
            }
        }
        return $this;
    }
}