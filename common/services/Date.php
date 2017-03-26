<?php
/**
 * Date.php
 *
 * Сервис для работы с датой
 *
 * @author      Pereskokov Yurii
 * @copyright   2017 Pereskokov Yurii
 * @link        http://www.mediasite.ru/
 */

namespace common\services;

use KoKoKo\assert\Assert;
use Yii;

/**
 * Class Date
 * @package common\services
 */
class Date
{
    /**
     * @param string $firstDateTime
     * @param string $secondDateTime
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function firstDateTimeMoreSecondDateTime($firstDateTime, $secondDateTime)
    {
        Assert::assert($firstDateTime,  'firstDateTime')->notEmpty()->string();
        Assert::assert($secondDateTime, 'secondDateTime')->notEmpty()->string();

        $firstDateTime  = new \DateTime($firstDateTime);
        $secondDateTime = new \DateTime($secondDateTime);

        $firstDateTimeUnixFormat  = $firstDateTime->format('U');
        $secondDateTimeUnixFormat = $secondDateTime->format('U');

        if ($firstDateTimeUnixFormat - $secondDateTimeUnixFormat < 0) {

            return false;
        }

        return true;
    }
}