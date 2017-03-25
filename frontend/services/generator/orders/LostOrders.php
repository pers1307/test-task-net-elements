<?php

namespace frontend\services\generator\orders;

use common\entity\generator\Order;
use common\entity\generator\PositionInOrder;
use common\entity\generator\SelectionProduct;
use common\entity\generator\Setting;

class LostOrders
{
    /**
     * @var float
     */
    private $margin;

    /**
     * @var int
     */
    private $count;

    /**
     * @var float
     */
    private $minTotal;

    /**
     * @var float
     */
    private $totalMargin = 0;

    /**
     * @var int
     */
    private $limitMinPriceProduct;

    /**
     * @var int
     */
    private $limitMaxPriceProduct;

    /**
     * @param float $margin
     */
    public function setMargin($margin)
    {
        $this->margin = $margin;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @param float $minTotal
     */
    public function setMinTotal($minTotal)
    {
        $this->minTotal = $minTotal;
    }

    /**
     * @return float
     */
    public function getTotalMargin()
    {
        return $this->totalMargin;
    }

    /**
     * @param double $limitMinPriceProduct
     */
    public function setLimitMinPriceProduct($limitMinPriceProduct)
    {
        $this->limitMinPriceProduct = $limitMinPriceProduct;
    }

    /**
     * @param double $limitMaxPriceProduct
     */
    public function setLimitMaxPriceProduct($limitMaxPriceProduct)
    {
        $this->limitMaxPriceProduct = $limitMaxPriceProduct;
    }

    /**
     * @return int
     */
    public function getRecommendCount()
    {
        $countOrders = Order::count();

        return floor($countOrders * 0.5);
    }

    /**
     * @return int
     */
    public function getRecommendMinTotal()
    {
        $minSumOrder = Setting::find()
            ->where(['parameter' => 'minSumOrder'])
            ->asArray()
            ->one();

        if (!empty($minSumOrder)) {

            $minSumOrder = $minSumOrder['value'];
        } else {

            $minSumOrder = '';
        }

        return $minSumOrder;
    }

    /**
     * @return float
     */
    public function getPriceLimit()
    {
        $selectPriceLimit = Setting::find()
            ->where(['parameter' => 'selectPriceLimit'])
            ->asArray()
            ->one();

        if (!empty($selectPriceLimit)) {

            $selectPriceLimit = $selectPriceLimit['value'];
        } else {

            $selectPriceLimit = '';
        }

        return $selectPriceLimit;
    }

    public function add()
    {
        $this->deleteAll();

        $selectMarginService = new SelectMargin();

        $selectMarginService->setMargin($this->margin);
        $selectMarginService->setLimitMinPriceProduct($this->limitMinPriceProduct);
        $selectMarginService->setLimitMaxPriceProduct($this->limitMaxPriceProduct);

        $selectMarginService->newProducts();

        $this->totalMargin = $selectMarginService->getSelectedMargin();

        $divideProductsOnOrdersService = new DivideProductsOnOrders();

        $divideProductsOnOrdersService->setMinSumOrder($this->minTotal);
        $divideProductsOnOrdersService->setCountOrders($this->count);

        $divideProductsOnOrdersService->sliceNewProductOnLostOrders();
        $divideProductsOnOrdersService->gumPositionsInOrders();
        $divideProductsOnOrdersService->makeTotalOrders();

        SelectionProduct::resetNewStatus();
    }

    public function deleteAll()
    {
        $cancelOrders = Order::find()
            ->select('id')
            ->where(['lost' => 1])
            ->asArray()
            ->all();

        foreach ($cancelOrders as $cancelOrder) {

            PositionInOrder::deleteAll(['id_order' => $cancelOrder['id']]);
        }

        Order::deleteAll(['lost' => 1]);
    }

    public function normalizationOrderId()
    {
        $orders = Order::find()->all();

        $count = 1;

        foreach ($orders as $order) {

            $orderId = $order->id;

            $positions = PositionInOrder::findAll(['id_order' => $orderId]);

            foreach ($positions as $position) {

                $position->id_order = $count;

                $position->save();
            }

            $order->id = $count;

            $order->save();

            ++$count;
        }
    }

    /**
     * @return int
     */
    public function getCountOrder()
    {
        $countLostOrders = Order::find()
            ->where(['lost' => 1])
            ->count();

        return $countLostOrders;
    }

    /**
     * @return float
     */
    public function getMinFactSumOrder()
    {
        $minSumOrder = Order::find()
            ->select('MIN(total) as min')
            ->where(['lost' => 1])
            ->asArray()
            ->one();

        return $minSumOrder['min'];
    }

    /**
     * @return float
     */
    public function getMaxFactOrder()
    {
        $maxSumOrder = Order::find()
            ->select('MAX(total) as max')
            ->where(['lost' => 1])
            ->asArray()
            ->one();

        return $maxSumOrder['max'];
    }

    /**
     * @return float
     */
    public function getAvgFactOrder()
    {
        $maxSumOrder = Order::find()
            ->select('AVG(total) as avg')
            ->where(['lost' => 1])
            ->asArray()
            ->one();

        return $maxSumOrder['avg'];
    }

    /**
     * @return int
     */
    public function getMinFactCountProductInOrder()
    {
        $minCountProductInOrder = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT MIN(order_count.count) as min
                    FROM
                    (
                        SELECT id_order, COUNT(position_in_order.count) as count
                        FROM position_in_order
                        JOIN product ON position_in_order.id_product = product.id
                        JOIN `order` ON position_in_order.id_order = `order`.id
                        WHERE `order`.lost = 1
                        GROUP BY id_order
                    ) as order_count
                '
            )
            ->queryOne();

        return $minCountProductInOrder['min'];
    }

    /**
     * @return int
     */
    public function getMaxFactCountProductInOrder()
    {
        $minCountProductInOrder = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT MAX(order_count.count) as max
                    FROM
                    (
                        SELECT id_order, COUNT(position_in_order.count) as count
                        FROM position_in_order
                        JOIN product ON position_in_order.id_product = product.id
                        JOIN `order` ON position_in_order.id_order = `order`.id
                        WHERE `order`.lost = 1
                        GROUP BY id_order
                    ) as order_count
                '
            )
            ->queryOne();

        return $minCountProductInOrder['max'];
    }

    /**
     * @return float
     */
    public function getAvgFactCountProductInOrder()
    {
        $minCountProductInOrder = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT AVG(order_count.count) as avg
                    FROM
                    (
                        SELECT id_order, COUNT(position_in_order.count) as count
                        FROM position_in_order
                        JOIN product ON position_in_order.id_product = product.id
                        JOIN `order` ON position_in_order.id_order = `order`.id
                        WHERE `order`.lost = 1
                        GROUP BY id_order
                    ) as order_count
                '
            )
            ->queryOne();

        return floor($minCountProductInOrder['avg']);
    }

    /**
     * @return float
     */
    public function getFactMargin()
    {
        $minCountProductInOrder = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT SUM(margin) as margin
                    FROM
                    (
                        SELECT (product.price_shop - product.price_purchase) * position_in_order.count as margin
                        FROM `order`
                        JOIN position_in_order ON `order`.id = position_in_order.id_order
                        JOIN product ON position_in_order.id_product = product.id
                        WHERE lost = 1
                        AND `name` <> "Доставка"
                        GROUP BY product.`name`
                        ORDER BY count DESC
                    ) as result
                '
            )
            ->queryOne();

        return $minCountProductInOrder['margin'];
    }
}