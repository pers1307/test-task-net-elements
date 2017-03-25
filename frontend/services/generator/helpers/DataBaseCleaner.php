<?php

namespace frontend\services\generator\helpers;

use common\entity\generator\Call;
use common\entity\generator\Client;
use common\entity\generator\Delivery;
use common\entity\generator\MakesOrdersDate;
use common\entity\generator\Order;
use common\entity\generator\PositionInOrder;
use common\entity\generator\Product;
use common\entity\generator\SalesDate;
use common\entity\generator\SelectionProduct;
use common\entity\generator\Setting;

/**
 * Class DataBaseCleaner
 * @package frontend\services
 */
class DataBaseCleaner
{
    /**
     * todo: refactor it!
     */
    public static function productClean()
    {
        SelectionProduct::deleteAll();
        Product::deleteAll();
        PositionInOrder::deleteAll();
        Order::deleteAll();

        Setting::remove('countOrders');
        Setting::remove('minSumOrder');
    }

    /**
     * todo: refactor it!
     */
    public static function clientClean()
    {
        Client::deleteAll();

        Order::updateAll(['id_client' => null]);
    }

    /**
     * todo: refactor it!
     */
    public static function callClean()
    {
        Call::deleteAll();
    }

    public function all()
    {
        Setting::deleteAll();
        Call::deleteAll();
        Order::deleteAll();
        SalesDate::deleteAll();
        MakesOrdersDate::deleteAll();
        PositionInOrder::deleteAll();
        SelectionProduct::deleteAll();
        Product::deleteAll();
        Client::deleteAll();
        Delivery::deleteAll();
    }

    public function calls()
    {
        Call::deleteAll();
    }

    public function orders()
    {
        SelectionProduct::deleteAll();
        PositionInOrder::deleteAll();
        Order::deleteAll();

        Setting::remove('countOrders');
        Setting::remove('minSumOrder');
    }

    public function products()
    {
        Product::deleteAll();
    }

    public function clients()
    {
        Client::deleteAll();
    }

    public function allUnlessProductsAndClients()
    {
        SelectionProduct::deleteAll();
        PositionInOrder::deleteAll();
        SalesDate::deleteAll();
        MakesOrdersDate::deleteAll();
        Order::deleteAll();
        Call::deleteAll();
        Setting::deleteAll();
        Delivery::deleteAll();
    }
}