<?php

namespace frontend\services\generator\helpers;

/**
 * todo: Переделать! Это будет повешано на задачу.
 */

//use frontend\models\dostavista\Orders;
//use frontend\models\generator\Call;
//use frontend\models\generator\Delivery;
//use frontend\models\generator\Order;
//use frontend\models\openCart\Order as OpenCartOrder;
//use frontend\models\openCart\OrderProduct;
//use frontend\models\openCart\OrderTotal;
//use frontend\models\zadarma\Calls;
//use frontend\models\youMagic\Calls as YoumagicCalls;
//use Yii;

/**
 * Class DataBaseCleaner
 * @package frontend\services
 */
class ExternalSystemsCleaner
{
    public function openCart()
    {
        /** @var Order $exportedOrders */
        $exportedOrders = Order::find()->where('id_order_in_shop IS NOT NULL')->all();

        foreach ($exportedOrders as $exportedOrder) {

            /** @var Order $exportedOrder */
            $openCartId = $exportedOrder->id_order_in_shop;

            OpenCartOrder::deleteAll(['order_id' => $openCartId]);
            OrderProduct::deleteAll(['order_id' => $openCartId]);
            OrderTotal::deleteAll(['order_id' => $openCartId]);

            $exportedOrder->id_order_in_shop = '';

            $exportedOrder->save();
        }

        $count = count($exportedOrders);

        return $count;
    }

    public function zandarma()
    {
        $exportedCalls = Call::find()->where('zandarma_call_id IS NOT NULL')->all();

        foreach ($exportedCalls as $exportedCall) {

            /** @var Call $exportedCall */
            $zandarmaCallId = $exportedCall->zandarma_call_id;

            Calls::deleteAll(['call_id' => $zandarmaCallId]);

            $exportedCall->zandarma_call_id = '';

            $exportedCall->save();
        }

        $count = count($exportedCalls);

        return $count;
    }

    public function youmagic()
    {
        $exportedCalls = Call::find()->where('youmagic_call_id IS NOT NULL')->all();

        foreach ($exportedCalls as $exportedCall) {

            /** @var Call $exportedCall */
            $youmagicCallId = $exportedCall->youmagic_call_id;

            YoumagicCalls::deleteAll(['call_id' => $youmagicCallId]);

            $exportedCall->youmagic_call_id = '';

            $exportedCall->save();
        }

        $count = count($exportedCalls);

        return $count;
    }

    public function dostavista()
    {
        /** @var Delivery $exportedDelivery */
        $exportedDelivery = Delivery::find()->where('id_dostavista IS NOT NULL')->all();

        $arrayForCount = [];

        foreach ($exportedDelivery as $item) {

            /** @var Delivery $item */
            $dostavistaId = $item->id_dostavista;

            Orders::deleteAll(['id' => $dostavistaId]);

            if (!isset($arrayForCount[$dostavistaId])) {

                $arrayForCount[$dostavistaId] = 1;
            }

            $item->id_dostavista = '';
            $item->synchronization = 0;

            $item->save();
        }

        $count = count($arrayForCount);

        return $count;
    }
}