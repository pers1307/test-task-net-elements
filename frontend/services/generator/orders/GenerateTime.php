<?php

namespace frontend\services\generator\orders;

use common\entity\generator\Order;

/**
 * Class Select
 * @package frontend\models
 */
class GenerateTime
{
    /**
     * 10 - 14
     *
     * @var int
     */
    public $chanceFirstTimeInterval;

    /**
     * 14 - 18
     *
     * @var int
     */
    public $chanceSecondTimeInterval;

    /**
     * 18 - 22
     *
     * @var int
     */
    public $chanceThirdTimeInterval;

    /**
     * @var int
     */
    public $orderBottomLimit;

    /**
     * @var int
     */
    public $orderTopLimit;

    /**
     * @var TimeBetweenOrders
     */
    private $firstTimeIntervalService;

    /**
     * @var TimeBetweenOrders
     */
    private $secondTimeIntervalService;

    /**
     * @var TimeBetweenOrders
     */
    private $thirdTimeIntervalService;

    /**
     * @param int $chance
     */
    public function setChanceFirstTimeInterval($chance)
    {
        $this->chanceFirstTimeInterval = $chance;
    }

    /**
     * @param int $chance
     */
    public function setChanceSecondTimeInterval($chance)
    {
        $this->chanceSecondTimeInterval = $chance;
    }

    /**
     * @param int $chance
     */
    public function setChanceThirdTimeInterval($chance)
    {
        $this->chanceThirdTimeInterval = $chance;
    }

    public function process()
    {
        $this->resetDateTimeSales();

        $arrayChanceFirstTimeInterval  = [];
        $arrayChanceSecondTimeInterval = [];
        $arrayChanceThirdTimeInterval  = [];

        $countOrders = Order::count();

        $countOrderForFirstTimeInterval  = floor($countOrders * ($this->chanceFirstTimeInterval  / 100));
        $countOrderForSecondTimeInterval = floor($countOrders * ($this->chanceSecondTimeInterval / 100));
        $countOrderForThirdTimeInterval  = floor($countOrders * ($this->chanceThirdTimeInterval  / 100));

        $totalCount = $countOrderForFirstTimeInterval
            + $countOrderForSecondTimeInterval
            + $countOrderForThirdTimeInterval;

        /**
         * Добьем до полного соответствия количества
         */
        if ($totalCount < $countOrders) {

            while ($totalCount < $countOrders) {

                ++$countOrderForThirdTimeInterval;

                $totalCount = $countOrderForFirstTimeInterval
                    + $countOrderForSecondTimeInterval
                    + $countOrderForThirdTimeInterval;
            }
        }

        $this->findLimitIdInOrder();

        for ($counter = 0; $counter < $countOrderForFirstTimeInterval; ++$counter) {

            while (true) {
                $orderId = $this->getRandOrderId();

                if (!isset($arrayChanceFirstTimeInterval[$orderId])) {
                    $arrayChanceFirstTimeInterval[$orderId] = 1;
                    break;
                }
            }
        }

        for ($counter = 0; $counter < $countOrderForSecondTimeInterval; ++$counter) {

            while (true) {
                $orderId = $this->getRandOrderId();

                if (
                    !isset($arrayChanceFirstTimeInterval[$orderId])
                    && !isset($arrayChanceSecondTimeInterval[$orderId])
                ) {
                    $arrayChanceSecondTimeInterval[$orderId] = 1;
                    break;
                }
            }
        }

        for ($counter = 0; $counter < $countOrderForThirdTimeInterval; ++$counter) {

            while (true) {
                $orderId = $this->getRandOrderId();

                if (
                    !isset($arrayChanceFirstTimeInterval[$orderId])
                    && !isset($arrayChanceSecondTimeInterval[$orderId])
                    && !isset($arrayChanceThirdTimeInterval[$orderId])
                ) {
                    $arrayChanceThirdTimeInterval[$orderId] = 1;
                    break;
                }
            }
        }

        $this->addSalesDataTime($arrayChanceFirstTimeInterval,  'first');
        $this->addSalesDataTime($arrayChanceSecondTimeInterval, 'second');
        $this->addSalesDataTime($arrayChanceThirdTimeInterval,  'third');

        $this->finalCheckTime();
    }

