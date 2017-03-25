<?php

namespace frontend\services\generator\orders;

use common\entity\generator\MakesOrdersDate;
use common\entity\generator\Order;

/**
 * Class Select
 * @package frontend\models
 */
class MakesOrderTime
{
    public function add()
    {
        /**
         * Выбрать все заказы
         */
        $orders = Order::find()
            ->all();

        $makesOrdersDates = MakesOrdersDate::find()
            ->asArray()
            ->all();

        /** @var Order $order */
        foreach ($orders as $order) {

            foreach ($makesOrdersDates as $makesOrdersDate) {

                if ($order->id_makes_order_date == $makesOrdersDate['id']) {

                    $makesOrdersDateTime = new \DateTime($makesOrdersDate['date']);

                    /**
                     * Случайное время с 10:00 - 22:00
                     */
                    $randSeconds = mt_rand(36000, 79200);

                    $interval = new \DateInterval('PT' . $randSeconds . 'S');

                    $makesOrdersDateTime->add($interval);

                    $makesOrdersDateTimeFormat = $makesOrdersDateTime->format('Y-m-d H:i:s');

                    $order->date_time_makes_order = $makesOrdersDateTimeFormat;

                    $order->save();
                }
            }
        }
    }
}