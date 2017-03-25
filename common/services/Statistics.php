<?php
/**
 * Statistics.php
 *
 * Сервис для построения статистики
 *
 * @author      Pereskokov Yurii
 * @copyright   2017 Pereskokov Yurii
 * @link        http://www.mediasite.ru/
 */

namespace common\services;
use KoKoKo\assert\Assert;
use Yii;

/**
 * Class Statistics
 * @package common\services
 */
class Statistics
{
    /**
     * @param string $from
     * @param string $to
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getSalesPlatesByOrderDateTime($from, $to)
    {
        Assert::assert($from, 'from')->notEmpty()->string();
        Assert::assert($to,   'to')->notEmpty()->string();

        /**
         * Тут возникло не понимание, что считать ценой gross sales
         * Это поле plates.price или order_items.price?
         * Оставил оба варианта, нужное раскомментировать.
         */
        $platesCommand = Yii::$app->dataBase->createCommand('
            SELECT 
            plates.id as id,
            plates.`name`,
            SUM(order_items.qty) as amount,
            TRUNCATE(SUM(order_items.price), 2) as gross_sales,
            TRUNCATE(SUM(order_items.tax), 2) as tax,
            TRUNCATE(SUM(order_items.price) - SUM(order_items.tax), 2) as net_sales
            FROM orders
            JOIN order_items ON orders.id = order_items.order_id
            JOIN plates ON order_items.plate_id = plates.id
            WHERE FROM_UNIXTIME(orders.date) BETWEEN :from AND :to
            GROUP BY plates.id
        ');

//        $platesCommand = Yii::$app->dataBase->createCommand('
//            SELECT
//            plates.id as id,
//            plates.`name`,
//            SUM(order_items.qty) as amount,
//            TRUNCATE(SUM(plates.price * order_items.qty), 2) as gross_sales,
//            TRUNCATE(SUM(order_items.tax), 2) as tax,
//            TRUNCATE(SUM(plates.price * order_items.qty) - SUM(order_items.tax), 2) as net_sales
//            FROM orders
//            JOIN order_items ON orders.id = order_items.order_id
//            JOIN plates ON order_items.plate_id = plates.id
//            WHERE FROM_UNIXTIME(orders.date) BETWEEN :from AND :to
//            GROUP BY plates.id
//        ');

        $platesCommand->bindValue(':from', $from);
        $platesCommand->bindValue(':to',   $to);

        $itemsPlate = $platesCommand->queryAll();

        $totalRow = $this->getTotalByItemsPlate($itemsPlate);

        $totalRow['countOrders'] = $this->getCountByOrderDateTime($from, $to);

        return [
            'itemsPlate' => $itemsPlate,
            'totalRow'   => $totalRow,
        ];
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return int
     * @throws \InvalidArgumentException
     */
    public function getCountByOrderDateTime($from, $to)
    {
        Assert::assert($from, 'from')->notEmpty()->string();
        Assert::assert($to,   'to')->notEmpty()->string();

        $platesCommand = Yii::$app->dataBase->createCommand('
            SELECT COUNT(*) as count
            FROM orders
            WHERE FROM_UNIXTIME(orders.date) BETWEEN :from AND :to
        ');

        $platesCommand->bindValue(':from', $from);
        $platesCommand->bindValue(':to',   $to);

        $result = $platesCommand->queryOne();

        return $result['count'];
    }

    /**
     * @param array $itemsPlate
     *
     * @return array
     */
    private function getTotalByItemsPlate($itemsPlate)
    {
        $totalRow = [
            'amount'      => 0,
            'gross_sales' => 0,
            'tax'         => 0,
            'net_sales'   => 0,
        ];

        foreach ($itemsPlate as $itemPlate) {

            $totalRow['amount']      += $itemPlate['amount'];
            $totalRow['gross_sales'] += $itemPlate['gross_sales'];
            $totalRow['tax']         += $itemPlate['tax'];
            $totalRow['net_sales']   += $itemPlate['net_sales'];
        }

        return $totalRow;
    }
}