    public function getCountOrdersWithFirstTimeInterval()
    {
        return Order::find()->where('TIME_TO_SEC(date_time_sales) BETWEEN 36000 AND 50400')->count();
    }

    public function getCountOrdersWithSecondTimeInterval()
    {
        return Order::find()->where('TIME_TO_SEC(date_time_sales) BETWEEN 50400 AND 64800')->count();
    }

    public function getCountOrdersWithThirdTimeInterval()
    {
        return Order::find()->where('TIME_TO_SEC(date_time_sales) BETWEEN 64800 AND 79200')->count();
    }

    public function getCountOrdersWithFirstTimeIntervalPerDate()
    {
        $arrayDateCount = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT FROM_DAYS(days) date, COUNT(*) count
                    FROM
                    (
                        SELECT TO_DAYS(date_time_sales) days
                        FROM `order`
                        WHERE TIME_TO_SEC(date_time_sales) BETWEEN 36000 AND 50400
                    ) as days
                    GROUP BY days
                '
            )
            ->queryAll();

        return $arrayDateCount;
    }

    public function getCountOrdersWithSecondTimeIntervalPerDate()
    {
        $arrayDateCount = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT FROM_DAYS(days) date, COUNT(*) count
                    FROM
                    (
                        SELECT TO_DAYS(date_time_sales) days
                        FROM `order`
                        WHERE TIME_TO_SEC(date_time_sales) BETWEEN 50400 AND 64800
                    ) as days
                    GROUP BY days
                '
            )
            ->queryAll();

        return $arrayDateCount;
    }

    public function getCountOrdersWithThirdTimeIntervalPerDate()
    {
        $arrayDateCount = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT FROM_DAYS(days) date, COUNT(*) count
                    FROM
                    (
                        SELECT TO_DAYS(date_time_sales) days
                        FROM `order`
                        WHERE TIME_TO_SEC(date_time_sales) BETWEEN 64800 AND 79200
                    ) as days
                    GROUP BY days
                '
            )
            ->queryAll();

        return $arrayDateCount;
    }

    private function resetDateTimeSales()
    {
        Order::updateAll(['date_time_sales' => null]);
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

    /**
     * @param array  $arrayWithOrdersId
     * @param string $typeTimeInterval
     */
    private function addSalesDataTime($arrayWithOrdersId, $typeTimeInterval)
    {
        $ids = $this->arrayKeyInString($arrayWithOrdersId);

        $orders = Order::find()
            ->select(
                [
                    '`order`.id',
                    'sales_date.date'
                ]
            )
            ->join('JOIN', 'sales_date', '`order`.id_sales_date = sales_date.id')
            ->where('`order`.id IN (' . $ids . ')')
            ->orderBy('sales_date.date ASC')
            ->asArray()
            ->all();

        foreach ($orders as $order) {

            $salesDate = new \DateTime($order['date']);

            if ($typeTimeInterval == 'first') {

                $randSeconds = $this->getRandomSecondForFirstInterval();
            } elseif ($typeTimeInterval == 'second') {

                $randSeconds = $this->getRandomSecondForSecondInterval();
            } elseif ($typeTimeInterval == 'third') {

                $randSeconds = $this->getRandomSecondForThirdInterval();
            }

            $interval = new \DateInterval('PT' . $randSeconds . 'S');

            $salesDate->add($interval);

            $format = $salesDate->format('Y-m-d H:i:s');

            $orderForUpdate = Order::findOne((int)$order['id']);

            $orderForUpdate->date_time_sales = $format;

            $orderForUpdate->save();
        }
    }

    /**
     * @param array $arrayWithOrdersId
     *
     * @return string
     */
    private function arrayKeyInString($arrayWithOrdersId)
    {
        $result = '';

        foreach ($arrayWithOrdersId as $orderId => $value) {

            if (empty($result)) {

                $result = $orderId;
            } else {

                $result .= ',' . $orderId;
            }
        }

        return $result;
    }

    /**
     * @return int
     */
    private function getRandomSecondForFirstInterval()
    {
        if (empty($this->firstTimeIntervalService)) {

            $this->firstTimeIntervalService = new TimeBetweenOrders(['type' => 'morning']);

            $this->firstTimeIntervalService->setBottomLine(36000);
            $this->firstTimeIntervalService->setUpperLine(50400);
        }

        return $this->firstTimeIntervalService->getSecond();
    }

    /**
     * @return int
     */
    private function getRandomSecondForSecondInterval()
    {
        if (empty($this->secondTimeIntervalService)) {

            $this->secondTimeIntervalService = new TimeBetweenOrders(['type' => 'afternoon']);

            $this->secondTimeIntervalService->setBottomLine(50400);
            $this->secondTimeIntervalService->setUpperLine(64800);
        }

        return $this->secondTimeIntervalService->getSecond();
    }

    /**
     * @return int
     */
    private function getRandomSecondForThirdInterval()
    {
        if (empty($this->thirdTimeIntervalService)) {

            $this->thirdTimeIntervalService = new TimeBetweenOrders(['type' => 'evening']);

            $this->thirdTimeIntervalService->setBottomLine(64800);
            $this->thirdTimeIntervalService->setUpperLine(79200);
        }

        return $this->thirdTimeIntervalService->getSecond();
    }

    private function finalCheckTime()
    {
        /**
         * Необходимо проверить, что время между доставками больше 50 минут
         * если это не так, то пофиксить это
         */

        $saleDates = Order::find()
            ->select('DATE(date_time_sales) as date')
            ->where('lost = 0 AND cancel = 0')
            ->groupBy('DATE(date_time_sales)')
            ->orderBy('DATE(date_time_sales) ASC')
            ->asArray()
            ->all();

        foreach ($saleDates as $saleDate) {

            $ordersInThatDay = Order::find()
                ->select([
                    '`order`.id',
                    '`order`.id_makes_order_date',
                    '`order`.date_time_sales',
                ])
                ->join('JOIN', 'client', '`order`.id_client = client.id')
                ->where(
                    '`order`.cancel = 0 AND `order`.lost = 0 AND DATE(`order`.date_time_sales) = "' . $saleDate['date'] . '"'
                )
                ->orderBy('date_time_sales ASC')
                ->asArray()
                ->all();

            $countOrdersInThatDay = count($ordersInThatDay);

            if (($countOrdersInThatDay % 2) != 0) {

                $countOrdersInDostavista = ceil($countOrdersInThatDay / 2);
            } else {

                $countOrdersInDostavista = round($countOrdersInThatDay / 2);
            }

            for (
                $indexOrdersInDostavista = 0, $indexOrdersInThatDay = 0;
                $indexOrdersInDostavista < $countOrdersInDostavista;
                ++$indexOrdersInDostavista, ++$indexOrdersInThatDay
            ) {

                ++$indexOrdersInThatDay;

                /**
                 * По второму заказу должна быть проверка на существование элемента
                 */
                if (isset($ordersInThatDay[$indexOrdersInThatDay])) {

                    /**
                     * Осуществляем проверку
                     */
                    $prevDateTime = new \DateTime($ordersInThatDay[$indexOrdersInThatDay - 1]['date_time_sales']);
                    $thisDateTime = new \DateTime($ordersInThatDay[$indexOrdersInThatDay]['date_time_sales']);

                    $interval = $prevDateTime->diff($thisDateTime);

                    $second = $interval->s + $interval->i * 60 + $interval->h * 3600;

                    if ($second < 3000) {

                        /**
                         * Меняем значение времени продажи у заказа
                         */

                        /** @var Order $order */
                        $order = Order::findOne(['id' => $ordersInThatDay[$indexOrdersInThatDay]['id']]);

                        $salesDate = new \DateTime($order->date_time_sales);

                        $interval = new \DateInterval('PT' . mt_rand(3000, 4200) . 'S');

                        $salesDate->add($interval);

                        $format = $salesDate->format('Y-m-d H:i:s');

                        $order->date_time_sales = $format;

                        $order->save();
                    }
                }
            }
        }
    }
}