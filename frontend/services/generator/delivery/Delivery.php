<?php

namespace frontend\services\generator\delivery;

use common\entity\generator\Delivery as DeliveryModel;
use common\entity\generator\Order;
use common\services\TimeConverter;

/**
 * Class Delivery
 * @package frontend\services
 */
class Delivery
{
    /**
     * @var string
     */
    public $products;

    /**
     * @var string
     */
    public $address;

    /**
     * @var string
     */
    public $comment;

    /**
     * @param string $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function makeList()
    {
        DeliveryModel::deleteAll();

        $saleDates = $this->getAllSalesDate();

        $this->makeByDays($saleDates);
    }

    private function getAllSalesDate()
    {
        $saleDates = Order::find()
            ->select('DATE(date_time_sales) as date')
            ->where('lost = 0 AND cancel = 0')
            ->groupBy('DATE(date_time_sales)')
            ->orderBy('DATE(date_time_sales) ASC')
            ->asArray()
            ->all();

        return $saleDates;
    }

    /**
     * @param array $saleDates
     */
    private function makeByDays($saleDates)
    {
        foreach ($saleDates as $saleDate) {

            $this->makeByDay($saleDate['date']);
        }
    }

    /**
     * @param string $day
     */
    private function makeByDay($day)
    {
        $ordersInThatDay = Order::find()
            ->select([
                '`order`.id',
                '`order`.total',
                '`order`.id_makes_order_date',
                '`order`.id_order_source',
                '`order`.id_client',
                '`order`.date_time_sales',
                '`order`.cancel',
                '`order`.id_reason_cansel',
                '`order`.lost',
                '`client`.first_name',
                '`client`.last_name',
                '`client`.address',
                '`client`.phone',
            ])
            ->join('JOIN', 'client', '`order`.id_client = client.id')
            ->where(
                '`order`.cancel = 0 AND `order`.lost = 0 AND DATE(`order`.date_time_sales) = "' . $day . '"'
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

            $salesDateTimeFirstOrder      = '';
            $salesDateTimeSecondOrder     = '';
            $secondOrderInDostavistaOrder = false;

            /** @var DeliveryModel $newDelivery */
            $newDelivery = new DeliveryModel();

            $newDelivery->content          = $this->products;
            $newDelivery->start_point      = $this->address;
            $newDelivery->user_description = $this->comment;

            $newDelivery->first_point       = $ordersInThatDay[$indexOrdersInThatDay]['address'];
            $newDelivery->first_time        = $ordersInThatDay[$indexOrdersInThatDay]['date_time_sales'];
            $newDelivery->first_phone       = $ordersInThatDay[$indexOrdersInThatDay]['phone'];
            $newDelivery->first_client_name = $ordersInThatDay[$indexOrdersInThatDay]['first_name'];
            $newDelivery->first_total       = $ordersInThatDay[$indexOrdersInThatDay]['total'];
            $newDelivery->first_order_id    = $ordersInThatDay[$indexOrdersInThatDay]['id'];

            /**
             * Предварительная дата, для установки даты расчета
             */
            $salesDateTimeFirstOrder = $ordersInThatDay[$indexOrdersInThatDay]['date_time_sales'];

            ++$indexOrdersInThatDay;

            /**
             * По второму заказу должна быть проверка на существование элемента
             */
            if (isset($ordersInThatDay[$indexOrdersInThatDay])) {

                $newDelivery->second_point = $ordersInThatDay[$indexOrdersInThatDay]['address'];
                $newDelivery->second_time = $ordersInThatDay[$indexOrdersInThatDay]['date_time_sales'];

                if (!empty($ordersInThatDay[$indexOrdersInThatDay]['phone'])) {

                    $newDelivery->second_phone = $ordersInThatDay[$indexOrdersInThatDay]['phone'];
                } else {

                    $newDelivery->second_phone = '';
                }

                if (!empty($ordersInThatDay[$indexOrdersInThatDay]['first_name'])) {

                    $newDelivery->second_client_name = $ordersInThatDay[$indexOrdersInThatDay]['first_name'];
                } else {

                    $newDelivery->second_client_name = '';
                }

                $newDelivery->second_total    = $ordersInThatDay[$indexOrdersInThatDay]['total'];
                $newDelivery->second_order_id = $ordersInThatDay[$indexOrdersInThatDay]['id'];

                /**
                 * Окончательная дата, для установки даты расчета
                 */
                $salesDateTimeSecondOrder = $ordersInThatDay[$indexOrdersInThatDay]['date_time_sales'];
            } else {

                $newDelivery->second_point       = '';
                $newDelivery->second_time        = null;
                $newDelivery->second_phone       = '';
                $newDelivery->second_client_name = '';
                $newDelivery->second_total       = '0';
                $newDelivery->second_order_id    = '0';
            }

            if (!empty($salesDateTimeSecondOrder)) {

                $newDelivery->finish  = $this->getFinishDateTime($salesDateTimeSecondOrder);
                $newDelivery->created = $this->getCreatedDateTime($salesDateTimeFirstOrder, $salesDateTimeSecondOrder);
            } else {

                $newDelivery->finish  = $this->getFinishDateTime($salesDateTimeFirstOrder);
                $newDelivery->created = $this->getCreatedDateTime($salesDateTimeFirstOrder, $salesDateTimeFirstOrder);
            }

            $newDelivery->start_time = $this->getStartTime($newDelivery->created, $newDelivery->finish);

            /**
             * todo: пока сделаем рандомным, потом завяжем на пользователе
             * todo: должно формироваться по тарифам, но пока непонятно как из сюда наложить
             */
            $newDelivery->price = mt_rand(200, 600);

            $newDelivery->save();
        }
    }

