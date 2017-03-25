<?php

namespace frontend\services\generator\orders;

use common\entity\generator\Order;
use common\entity\generator\PositionInOrder;
use common\entity\generator\SelectionProduct;
use common\entity\generator\Setting;

/**
 * Class Select
 * Модель выборки из базы по параметрам
 * @package frontend\models
 */
class DivideProductsOnOrders
{
    /**
     * @var int
     */
    public $countOrders;

    /**
     * @var float
     */
    public $minSumOrder;

    /**
     * @var int
     */
    private $topLimit;

    /**
     * Позиция id в selection_product
     * @var int
     */
    private $indexPosition;

    /**
     * @var int
     */
    private $bottomLimit;

    /**
     * @var int
     */
    private $orderTopLimit;

    /**
     * @var int
     */
    private $orderBottomLimit;

    /**
     * @param int $countOrders
     */
    public function setCountOrders($countOrders)
    {
        $this->countOrders = $countOrders;
    }

    /**
     * @param int $minSumOrder
     */
    public function setMinSumOrder($minSumOrder)
    {
        $this->minSumOrder = $minSumOrder;
    }

    /**
     * @return float
     */
    public function getSumFromSelectedProducts()
    {
        $result = SelectionProduct::find()
            ->select('SUM(product.price_shop) as sum')
            ->join('join', 'product', 'selection_product.id_product = product.id')
            ->asArray()
            ->one();

        return $result['sum'];
    }

    public function sliceProductOnOrders()
    {
        $this->resetOrders();
        $this->resetPositionsInOrders();

        $this->findLimitIdInSelectionProduct();

        $setting = new Setting();
        $setting->add('countOrders', $this->countOrders);

        $setting = new Setting();
        $setting->add('minSumOrder', $this->minSumOrder);

        $countDoneOrders = 0;
        $costOrder       = 0;

        $this->indexPosition = $this->bottomLimit;

        do {
            $idsInOrder = [];

            while ($costOrder < $this->minSumOrder) {

                if ($this->indexPosition <= $this->topLimit) {

                    $product = SelectionProduct::find()
                        ->select(['selection_product.id_product', 'product.price_shop as price'])
                        ->join('JOIN', 'product', 'selection_product.id_product = product.id')
                        ->where(['selection_product.id' => $this->indexPosition])
                        ->asArray()
                        ->one();

                    $idsInOrder[] = $product['id_product'];
                    ++$this->indexPosition;
                    $costOrder += $product['price'];
                } else {
                    break;
                }
            }

            $order = new Order();
            $order->save();

            foreach ($idsInOrder as $id) {

                $orderPosition = new PositionInOrder();

                $orderPosition->id_product = $id;
                $orderPosition->count      = 1;
                $orderPosition->id_order   = $order->id;

                $orderPosition->save();
            }

            ++$countDoneOrders;
            $costOrder = 0;

            if ($this->indexPosition > $this->topLimit) {
                break;
            }

        } while ($countDoneOrders < $this->countOrders);

        if ($this->indexPosition <= $this->topLimit) {

            $this->findLimitIdInOrder();

            $selectedProducts = SelectionProduct::find()
                ->where('id >= ' . $this->indexPosition)
                ->asArray()
                ->all();

            foreach ($selectedProducts as $product) {

                $randomOrder = $this->getRandOrderId();

                $orderPosition = new PositionInOrder();

                $orderPosition->id_product = $product['id_product'];
                $orderPosition->count      = 1;
                $orderPosition->id_order   = $randomOrder;

                $orderPosition->save();
            }
        }
    }

