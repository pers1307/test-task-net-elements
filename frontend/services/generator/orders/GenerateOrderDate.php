<?php

namespace frontend\services\generator\orders;
use common\entity\generator\MakesOrdersDate;
use common\entity\generator\Order;
use common\entity\generator\SalesDate;


/**
 * Class Select
 * @package frontend\models
 */
class GenerateOrderDate
{
    /**
     * @var int
     */
    public $chanceMakeOrderBeforeOneDaySale;

    /**
     * @var int
     */
    public $chanceMakeOrderBeforeTwoDaySale;

    /**
     * from 3 to 12 days
     * @var int
     */
    public $chanceMakeOrderBeforeMoreDaySale;

    /**
     * @var int
     */
    public $orderBottomLimit;

    /**
     * @var int
     */
    public $orderTopLimit;

    /**
     * @param int $chance
     */
    public function setСhanceMakeOrderBeforeOneDaySale($chance)
    {
        $this->chanceMakeOrderBeforeOneDaySale = $chance;
    }

    /**
     * @param int $chance
     */
    public function setChanceMakeOrderBeforeTwoDaySale($chance)
    {
        $this->chanceMakeOrderBeforeTwoDaySale = $chance;
    }

    /**
     * @param int $chance
     */
    public function setChanceMakeOrderBeforeMoreDaySale($chance)
    {
        $this->chanceMakeOrderBeforeMoreDaySale = $chance;
    }

    public function generate()
    {
        $this->resetIdMakesOrderDateInOrder();
        $this->resetMakesOrdersDate();

        $arrayOrderBeforeOneDaySale  = [];
        $arrayOrderBeforeTwoDaySale  = [];
        $arrayOrderBeforeMoreDaySale = [];

        $countOrders = $this->countOrders();

        $countOrderBeforeOneDaySale  = floor($countOrders * ($this->chanceMakeOrderBeforeOneDaySale / 100));
        $countOrderBeforeTwoDaySale  = floor($countOrders * ($this->chanceMakeOrderBeforeTwoDaySale / 100));
        $countOrderBeforeMoreDaySale = floor($countOrders * ($this->chanceMakeOrderBeforeMoreDaySale / 100));

        $this->findLimitIdInOrder();

        for ($counter = 0; $counter < $countOrderBeforeOneDaySale; ++$counter) {

            while (true) {
                $orderId = $this->getRandOrderId();

                if (!isset($arrayOrderBeforeOneDaySale[$orderId])) {

                    $arrayOrderBeforeOneDaySale[$orderId] = 1;
                    break;
                }
            }
        }

        for ($counter = 0; $counter < $countOrderBeforeTwoDaySale; ++$counter) {

            while (true) {
                $orderId = $this->getRandOrderId();

                if (
                    !isset($arrayOrderBeforeOneDaySale[$orderId])
                    && !isset($arrayOrderBeforeTwoDaySale[$orderId])
                ) {
                    $arrayOrderBeforeTwoDaySale[$orderId] = 1;
                    break;
                }
            }
        }

        for ($counter = 0; $counter < $countOrderBeforeMoreDaySale; ++$counter) {

            while (true) {
                $orderId = $this->getRandOrderId();

                if (
                    !isset($arrayOrderBeforeOneDaySale[$orderId])
                    && !isset($arrayOrderBeforeTwoDaySale[$orderId])
                    && !isset($arrayOrderBeforeMoreDaySale[$orderId])
                ) {
                    $arrayOrderBeforeMoreDaySale[$orderId] = 1;
                    break;
                }
            }
        }

        $interval = new \DateInterval('P1D');

        $this->addDatesInDataBase($arrayOrderBeforeOneDaySale, $interval);

        $interval = new \DateInterval('P2D');

        $this->addDatesInDataBase($arrayOrderBeforeTwoDaySale, $interval);

        $this->addDatesInDataBaseWithRandDate($arrayOrderBeforeMoreDaySale);

        $ordersWithoutIdMakesOrderDate = Order::find()
            ->select(['id'])
            ->where('id_makes_order_date IS NULL AND `lost` <> 1')
            ->asArray()
            ->all();

        if (!empty($ordersWithoutIdMakesOrderDate)) {

            $arrayOrderWithoutIdMakesOrderDate = [];

            foreach ($ordersWithoutIdMakesOrderDate as $orderWithoutIdMakesOrderDate) {

                $arrayOrderWithoutIdMakesOrderDate[$orderWithoutIdMakesOrderDate['id']] = 1;
            }

            $this->addDatesInDataBaseWithRandDate($arrayOrderWithoutIdMakesOrderDate);
        }
    }

