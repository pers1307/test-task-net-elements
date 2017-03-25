<?php

namespace api\controllers;

use api\modules\core\base\ApiController;
use common\repository\storage\OrderStorageRepository;
use Yii;

class PriceController extends ApiController
{
    const ERROR_INCORRECT_FIELDS = 1000;

    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    /**
     * /api/price.get-purchase-by-day
     *
     * POST
     * @param int    projectId
     * @param string day (example: '2017-12-30')
     *
     * @return response.data.success || response.data.total
     */
    public function actionGetPurchaseByDay()
    {
        $projectId = intval(Yii::$app->request->post('projectId'));
        $day       = htmlspecialchars(Yii::$app->request->post('day'));

        if (empty($projectId) || empty($day)) {

            $this->errorAction(1001, 'Custom system error', ['error' => 'noPostArgument']);
        } else {

            $orderStorageRepository = new OrderStorageRepository();

            $this->addData([
                'success'            => true,
                'projectId'          => $projectId,
                'day'                => $day,
                'purchasePriceInDay' => $orderStorageRepository->getPurchasePriceInDay($projectId, $day)
            ]);
        }
    }
}