    public function sliceNewProductOnCancelOrders()
    {
        $this->findLimitIdInNewSelectionProduct();

        $countDoneOrders = 0;
        $costOrder       = 0;

        $this->indexPosition = $this->bottomLimit;

        do {
            $idsInOrder = [];

            while ($costOrder < $this->minSumOrder) {

                if ($this->indexPosition <= $this->topLimit) {

                    $product = SelectionProduct::find()
                        ->select(['selection_product.id_product', 'product.price_shop as price'])
                        ->join('JOIN', 'product', 'selection_product.id_product = product.id')
                        ->where(['selection_product.id' => $this->indexPosition])
                        ->asArray()
                        ->one();

                    $idsInOrder[] = $product['id_product'];
                    ++$this->indexPosition;
                    $costOrder += $product['price'];
                } else {
                    break;
                }
            }

            $order = new Order();

            $order->id_reason_cansel = 1;
            $order->cancel           = 1;

            $order->save();

            foreach ($idsInOrder as $id) {

                $orderPosition = new PositionInOrder();

                $orderPosition->id_product = $id;
                $orderPosition->count      = 1;
                $orderPosition->id_order   = $order->id;

                $orderPosition->save();
            }

            ++$countDoneOrders;
            $costOrder = 0;

            if ($this->indexPosition > $this->topLimit) {
                break;
            }

        } while ($countDoneOrders < $this->countOrders);

        if ($this->indexPosition <= $this->topLimit) {

            $this->findLimitIdInCancelOrder();

            $selectedProducts = SelectionProduct::find()
                ->where('id >= ' . $this->indexPosition)
                ->asArray()
                ->all();

            foreach ($selectedProducts as $product) {

                $randomOrder = $this->getRandOrderId();

                $orderPosition = new PositionInOrder();

                $orderPosition->id_product = $product['id_product'];
                $orderPosition->count      = 1;
                $orderPosition->id_order   = $randomOrder;

                $orderPosition->save();
            }
        }
    }

    public function sliceNewProductOnLostOrders()
    {
        $this->findLimitIdInNewSelectionProduct();

        $countDoneOrders = 0;
        $costOrder       = 0;

        $this->indexPosition = $this->bottomLimit;

        do {
            $idsInOrder = [];

            while ($costOrder < $this->minSumOrder) {

                if ($this->indexPosition <= $this->topLimit) {

                    $product = SelectionProduct::find()
                        ->select(['selection_product.id_product', 'product.price_shop as price'])
                        ->join('JOIN', 'product', 'selection_product.id_product = product.id')
                        ->where(['selection_product.id' => $this->indexPosition])
                        ->asArray()
                        ->one();

                    $idsInOrder[] = $product['id_product'];
                    ++$this->indexPosition;
                    $costOrder += $product['price'];
                } else {
                    break;
                }
            }

            $order = new Order();

            $order->lost = 1;

            $order->save();

            foreach ($idsInOrder as $id) {

                $orderPosition = new PositionInOrder();

                $orderPosition->id_product = $id;
                $orderPosition->count      = 1;
                $orderPosition->id_order   = $order->id;

                $orderPosition->save();
            }

            ++$countDoneOrders;
            $costOrder = 0;

            if ($this->indexPosition > $this->topLimit) {
                break;
            }

        } while ($countDoneOrders < $this->countOrders);

        if ($this->indexPosition <= $this->topLimit) {

            $this->findLimitIdInLostOrder();

            $selectedProducts = SelectionProduct::find()
                ->where('id >= ' . $this->indexPosition)
                ->asArray()
                ->all();

            foreach ($selectedProducts as $product) {

                $randomOrder = $this->getRandOrderId();

                $orderPosition = new PositionInOrder();

                $orderPosition->id_product = $product['id_product'];
                $orderPosition->count      = 1;
                $orderPosition->id_order   = $randomOrder;

                $orderPosition->save();
            }
        }
    }

    public function gumPositionsInOrders()
    {
        $this->findLimitIdInOrder();

        $index = $this->orderBottomLimit;

        while ($index <= $this->orderTopLimit) {

            $positions = PositionInOrder::find()
                ->select(['id', 'id_product', 'count'])
                ->where(['id_order' => $index])
                ->asArray()
                ->all();

            $sawPositions = [];

            foreach ($positions as $position) {

                if (!empty($sawPositions)) {

                    $idSamePosition = null;

                    foreach ($sawPositions as $sawPosition) {

                        if ($sawPosition['id_product'] == $position['id_product']) {
                            $idSamePosition = $sawPosition['id'];
                        }
                    }

                    if (!empty($idSamePosition)) {

                        /** @var PositionInOrder $positionForGym */
                        $positionForGym = PositionInOrder::findOne(['id' => $idSamePosition]);

                        $positionForGym->count += $position['count'];

                        $positionForGym->save();

                        PositionInOrder::deleteAll(['id' => $position['id']]);
                    } else {
                        $sawPositions[] = $position;
                    }

                } else {
                    $sawPositions[] = $position;
                }
            }

            ++$index;
        }
    }