    public function generateLost()
    {
        $countOrders = $this->countLostOrders();
        $makeOrderDays = MakesOrdersDate::find()->count();

        $countLostOrdersInOneDay = floor($countOrders / $makeOrderDays);

        $makesOrdersDates = MakesOrdersDate::find()
            ->orderBy('date ASC')
            ->all();

        /** @var MakesOrdersDate $makesOrdersDate */
        foreach ($makesOrdersDates as $makesOrdersDate) {

            $lostOrders = Order::find()
                ->join('JOIN', 'sales_date', '`order`.id_sales_date = sales_date.id')
                //->where("lost = 1 AND id_makes_order_date IS NULL AND DATE(sales_date.date) > DATE('" . $makesOrdersDate->date . "')")
                ->where("lost = 1 AND DATE(sales_date.date) > DATE('" . $makesOrdersDate->date . "')")
                ->all();

            $countLostOrder = count($lostOrders);

            if ($countLostOrder == 0) {

                break;
            }

            $arrayRandIds = [];

            for ($index = 0; $index < $countLostOrdersInOneDay; ++$index) {

                $randId = mt_rand(0, $countLostOrder - 1);

                $arrayRandIds[] = $randId;
            }

            foreach ($arrayRandIds as $id) {

                /** @var Order $lostOrders */
                $lostOrders[$id]->id_makes_order_date = $makesOrdersDate->id;

                $lostOrders[$id]->save();
            }
        }

        $otherLostOrders = Order::find()
            ->where('lost = 1 AND id_makes_order_date IS NULL')
            ->all();

        /** @var Order $otherLostOrder */
        foreach ($otherLostOrders as $otherLostOrder) {

            /** @var SalesDate $saleDate */
            $saleDate = SalesDate::findOne(['id' => $otherLostOrder->id_sales_date]);

            /**
             * Выбрать дни совершения заказа меньше него
             */
            $makesOrderDatesLessSaleDate = MakesOrdersDate::find()
                ->where('DATE(date) < DATE("' . $saleDate->date . '") AND MONTH(date) = MONTH("' . $saleDate->date . '")')
                ->all();

            $count = count($makesOrderDatesLessSaleDate);

            /**
             * В случае, если нет таких дней, то попробуем выбрать снова
             */
            if ($count == 0) {

                $makesOrderDatesLessSaleDate = MakesOrdersDate::find()
                    ->where('DATE(date) < DATE("' . $saleDate->date . '")')
                    ->all();

                $count = count($makesOrderDatesLessSaleDate);
            }

            $randIdDay = mt_rand(0, $count - 1);

            /**
             * Выбрать случайный
             */
            $otherLostOrder->id_makes_order_date = $makesOrderDatesLessSaleDate[$randIdDay]->id;

            $otherLostOrder->save();
        }
    }

    /**
     * @return int
     */
    public function getCountOrders()
    {
        return Order::find()->count();
    }

    /**
     * @return int
     */
    public function getCountOrdersWithIdMakesOrdersDate()
    {
        return Order::find()->where('id_makes_order_date IS NOT NULL')->count();
    }

    /**
     * @return array
     */
    public function ordersPerDay()
    {
        $arrayOrdersInDays = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT days, COUNT(*) as orders
                    FROM
                    (
                        SELECT TO_DAYS(sales_date.date) - TO_DAYS(makes_orders_date.date) days
                        FROM `order`
                        JOIN sales_date ON `order`.id_sales_date = sales_date.id
                        JOIN makes_orders_date ON `order`.id_makes_order_date = makes_orders_date.id
                    ) AS days
                    GROUP BY days
                '
            )
            ->queryAll();

