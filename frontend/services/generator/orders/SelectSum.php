<?php

namespace frontend\services\generator\orders;

use common\entity\generator\Product;
use common\entity\generator\SelectionProduct;

/**
 * Class Select
 * Модель выборки из базы по параметрам
 * @package frontend\models
 */
class SelectSum
{
    /**
     * @var int
     */
    public $sum;

    /**
     * @var float
     */
    public $selectedSum;

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
     * @param int $sum
     */
    public function setSum($sum)
    {
        $this->sum = $sum;
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
    public function getSelectedSum()
    {
        return $this->selectedSum;
    }

    public function selectSum()
    {
        $this->resetSelectedProducts();

        $this->findLimitIdInDataBase();

        do {
            $randIds = $this->getArrayRandId();

            foreach ($randIds as $id) {

                /**
                 * Проверка продукта
                 */
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
                $selectProduct->new = 0;
                $selectProduct->save();
            }

            $sumLevelFromBase = $this->getSumFromSelectedProducts();

        } while ($sumLevelFromBase < $this->sum);

        /**
         * Теперь пойдем в обратную сторону, чтобы удостовериться что мы выбрали товары
         * максимально оптимально
         */
        do {
            $sumWithoutLastProduct = $this->getSumFromSelectedProductsWithoutLast();

            if ($sumWithoutLastProduct > $this->sum) {

                $this->deleteLastSelectedProduct();
            } else {

                break;
            }

        } while (true);

        $this->selectedSum = $this->getSumFromSelectedProducts();
    }

    public function addProductBySelectedSum()
    {
        $this->findLimitIdInDataBase();

        do {
            $randIds = $this->getArrayRandId();

            foreach ($randIds as $id) {

                /**
                 * Проверка продукта
                 */
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
                $selectProduct->new = 1;
                $selectProduct->save();
            }

            $sumLevelFromBase = $this->getSumFromNewSelectedProducts();

        } while ($sumLevelFromBase < $this->sum);
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

    public function getMaginFromSelectedProducts()
    {
        $result = SelectionProduct::find()
            ->select('SUM(product.price_shop - product.price_purchase) as sum')
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

    /**
     * @return float
     */
    private function getSumFromSelectedProducts()
    {
        $result = SelectionProduct::find()
            ->select('SUM(product.price_shop) as sum')
            ->join('join', 'product', 'selection_product.id_product = product.id')
            ->asArray()
            ->one();

        return $result['sum'];
    }

    /**
     * @return float
     */
    private function getSumFromNewSelectedProducts()
    {
        $result = SelectionProduct::find()
            ->select('SUM(product.price_shop) as sum')
            ->join('join', 'product', 'selection_product.id_product = product.id')
            ->where(['selection_product.new' => '1'])
            ->asArray()
            ->one();

        return $result['sum'];
    }

    private function resetSelectedProducts()
    {
        SelectionProduct::deleteAll();
    }

    /**
     * @return int
     */
    private function getSumFromSelectedProductsWithoutLast()
    {
        $result = SelectionProduct::find()
            ->select('SUM(product.price_shop) as sum')
            ->join('join', 'product', 'selection_product.id_product = product.id')
            ->where('selection_product.id <> (SELECT MAX(id) FROM selection_product)')
            ->asArray()
            ->one();

        return $result['sum'];
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
}