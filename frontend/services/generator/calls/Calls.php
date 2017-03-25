<?php

namespace frontend\services\generator\calls;

use common\entity\generator\Call as modelCall;
use common\entity\generator\Order;
use frontend\services\generator\helpers\DataBaseCleaner;

/**
 * Class DataBaseCleaner
 * @package frontend\services
 */
class Calls
{
    /**
     * @var int
     */
    public $minDurationCallPerSecond;

    /**
     * @var int
     */
    public $maxDurationCallPerSecond;

    /**
     * @var string
     */
    public $minTimeCallPerSecond;

    /**
     * @var string
     */
    public $maxTimeCallPerSecond;

    /**
     * @param int $minDurationCallPerSecond
     */
    public function setMinDurationCallPerSecond($minDurationCallPerSecond)
    {
        $this->minDurationCallPerSecond = $minDurationCallPerSecond;
    }

    /**
     * @param int $maxDurationCallPerSecond
     */
    public function setMaxDurationCallPerSecond($maxDurationCallPerSecond)
    {
        $this->maxDurationCallPerSecond = $maxDurationCallPerSecond;
    }

    /**
     * @param int $minTimeCallPerSecond
     */
    public function setMinTimeCallPerSecond($minTimeCallPerSecond)
    {
        $this->minTimeCallPerSecond = $minTimeCallPerSecond;
    }

    /**
     * @param int $maxTimeCallPerSecond
     */
    public function setMaxTimeCallPerSecond($maxTimeCallPerSecond)
    {
        $this->maxTimeCallPerSecond = $maxTimeCallPerSecond;
    }

    public function addCallInMakesOrderDay()
    {
        DataBaseCleaner::callClean();

        $callAndDate = Order::find()
            ->select([
                'makes_orders_date.date',
                'client.phone',
                'client.id client_id',
                'makes_orders_date.id makes_orders_date_id'
            ])
            ->join('JOIN', 'client', '`order`.id_client = client.id')
            ->join('JOIN', 'makes_orders_date', '`order`.id_makes_order_date = makes_orders_date.id')
            ->orderBy('makes_orders_date.date ASC')
            ->asArray()
            ->all();

        foreach ($callAndDate as $item) {

            $call = new modelCall();

            $formatPhone = str_replace('-', '', $item['phone']);
            $formatPhone = str_replace('+', '', $formatPhone);
            $formatPhone = str_replace('(', '', $formatPhone);
            $formatPhone = str_replace(')', '', $formatPhone);
            $formatPhone = str_replace(' ', '', $formatPhone);

            $call->datetime            = $item['date'];
            $call->format_phone        = $formatPhone;
            $call->id_client           = $item['client_id'];
            $call->id_makes_order_date = $item['makes_orders_date_id'];
            $call->lost                = 0;
            $call->missed              = 0;

            $call->save();
        }
    }

    public function generateTimeCalls()
    {
        $calls = modelCall::find()
            ->select(['id', 'datetime'])
            ->asArray()
            ->all();

        $arrayRandSecond = [];

        foreach ($calls as $call) {

            $newDate = new \DateTime($call['datetime']);

            while (true) {

                $randSeconds = $this->getRandTimePerSecond();

                if (!isset($arrayRandSecond[$randSeconds])) {

                    $arrayRandSecond[$randSeconds] = 1;

                    break;
                }
            }

            $interval = new \DateInterval('PT' . $randSeconds . 'S');

            $newDate->add($interval);

            $format = $newDate->format('Y-m-d H:i:s');

            $callForUpdate = modelCall::findOne(['id' => $call['id']]);

            $callForUpdate->datetime = $format;

            $callForUpdate->save();
        }
    }

    public function generateDurationCalls()
    {
        $calls = modelCall::find()
            ->select(['id'])
            ->asArray()
            ->all();

        foreach ($calls as $call) {

            $randSeconds = $this->getRandDurationPerSecond();

            $interval = new \DateInterval('PT' . $randSeconds . 'S');

            $interval = $this->recalculateDateInterval($interval);

            $format = $interval->format('%H:%I:%S');

            $callForUpdate = modelCall::findOne(['id' => $call['id']]);

            if ($callForUpdate->missed != 1) {

                $callForUpdate->duration = $format;
            } else {

                $callForUpdate->duration = '00:00:00';
            }

            $callForUpdate->save();
        }
    }

    private function getRandTimePerSecond()
    {
        return mt_rand($this->minTimeCallPerSecond, $this->maxTimeCallPerSecond);
    }

    private function getRandDurationPerSecond()
    {
        return mt_rand($this->minDurationCallPerSecond, $this->maxDurationCallPerSecond);
    }

    private function recalculateDateInterval($dateInterval)
    {
        $from = new \DateTime();

        $to = clone $from;

        $to   = $to->add($dateInterval);
        $diff = $from->diff($to);

        foreach ($diff as $k => $v) $dateInterval->$k = $v;

        return $dateInterval;
    }
}