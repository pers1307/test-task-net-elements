<?php

namespace frontend\services\generator\orders;

use common\entity\generator\Order;
use common\entity\generator\SalesDate;

/**
 * Class GenerateOrderSalesDay
 * @package frontend\models
 */
class GenerateOrderSalesDay
{
    /**
     * @var int
     */
    public $minCountSalesOnDay;

    /**
     * @var int
     */
    public $maxCountSalesOnDay;

    /**
     * @var int
     */
    public $salesDateTopLimit;

    /**
     * @var int
     */
    public $salesDateBottomLimit;

    /**
     * @var int
     */
    public $orderTopLimit;

    /**
     * @var int
     */
    public $orderBottomLimit;

    /**
     * @param int $minCountSalesOnDay
     */
    public function setMinCountSalesOnDay($minCountSalesOnDay)
    {
        $this->minCountSalesOnDay = $minCountSalesOnDay;
    }

    /**
     * @param int $maxCountSalesOnDay
     */
    public function setMaxCountSalesOnDay($maxCountSalesOnDay)
    {
        $this->maxCountSalesOnDay = $maxCountSalesOnDay;
    }

    /**
     * @return string
     */
    public function checkArgument()
    {
        if ($this->minCountSalesOnDay > $this->maxCountSalesOnDay) {

            return 'Ошибка входных параметров.
                Минимальное количество продаж в день должно быть меньше максимального.
            ';
        }

        $countDays   = SalesDate::find()->count();
        $countOrders = Order::find()->count();

        if (($this->maxCountSalesOnDay * $countDays) < $countOrders) {

            return 'Ошибка входных параметров. При количестве дней продаж ' . $countDays
            . ' и общем количестве заказов ' . $countOrders
            . ' максимальное количество продаж в день должно быть больше ' . ceil($countOrders / $countDays);
        }

        if (($this->minCountSalesOnDay * $countDays) > $countOrders) {

            return 'Ошибка входных параметров. При количестве дней продаж ' . $countDays
            . ' и общем количестве заказов ' . $countOrders
            . ' минимальное количество продаж в день должно быть меньше ' . ceil($countOrders / $countDays);
        }
    }

    public function process()
    {
        $this->resetSalesDaysInOrders();

        $this->findLimitIdInSalesDate();
        $this->findLimitIdInOrder();

        $orderCursor     = $this->orderBottomLimit;
        $salesDateCursor = $this->salesDateBottomLimit;

        while ($salesDateCursor <= $this->salesDateTopLimit) {

            $countMinOrderInDay = 0;

            while ($countMinOrderInDay < $this->minCountSalesOnDay) {

                $order = Order::findOne($orderCursor);

                if (!is_null($order)) {

                    $order->id_sales_date = $salesDateCursor;

                    $order->save();

                    ++$countMinOrderInDay;
                }

                ++$orderCursor;
            }

            ++$salesDateCursor;
        }

        if ($orderCursor <= $this->orderTopLimit) {

            while ($orderCursor <= $this->orderTopLimit) {

                $randSalesDay = 0;

                while (true) {

                    $randSalesDay = $this->getRandSalesDayId();

                    $countOrdersInThatDay = Order::find()
                        ->where(['id_sales_date' => $randSalesDay])
                        ->count();

                    if ($countOrdersInThatDay < $this->maxCountSalesOnDay) {

                        break;
                    }
                }

                $order = Order::findOne($orderCursor);

                if (!is_null($order)) {

                    $order->id_sales_date = $randSalesDay;

                    $order->save();
                }

                ++$orderCursor;
            }
        }
    }

    public function countDays()
    {
        return SalesDate::find()
            ->count();
    }

    /**
     * @return int
     */
    public function minFactCountOrdersInDay()
    {
        $minCountOrdersInDay = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT MIN(ordersPerDate.count) AS min
                    FROM
                    (
                        SELECT id_sales_date, COUNT(*) AS count
                        FROM `order`
                        GROUP BY id_sales_date
                    ) AS ordersPerDate
                '
            )
            ->queryOne();

        return $minCountOrdersInDay['min'];
    }

    /**
     * @return int
     */
    public function maxFactCountOrdersInDay()
    {
        $maxCountOrdersInDay = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT MAX(ordersPerDate.count) AS max
                    FROM
                    (
                        SELECT id_sales_date, COUNT(*) AS count
                        FROM `order`
                        GROUP BY id_sales_date
                    ) AS ordersPerDate
                '
            )
            ->queryOne();

        return $maxCountOrdersInDay['max'];
    }

    /**
     * @return int
     */
    public function getAvgFactCountOrdersInDay()
    {
        $avgCountOrdersInDay = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT AVG(ordersPerDate.count) AS avg
                    FROM
                    (
                        SELECT id_sales_date, COUNT(*) AS count
                        FROM `order`
                        GROUP BY id_sales_date
                    ) AS ordersPerDate
                '
            )
            ->queryOne();

        return floor($avgCountOrdersInDay['avg']);
    }

    private function findLimitIdInSalesDate()
    {
        $result = SalesDate::find()
            ->select('MIN(id) as min')
            ->asArray()
            ->one();

        $this->salesDateBottomLimit = $result['min'];

        $result = SalesDate::find()
            ->select('MAX(id) as max')
            ->asArray()
            ->one();

        $this->salesDateTopLimit = $result['max'];
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
    private function getRandSalesDayId()
    {
        return mt_rand($this->salesDateBottomLimit, $this->salesDateTopLimit);
    }

    private function resetSalesDaysInOrders()
    {
        Order::updateAll(['id_sales_date' => null]);
    }
}