    /**
     * @param string $lastDateTimeDelivery
     *
     * @return string
     */
    private function getFinishDateTime($lastDateTimeDelivery)
    {
        /**
         * 20 - 40 минут
         */
        $randSeconds = $this->getRandSecond(1200, 2400);

        $salesDate = new \DateTime($lastDateTimeDelivery);

        $interval = new \DateInterval('PT' . $randSeconds . 'S');

        $salesDate->add($interval);

        $format = $salesDate->format('Y-m-d H:i:s');

        return $format;
    }

    /**
     * @param string $beforeThisDateTime
     * @param string $finishDateTime
     *
     * @return string
     */
    private function getCreatedDateTime($beforeThisDateTime, $finishDateTime)
    {
        /**
         * Генерируем массивы с числами
         */
        $arrayWithChance = [];

        for ($index = 0; $index < 63; ++$index) {

            $arrayWithChance[] = 'morning';
        }

        for ($index = 0; $index < 37; ++$index) {

            $arrayWithChance[] = 'evening';
        }

        $index = mt_rand(0, 99);

        $timeOfDay = $arrayWithChance[$index];

        if ($timeOfDay == 'evening') {

            $finishDateTime = new \DateTime($finishDateTime);

            $finishDateTime = $finishDateTime->format('Y-m-d');

            $finishDateTime = new \DateTime($finishDateTime);

            $oneDayAgo = new \DateInterval('P1D');

            $oneDayAgo->invert = 1;

            $finishDateTime->add($oneDayAgo);

            $eveningTimeInSecond = mt_rand(61200, 72000);

            $interval = new \DateInterval('PT' . $eveningTimeInSecond . 'S');

            $finishDateTime->add($interval);

            $format = $finishDateTime->format('Y-m-d H:i:s');

            return $format;
        } else {

            $beforeThisDateTime = new \DateTime($beforeThisDateTime);

            $hours = $beforeThisDateTime->format('H');

            if ($hours > 12) {

                $resultDateTime = $beforeThisDateTime->format('Y-m-d');
                $resultDateTime = new \DateTime($resultDateTime);

                $morningTimeInSecond = mt_rand(25200, 39600);

                $interval = new \DateInterval('PT' . $morningTimeInSecond . 'S');

                $resultDateTime->add($interval);

                $format = $resultDateTime->format('Y-m-d H:i:s');

                return $format;
            } else {

                $time = $beforeThisDateTime->format('H:i:s');

                $timeConverterService = new TimeConverter();

                $timeInSecond = $timeConverterService->stringTimeToSecond($time);

                $newUpperLevel = $timeInSecond - 5400;

                $resultDateTime = $beforeThisDateTime->format('Y-m-d');
                $resultDateTime = new \DateTime($resultDateTime);

                $morningTimeInSecond = mt_rand(25200, $newUpperLevel);

                $interval = new \DateInterval('PT' . $morningTimeInSecond . 'S');

                $resultDateTime->add($interval);

                $format = $resultDateTime->format('Y-m-d H:i:s');

                return $format;
            }
        }
    }

    /**
     * @param int $minSecond
     * @param int $maxSecond
     *
     * @return int
     */
    private function getRandSecond($minSecond, $maxSecond)
    {
        return mt_rand($minSecond, $maxSecond);
    }

    /**
     * @param string $makeOrderDateTime
     * @param string $finishOrderDateTime
     *
     * @return string
     */
    private function getStartTime($makeOrderDateTime, $finishOrderDateTime)
    {
        $makeOrderDateTime   = new \DateTime($makeOrderDateTime);
        $finishOrderDateTime = new \DateTime($finishOrderDateTime);

        $dayMakeOrderDateTime   = $makeOrderDateTime->format('d');
        $dayFinishOrderDateTime = $finishOrderDateTime->format('d');

        if ($dayMakeOrderDateTime == $dayFinishOrderDateTime) {

            /**
             * 20 - 50 минут
             */
            $randSeconds = $this->getRandSecond(1200, 3000);

            $salesDate = $makeOrderDateTime;

            $interval = new \DateInterval('PT' . $randSeconds . 'S');

            $salesDate->add($interval);

            $format = $salesDate->format('Y-m-d H:i:s');

            return $format;
        } else {

            $chanceArray = [];

            for ($index = 0; $index < 26; ++$index) {

                $chanceArray[] = 'evening';
            }

            for ($index = 0; $index < 74; ++$index) {

                $chanceArray[] = 'morning';
            }

            $result = mt_rand(0, 99);
            $result = $chanceArray[$result];

            if ($result == 'evening') {

                $randSeconds = $this->getRandSecond(1200, 3000);

                $interval = new \DateInterval('PT' . $randSeconds . 'S');

                $makeOrderDateTime->add($interval);

                $format = $makeOrderDateTime->format('Y-m-d H:i:s');

                return $format;
            } else {

                $result = $makeOrderDateTime->format('Y-m-d');
                $result = new \DateTime($result);

                $interval = new \DateInterval('P1D');

                $result->add($interval);

                $randSeconds = mt_rand(25200, 39600);

                $interval = new \DateInterval('PT' . $randSeconds . 'S');

                $result->add($interval);

                $format = $result->format('Y-m-d H:i:s');

                return $format;
            }
        }
    }
}