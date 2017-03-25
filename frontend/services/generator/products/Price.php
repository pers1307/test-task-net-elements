<?php

namespace frontend\services\generator\products;

use common\entity\generator\Product;

/**
 * Class Price
 * @package frontend\services
 */
class Price
{
    /**
     * @var double
     */
    public $maxPrice;

    /**
     * @var double
     */
    public $minPrice;


    public function __construct()
    {
        $this->maxPrice = $this->getMaximumPriceProduct();
        $this->minPrice = $this->getMinimumPriceProduct();
    }

    /**
     * @return double
     */
    public function getMinimumPriceProduct()
    {
        $result = Product::find()
            ->select('MIN(price_shop) as price')
            ->asArray()
            ->one();

        return round($result['price']);
    }

    /**
     * @return double
     */
    public function getMaximumPriceProduct()
    {
        $result = Product::find()
            ->select('MAX(price_shop) as price')
            ->asArray()
            ->one();

        return round($result['price']);
    }

    /**
     * @param string $range
     *
     * @return int
     */
    public function getMaximumPrice($range)
    {
        $range = explode(';', $range);

        return $range[1];
    }

    /**
     * @param string $range
     *
     * @return int
     */
    public function getMinimumPrice($range)
    {
        $range = explode(';', $range);

        return $range[0];
    }
}