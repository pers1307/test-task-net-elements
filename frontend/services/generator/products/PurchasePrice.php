<?php

namespace frontend\services\generator\products;

use common\entity\generator\Product;

/**
 * Class DataBaseCleaner
 * @package frontend\services
 */
class PurchasePrice
{
    /**
     * @var int
     */
    private $averageMargin;

    /**
     * @var array
     */
    private $randMarginValues = [];

    /**
     * @param int $averageMargin
     */
    public function setAverageMargin($averageMargin)
    {
        $this->averageMargin = $averageMargin;
    }

    public function execute()
    {
        $countProduct = Product::find()->count();

        $products = Product::find()->asArray()->all();

        for ($cursor = 0; $cursor < $countProduct; ++$cursor) {

            $randMarginValue = $this->averageMargin + $this->getRandMargin();

            $this->randMarginValues[] = $randMarginValue;

            $product = Product::findOne(['id' => $products[$cursor]['id']]);

            $percent = ($randMarginValue / 100) + 1;

            $product->price_purchase = $product->price_shop / $percent;

            $product->save();
        }
    }

    public function getFactAvgMargin()
    {
        $count = count($this->randMarginValues);

        $sum = 0;

        foreach ($this->randMarginValues as $item) {

            $sum += $item;
        }

        return number_format($sum / $count, 2);
    }

    /**
     * @return float
     */
    public function getFactMaxMargin()
    {
        $maxFactMargin = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT
                    MAX((price_shop / price_purchase) * 100 - 100) as max
                    FROM product
                '
            )
            ->queryOne();

        return number_format($maxFactMargin['max'], 2);
    }

    /**
     * @return float
     */
    public function getFactMinMargin()
    {
        $minFactMargin = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT
                    MIN((price_shop / price_purchase) * 100 - 100) as min
                    FROM product
                '
            )
            ->queryOne();

        return number_format($minFactMargin['min'], 2);
    }

    /**
     * @return float
     */
    private function getRandMargin()
    {
        $halfAvgMargin = $this->averageMargin / 2;

        return mt_rand(0 - $halfAvgMargin, $halfAvgMargin);
    }
}