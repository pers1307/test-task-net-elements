<?php

namespace frontend\services\generator\orders;
use common\entity\generator\Order;
use common\entity\generator\SalesDate;

/**
 * Class Select
 * @package frontend\models
 */
class SelectRangeDate
{
    /**
     * @var string
     */
    public $startDate;

    /**
     * @var string
     */
    public $endDate;

    /**
     * @var string
     */
    public $withoutWeekend;

    /**
     * @param string $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @param string $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @param int $withoutWeekend
     */
    public function setWithoutWeekend($withoutWeekend)
    {
        $this->withoutWeekend = $withoutWeekend;
    }

    public function generate()
    {
        $this->resetSalesDate();

        $dateStart = new \DateTime($this->startDate);
        $dateEnd   = new \DateTime($this->endDate);

        $resultCompare = $dateStart->diff($dateEnd);

        $interval = new \DateInterval('P1D');

        while ($resultCompare->d >= 0 && $resultCompare->invert != 1) {

            if ($this->withoutWeekend == 1) {

                $dayOfWeek = $dateStart->format('l');

                if ($dayOfWeek == 'Sunday' || $dayOfWeek == 'Saturday') {
                    $dateStart->add($interval);
                    $resultCompare = $dateStart->diff($dateEnd);
                    continue;
                }
            }

            $saleDate = new SalesDate();

            $format = $dateStart->format('Y-m-d H:i:s');

            $saleDate->date = $format;
            $saleDate->save();

            $dateStart->add($interval);
            $resultCompare = $dateStart->diff($dateEnd);
        }

        /**
         * Проверка корректности дальнейшего распределения
         */

        $countSalesDate = SalesDate::find()->count();
        $countOrders    = Order::find()->count();

        if ($countSalesDate > $countOrders) {

            SalesDate::deleteAll();

            return 'Ошибка! Количество дней не должно превышать количество заказов! Количество заказов : ' . $countOrders
                . ', количесво дней : ' . $countSalesDate . '.';
        }

        return '';
    }

    private function resetSalesDate()
    {
        SalesDate::deleteAll();
    }
}