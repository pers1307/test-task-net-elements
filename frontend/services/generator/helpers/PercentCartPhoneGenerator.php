<?php

namespace frontend\services\generator\helpers;

use Yii;

/**
 * Class PercentCartPhoneGenerator
 * @package frontend\services
 */
class PercentCartPhoneGenerator
{
    /**
     * @var int
     */
    public $minPercentForCart;

    public function setMinPercentForCart($percent)
    {
        $this->minPercentForCart = $percent;
    }

    /**
     * @return array
     */
    public function getPercent()
    {
        $result = [];

        if ($this->minPercentForCart < 65) {

            $result[0] = mt_rand(65, 90);
            $result[1] = 100 - $result[0];
        } else {

            $result[0] = mt_rand($this->minPercentForCart, 100);
            $result[1] = 100 - $result[0];
        }

        return $result;
    }
}