<?php

namespace frontend\services\generator\helpers;

/**
 * Class Select
 * Модель выборки из базы по параметрам
 * @package frontend\models
 */
class PercentGenerator
{
    /**
     * @var array
     */
    private $firstRange = [];

    /**
     * @var array
     */
    private $secondRange = [];

    /**
     * @param $array
     */
    public function setFirstRange($array)
    {
        $this->firstRange = $array;
    }

    public function setSecondRange($array)
    {
        $this->secondRange = $array;
    }

    public function getPercents()
    {
        $result = [];

        $result[0] = mt_rand($this->firstRange[0],  $this->firstRange[1]);
        $result[1] = mt_rand($this->secondRange[0], $this->secondRange[1]);

        $result[2] = 100 - ($result[0] + $result[1]);

        return $result;
    }
}