        return $arrayOrdersInDays;
    }

    /**
     * @return int
     */
    private function countOrders()
    {
        return Order::find()
            ->where('`lost` <> 1')
            ->count();
    }

    /**
     * @return int
     */
    private function countLostOrders()
    {
        return Order::find()
            ->where('`lost` = 1')
            ->count();
    }

    private function findLimitIdInOrder()
    {
        $result = Order::find()
            ->select('MIN(id) as min')
            ->where('`lost` <> 1')
            ->asArray()
            ->one();

        $this->orderBottomLimit = $result['min'];

        $result = Order::find()
            ->select('MAX(id) as max')
            ->where('`lost` <> 1')
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

    /**
     * @return int
     */
    private function getRandCountDays()
    {
        return mt_rand(3, 12);
    }

    private function resetMakesOrdersDate()
    {
        MakesOrdersDate::deleteAll();
    }

    private function resetIdMakesOrderDateInOrder()
    {
        Order::updateAll(['id_makes_order_date' => null]);
    }

    /**
     * @param array $arrayOrders
     * @param \DateInterval $interval
     */
    private function addDatesInDataBase($arrayOrders, $interval)
    {
        foreach ($arrayOrders as $key => $value) {

            $order = Order::find()
                ->select(['*'])
                ->join('JOIN', 'sales_date', '`order`.id_sales_date = sales_date.id')
                ->where(['`order`.id' => $key])
                ->asArray()
                ->one();

            $salesDate     = new \DateTime($order['date']);
            $makeOrderDate = clone $salesDate;

            $makeOrderDate->sub($interval);

            /**
             * Проверяем день. Если он выходной,
             * то надо сделать перевыбор дня
             */
            while (true) {

                $dayType = $makeOrderDate->format('N');

                if ($dayType == 6 || $dayType == 7) {

                    /**
                     * Проверить, если в этот день более 2х заказов, то перевыбрать,
                     * иначе нет
                     */
                    $format = $makeOrderDate->format('Y-m-d H:i:s');

                    /** @var MakesOrdersDate $makeOrderDateModel */
                    $makeOrderDateModel = MakesOrdersDate::find()->where(['date' => $format])->one();

                    if (!is_null($makeOrderDateModel)) {

                        $count = Order::find()->where('id_makes_order_date = ' . $makeOrderDateModel->id)->count();

                        if ($count <= 2) {

                            break;
                        }
                    } else {

                        break;
                    }

                    $makeOrderDate = clone $salesDate;

                    $randDays = $this->getRandCountDays();

                    $randInterval = new \DateInterval('P' . $randDays . 'D');

                    $makeOrderDate->sub($randInterval);
                } else {

                    break;
                }
            }

            $format = $makeOrderDate->format('Y-m-d H:i:s');

            $makeOrderDateModel = MakesOrdersDate::find()->where(['date' => $format])->one();

            if (is_null($makeOrderDateModel)) {

                $makeOrderDateModel = new MakesOrdersDate();

                $makeOrderDateModel->date = $format;

                $makeOrderDateModel->save();

                $orderForUpdate = Order::findOne($key);

                $orderForUpdate->id_makes_order_date = $makeOrderDateModel->id;

                $orderForUpdate->save();
            } else {

                $orderForUpdate = Order::findOne($key);

                $orderForUpdate->id_makes_order_date = $makeOrderDateModel->id;

                $orderForUpdate->save();
            }
        }
    }

    /**
     * @param array $arrayOrders
     */
    private function addDatesInDataBaseWithRandDate($arrayOrders)
    {
        foreach ($arrayOrders as $key => $value) {

            $order = Order::find()
                ->select(['*'])
                ->join('JOIN', 'sales_date', '`order`.id_sales_date = sales_date.id')
                ->where(['`order`.id' => $key])
                ->asArray()
                ->one();

            $salesDate     = new \DateTime($order['date']);
            $makeOrderDate = clone $salesDate;

            $randDays = $this->getRandCountDays();

            $interval = new \DateInterval('P' . $randDays . 'D');

            $makeOrderDate->sub($interval);

            /**
             * Проверяем день. Если он выходной,
             * то надо сделать перевыбор дня
             */
            while (true) {

                $dayType = $makeOrderDate->format('N');

                if ($dayType == 6 || $dayType == 7) {

                    /**
                     * Проверить, если в этот день более 2х заказов, то перевыбрать,
                     * иначе нет
                     */
                    $format = $makeOrderDate->format('Y-m-d H:i:s');

                    /** @var MakesOrdersDate $makeOrderDateModel */
                    $makeOrderDateModel = MakesOrdersDate::find()->where(['date' => $format])->one();

                    if (!is_null($makeOrderDateModel)) {

                        $count = Order::find()->where('id_makes_order_date = ' . $makeOrderDateModel->id)->count();

                        if ($count <= 2) {

                            break;
                        }
                    } else {

                        break;
                    }

                    $makeOrderDate = clone $salesDate;

                    $randDays = $this->getRandCountDays();

                    $randInterval = new \DateInterval('P' . $randDays . 'D');

                    $makeOrderDate->sub($randInterval);
                } else {

                    break;
                }
            }

            $format = $makeOrderDate->format('Y-m-d H:i:s');

            $makeOrderDateModel = MakesOrdersDate::find()->where(['date' => $format])->one();

            if (is_null($makeOrderDateModel)) {

                $makeOrderDateModel = new MakesOrdersDate();

                $makeOrderDateModel->date = $format;

                $makeOrderDateModel->save();

                $orderForUpdate = Order::findOne($key);

                $orderForUpdate->id_makes_order_date = $makeOrderDateModel->id;

                $orderForUpdate->save();
            } else {

                $orderForUpdate = Order::findOne($key);

                $orderForUpdate->id_makes_order_date = $makeOrderDateModel->id;

                $orderForUpdate->save();
            }
        }
    }
}