<?php

namespace frontend\services\generator\file;

/**
 * Class Helper
 * @package frontend\services\generator\file
 */
class Helper
{
    /**
     * @var array
     */
    private $months = [
        1  => 'январь',
        2  => 'февраль',
        3  => 'март',
        4  => 'апрель',
        5  => 'май',
        6  => 'июнь',
        7  => 'июль',
        8  => 'август',
        9  => 'сентябрь',
        10 => 'октябрь',
        11 => 'ноябрь',
        12 => 'декабрь'
    ];

    /**
     * @return array
     */
    public function getMonths()
    {
        return $this->months;
    }

    /**
     * @return array
     */
    public function getYears()
    {
        $startYear = 2015;
        $nowYear   = date('Y');
        $result    = [];

        $indexYear = $startYear;

        while ($indexYear <= $nowYear) {

            $result[$indexYear] = $indexYear;

            ++$indexYear;
        }

        for ($index = 1; $index < 6; ++$index) {

            $result[$nowYear + $index] = $nowYear + $index;
        }

        return $result;
    }
}