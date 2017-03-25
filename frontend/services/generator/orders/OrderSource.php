<?php

namespace frontend\services\generator\orders;

use common\entity\generator\Order;

/**
 * Class Select
 * @package frontend\models
 */
class OrderSource
{
    /**
     * @var int
     */
    private $chanceForCart;

    /**
     * @var int
     */
    private $chanceForPhone;

    /**
     * @var int
     */
    private $orderBottomLimit;

    /**
     * @var int
     */
    private $orderTopLimit;

    /**
     * @param int $chanceForCart
     */
    public function setChanceForCart($chanceForCart)
    {
        $this->chanceForCart = $chanceForCart;
    }

    /**
     * @param int $chanceForPhone
     */
    public function setChanceForPhone($chanceForPhone)
    {
        $this->chanceForPhone = $chanceForPhone;
    }

    public function spread()
    {
        $this->resetIdOrderSource();

        $arrayСhanceForCart  = [];
        $arrayСhanceForPhone = [];

        $countOrders = Order::count();

        $countСhanceForCart  = floor($countOrders * ($this->chanceForCart  / 100));
        $countСhanceForPhone = floor($countOrders * ($this->chanceForPhone / 100));

        $totalCount = $countСhanceForCart
            + $countСhanceForPhone;

        /**
         * Добьем до полного соответствия количества
         */
        if ($totalCount < $countOrders) {

            while ($totalCount < $countOrders) {
                ++$countСhanceForPhone;

                $totalCount = $countСhanceForCart
                    + $countСhanceForPhone;
            }
        }

        $this->findLimitIdInOrder();

        $arrayLostOrdersId = Order::getArrayLostOrdersId();

        $countLostOrdersId = count($arrayLostOrdersId);

        foreach ($arrayLostOrdersId as $id) {

            $arrayСhanceForCart[$id] = 1;
        }

        for ($counter = 0; $counter < $countСhanceForPhone; ++$counter) {

            while (true) {
                $orderId = $this->getRandOrderId();

                if (
                    !isset($arrayСhanceForCart[$orderId])
                    && !isset($arrayСhanceForPhone[$orderId])
                ) {

                    $arrayСhanceForPhone[$orderId] = 1;
                    break;
                }
            }
        }

        for ($counter = 0; $counter < ($countСhanceForCart - $countLostOrdersId); ++$counter) {

            while (true) {
                $orderId = $this->getRandOrderId();

                if (
                    !isset($arrayСhanceForCart[$orderId])
                    && !isset($arrayСhanceForPhone[$orderId])
                ) {

                    $arrayСhanceForCart[$orderId] = 1;
                    break;
                }
            }
        }

        /**
         * Захардкодим id причин
         */
        $this->updateOrders($arrayСhanceForPhone, 1);
        $this->updateOrders($arrayСhanceForCart, 2);
    }

    public function ordersSourceCount()
    {
        $arrayOrdersSourceCount = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT `name`, COUNT(*) count
                    FROM `order`
                    JOIN order_source ON `order`.id_order_source = order_source.id
                    GROUP BY `name`
                '
            )
            ->queryAll();

        return $arrayOrdersSourceCount;
    }

    private function resetIdOrderSource()
    {
        Order::updateAll(['id_order_source' => null]);
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

    /**
     * @return int
     */
    private function getRandOrderId()
    {
        return mt_rand($this->orderBottomLimit, $this->orderTopLimit);
    }

    private function updateOrders($arrayWithIdOrders, $idSource)
    {
        foreach ($arrayWithIdOrders as $orderId => $value) {

            $order = Order::findOne($orderId);

            $order->id_order_source = $idSource;

            $order->save();
        }
    }
}