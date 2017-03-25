<?php

namespace frontend\services\generator\orders;

use common\entity\generator\Product;
use common\entity\generator\SelectionProduct;

/**
 * Class Select
 * Модель выборки из базы по параметрам
 * @package frontend\models
 */
class SelectMargin
{
    /**
     * @var int
     */
    public $margin;

    /**
     * @var float
     */
    public $selectedMargin;

    /**
     * Определяет количество итераций за которые сойдется процесс
     * @var int
     */
    private $itemOnOneSelect = 10;

    /**
     * @var int
     */
    private $topLimit;

    /**
     * @var int
     */
    private $bottomLimit;

    /**
     * @var int
     */
    private $limitMinPriceProduct;

    /**
     * @var int
     */
    private $limitMaxPriceProduct;

    /**
     * @param int $margin
     */
    public function setMargin($margin)
    {
        $this->margin = $margin;
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
     * @return float
     */
    public function getSelectedMargin()
    {
        return $this->selectedMargin;
    }

    public function selectMargin()
    {
        $this->resetSelectedProducts();

        $this->findLimitIdInDataBase();

        do {
            $randIds = $this->getArrayRandId();

            foreach ($randIds as $id) {

                /**
                 * Проверка продукта
                 */
                /** @var Product $product */
                $product = Product::findOne(['id' => $id]);

                if ($product->name == 'Доставка') {

                    continue;
                }

                if (!empty($this->limitMinPriceProduct)) {

                    if ($product->price_shop < $this->limitMinPriceProduct) {

                        continue;
                    }
                }

                if (!empty($this->limitMaxPriceProduct)) {

                    if ($product->price_shop > $this->limitMaxPriceProduct) {

                        continue;
                    }
                }

                if ($product->price_shop == 0) {

                    continue;
                }

                $selectProduct = new SelectionProduct();
                $selectProduct->id_product = $id;
                $selectProduct->save();
            }

            $marginLevelFromBase = $this->getMaginFromSelectedProducts();

        } while ($marginLevelFromBase < $this->margin);

        /**
         * Теперь пойдем в обратную сторону, чтобы удостовериться что мы выбрали товары
         * максимально оптимально
         */
        do {
            $marginWithoutLastProduct = $this->getMaginFromSelectedProductsWithoutLast();

            if ($marginWithoutLastProduct > $this->margin) {

                $this->deleteLastSelectedProduct();
            } else {

                break;
            }

        } while (true);

        $this->selectedMargin = $this->getMaginFromSelectedProducts();
    }

    public function newProducts()
    {
        $this->findLimitIdInDataBase();

        do {
            $randIds = $this->getArrayRandId();

            foreach ($randIds as $id) {

                /**
                 * Проверка продукта
                 */
                /** @var Product $product */
                $product = Product::findOne(['id' => $id]);

                if ($product->name == 'Доставка') {

                    continue;
                }

                if (!empty($this->limitMinPriceProduct)) {

                    if ($product->price_shop < $this->limitMinPriceProduct) {

                        continue;
                    }
                }

                if (!empty($this->limitMaxPriceProduct)) {

                    if ($product->price_shop > $this->limitMaxPriceProduct) {

                        continue;
                    }
                }

                if ($product->price_shop == 0) {

                    continue;
                }

                $selectProduct = new SelectionProduct();

                $selectProduct->id_product = $id;
                $selectProduct->new        = 1;

                $selectProduct->save();
            }

            $marginLevelFromBase = $this->getMaginFromNewSelectedProducts();

        } while ($marginLevelFromBase < $this->margin);

        /**
         * Теперь пойдем в обратную сторону, чтобы удостовериться что мы выбрали товары
         * максимально оптимально
         */
        do {
            $marginWithoutLastProduct = $this->getMaginFromNewSelectedProductsWithoutLast();

            if ($marginWithoutLastProduct > $this->margin) {

                $this->deleteLastNewSelectedProduct();
            } else {

                break;
            }

        } while (true);

        $this->selectedMargin = $this->getMaginFromNewSelectedProducts();
    }

    /**
     * @return int
     */
    public function getCountSameProduct()
    {
        $countSameProduct = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT COUNT(*) count
                    FROM
                    (
                        SELECT id_product, COUNT(id_product) AS count
                        FROM selection_product
                        GROUP BY id_product
                    ) as result
                    WHERE result.count > 2
                '
            )
            ->queryOne();

        return $countSameProduct['count'];
    }

