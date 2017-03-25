<?php
/**
 * StatisticsController.php
 *
 * Api контроллер для получения статистики
 *
 * @author      Pereskokov Yurii
 * @copyright   2017 Pereskokov Yurii
 * @link        http://www.mediasite.ru/
 */

namespace api\controllers;

use api\modules\core\base\ApiController;
use common\exception\NoGetArgumentException;
use common\services\Statistics;
use Yii;

class StatisticsController extends ApiController
{
    const ERROR_INCORRECT_FIELDS = 1000;

    /**
     * @param \yii\base\Action $action
     *
     * @return bool
     */
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    /**
     * /api/statistics.get-by-plates
     *
     * Метод для получения продуктов и их характеристик продаж
     * по переданному диапазону дат совершения заказа
     *
     * GET
     * @param string **from (example: '2016-09-01 00:00:00')
     * @param string to**   (example: '2016-09-15 22:58:36')
     *
     * @return response.data.success || response.data.total
     */
    public function actionGetByPlates()
    {
        try {
            $fromDateTime = htmlspecialchars(Yii::$app->request->get('**from'));
            $toDateTime   = htmlspecialchars(Yii::$app->request->get('to**'));

            if (empty($fromDateTime) || empty($toDateTime)) {

                if (empty($fromDateTime)) {

                    throw new NoGetArgumentException('Не передано время (параметр **from)');
                }

                if (empty($toDateTime)) {

                    throw new NoGetArgumentException('Не передано время (параметр to**)');
                }
            }

            $statisticsService = new Statistics();

            $platesAndTotal = $statisticsService->getSalesPlatesByOrderDateTime($fromDateTime, $toDateTime);

            $this->addData([
                'plates' => $platesAndTotal['itemsPlate'],
                'total'  => $platesAndTotal['totalRow']
            ]);
        } catch (NoGetArgumentException $noGetArgumentException) {

            $this->errorAction(1001, 'Custom system error', ['error' => $noGetArgumentException->getMessage()]);
        } catch (\Exception $exception) {

            $this->errorAction(1001, 'Custom system error', ['error' => 'Что - то пошло не так']);
        }
    }
}