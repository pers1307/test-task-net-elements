<?php

namespace frontend\services\generator\orders;
use common\entity\generator\Client;
use common\entity\generator\Order;

/**
 * Class Select
 * Модель выборки из базы по параметрам
 * @package frontend\models
 */
class SpreadClientsOnOrders
{
    /**
     * @var int
     */
    private $orderTopLimit;

    /**
     * @var int
     */
    private $orderBottomLimit;

    public function spreadOrdersForClients()
    {
        $this->resetIdClientsInOrders();

        $this->findLimitIdInOrder();

        $orderCursor = $this->orderBottomLimit;

        $clients = $this->getAllClientsAsArray();

        $clientsCursor = 0;
        $countClients  = count($clients);

        while ($orderCursor <= $this->orderTopLimit) {

            $order = Order::findOne($orderCursor);

            $order->id_client = $clients[$clientsCursor];

            $order->save();

            ++$orderCursor;

            if ($clientsCursor < $countClients - 1) {

                ++$clientsCursor;
            } else {

                $clientsCursor = 0;
            }
        }
    }

    /**
     * @return float
     */
    public function countCycleWriteClientsInOrders()
    {
        return Order::count() / Client::find()->count();
    }

    /**
     * @return mixed
     */
    public function maxCountOneClientInOrders()
    {
        $maxCountOneClient = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT MAX(count_client.count) as count
                    FROM
                    (
                        SELECT id_client, COUNT(*) count
                        FROM `order`
                        GROUP BY id_client
                    ) as count_client
                '
            )
            ->queryOne();

        return $maxCountOneClient['count'];
    }

    private function findLimitIdInOrder()
    {
        $result = Order::find()
            ->select('MIN(id) as min')
            ->asArray()
            ->one();

        $this->orderBottomLimit = $result['min'];

        $result = Order::find()
            ->select('MAX(id) as max')
            ->asArray()
            ->one();

        $this->orderTopLimit = $result['max'];
    }

    private function resetIdClientsInOrders()
    {
        Order::updateAll(['id_client' => null]);
    }

    private function getAllClientsAsArray()
    {
        $clients = Client::find()
            ->select('id')
            ->asArray()
            ->all();

        $formatArray = [];

        foreach ($clients as $client) {
            $formatArray[] = $client['id'];
        }

        return $formatArray;
    }
}