    /**
     * @return int
     */
    public function getCountUniqueProduct()
    {
        $countUniqueProduct = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT COUNT(*) count
                    FROM
                    (
                        SELECT id_product, COUNT(id_product) AS count
                        FROM selection_product
                        GROUP BY id_product
                    ) as result
                    WHERE result.count = 1
                '
            )
            ->queryOne();

        return $countUniqueProduct['count'];
    }

    /**
     * @return int
     */
    public function getCountProduct()
    {
        $countProduct = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT COUNT(*) AS count
                    FROM selection_product
                '
            )
            ->queryOne();

        return $countProduct['count'];
    }

    /**
     * @return int
     */
    public function getCountMaxSameProduct()
    {
        $countMaxSameProduct = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT MAX(result.count) max
                    FROM
                    (
                        SELECT id_product, COUNT(id_product) AS count
                        FROM selection_product
                        GROUP BY id_product
                    ) as result
                    WHERE result.count > 2
                '
            )
            ->queryOne();

        return $countMaxSameProduct['max'];
    }

    public function getSumFromSelectedProducts()
    {
        $result = SelectionProduct::find()
            ->select('SUM(product.price_shop) as sum')
            ->join('join', 'product', 'selection_product.id_product = product.id')
            ->asArray()
            ->one();

        return $result['sum'];
    }

    private function findLimitIdInDataBase()
    {
        $result = Product::find()
            ->select('MIN(id) as min')
            ->asArray()
            ->one();

        $this->bottomLimit = $result['min'];

        $result = Product::find()
            ->select('MAX(id) as max')
            ->asArray()
            ->one();

        $this->topLimit = $result['max'];
    }

    /**
     * @return int
     */
    private function getRandId()
    {
        return mt_rand($this->bottomLimit, $this->topLimit);
    }

    /**
     * @return array
     */
    private function getArrayRandId()
    {
        $result = [];

        for ($i = 0; $i < $this->itemOnOneSelect; $i++) {
            $result[] = $this->getRandId();
        }

        return $result;
    }

    private function getMaginFromSelectedProducts()
    {
        $result = SelectionProduct::find()
            ->select('SUM(product.price_shop - product.price_purchase) as summ')
            ->join('join', 'product', 'selection_product.id_product = product.id')
            ->asArray()
            ->one();

        return $result['summ'];
    }

    private function getMaginFromNewSelectedProducts()
    {
        $result = SelectionProduct::find()
            ->select('SUM(product.price_shop - product.price_purchase) as summ')
            ->join('join', 'product', 'selection_product.id_product = product.id')
            ->where(['new' => 1])
            ->asArray()
            ->one();

        return $result['summ'];
    }

    private function resetSelectedProducts()
    {
        SelectionProduct::deleteAll();
    }

    /**
     * @return int
     */
    private function getMaginFromSelectedProductsWithoutLast()
    {
        $result = SelectionProduct::find()
            ->select('SUM(product.price_shop - product.price_purchase) as summ')
            ->join('join', 'product', 'selection_product.id_product = product.id')
            ->where('selection_product.id <> (SELECT MAX(id) FROM selection_product)')
            ->asArray()
            ->one();

        return $result['summ'];
    }

    private function getMaginFromNewSelectedProductsWithoutLast()
    {
        $result = SelectionProduct::find()
            ->select('SUM(product.price_shop - product.price_purchase) as summ')
            ->join('join', 'product', 'selection_product.id_product = product.id')
            ->where('selection_product.id <> (SELECT MAX(id) FROM selection_product WHERE new = 1) AND selection_product.new = 1')
            ->asArray()
            ->one();

        return $result['summ'];
    }

    private function deleteLastSelectedProduct()
    {
        $result = SelectionProduct::find()
            ->select('MAX(id) lastId')
            ->asArray()
            ->one();

        $lastId = $result['lastId'];

        SelectionProduct::deleteAll('id = ' . $lastId);
    }

    private function deleteLastNewSelectedProduct()
    {
        $result = SelectionProduct::find()
            ->select('MAX(id) lastId')
            ->where(['new' => 1])
            ->asArray()
            ->one();

        $lastId = $result['lastId'];

        SelectionProduct::deleteAll('id = ' . $lastId);
    }
}
