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

        return $platesCommand->queryAll();
    }
}