    public function makeTotalOrders()
    {
        $ordersWithTotal = PositionInOrder::find()
            ->select(['id_order', 'SUM(price_shop * count) sum'])
            ->join('JOIN', 'product', 'position_in_order.id_product = product.id')
            ->groupBy('id_order')
            ->asArray()
            ->all();

        foreach ($ordersWithTotal as $orderWithTotal) {

            /** @var Order $order */
            $order = Order::findOne($orderWithTotal['id_order']);

            if (!is_null($order)) {

                $order->total = $orderWithTotal['sum'];

                $order->save();
            }
        }
    }

    public function getMinFactSumOrder()
    {
        $minSumOrder = Order::find()
            ->select('MIN(total) as min')
            ->asArray()
            ->one();

        return $minSumOrder['min'];
    }

    public function getMaxFactOrder()
    {
        $maxSumOrder = Order::find()
            ->select('MAX(total) as max')
            ->asArray()
            ->one();

        return $maxSumOrder['max'];
    }

    public function getAvgFactOrder()
    {
        $maxSumOrder = Order::find()
            ->select('AVG(total) as avg')
            ->asArray()
            ->one();

        return $maxSumOrder['avg'];
    }

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
                        GROUP BY id_order
                    ) as order_count
                '
            )
            ->queryOne();

        return $minCountProductInOrder['min'];
    }

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
                        GROUP BY id_order
                    ) as order_count
                '
            )
            ->queryOne();

        return $minCountProductInOrder['max'];
    }

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
                        GROUP BY id_order
                    ) as order_count
                '
            )
            ->queryOne();

        return floor($minCountProductInOrder['avg']);
    }

    /**
     * @return int
     */
    public function getCountOrders()
    {
        $countOrders = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT COUNT(*) as count
                    FROM `order`
                    WHERE lost = 0
                '
            )
            ->queryOne();

        return $countOrders['count'];
    }

    private function findLimitIdInSelectionProduct()
    {
        $result = SelectionProduct::find()
            ->select('MIN(id) as min')
            ->asArray()
            ->one();

        $this->bottomLimit = $result['min'];

        $result = SelectionProduct::find()
            ->select('MAX(id) as max')
            ->asArray()
            ->one();

        $this->topLimit = $result['max'];
    }

    private function findLimitIdInNewSelectionProduct()
    {
        $result = SelectionProduct::find()
            ->select('MIN(id) as min')
            ->where(['new' => 1])
            ->asArray()
            ->one();

        $this->bottomLimit = $result['min'];

        $result = SelectionProduct::find()
            ->select('MAX(id) as max')
            ->where(['new' => 1])
            ->asArray()
            ->one();

        $this->topLimit = $result['max'];
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

    private function findLimitIdInCancelOrder()
    {
        $result = Order::find()
            ->select('MIN(id) as min')
            ->where(['cancel' => '1'])
            ->asArray()
            ->one();

        $this->orderBottomLimit = $result['min'];

        $result = Order::find()
            ->select('MAX(id) as max')
            ->where(['cancel' => '1'])
            ->asArray()
            ->one();

        $this->orderTopLimit = $result['max'];
    }

    private function findLimitIdInLostOrder()
    {
        $result = Order::find()
            ->select('MIN(id) as min')
            ->where(['lost' => '1'])
            ->asArray()
            ->one();

        $this->orderBottomLimit = $result['min'];

        $result = Order::find()
            ->select('MAX(id) as max')
            ->where(['lost' => '1'])
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

    private function resetPositionsInOrders()
    {
        PositionInOrder::deleteAll();
    }

    private function resetOrders()
    {
        Order::deleteAll();
    }
}