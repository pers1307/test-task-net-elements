<?php

namespace frontend\services\generator\calls;

use common\entity\generator\Call;
use common\entity\generator\Client;
use common\entity\generator\MakesOrdersDate;

/**
 * Class DataBaseCleaner
 * @package frontend\services
 */
class AddCall
{
    /**
     * @var int
     */
    private $countMissed;

    /**
     * @var array
     */
    private $dayMapping = [];

    /**
     * @var array
     */
    private $dayMapWithFreeCall = [];

    /**
     * @var int
     */
    private $idClientTopLimit;

    /**
     * @var int
     */
    private $idClientBottomLimit;

    /**
     * @param int $countMissed
     */
    public function setCountMissed($countMissed)
    {
        $this->countMissed = $countMissed;
    }

    public function add()
    {
        $this->doMap();
        $this->doMapWithFreeCall();
        $this->addCancelCall();
        $this->addLostCall();
    }

    /**
     * @return array
     */
    public function getStatistic()
    {
        $dates = Call::find()
            ->select(['DATE(datetime) as date', 'COUNT(*) as count'])
            ->groupBy('DATE(datetime)')
            ->orderBy('DATE(datetime) ASC')
            ->asArray()
            ->all();

        $result = [];

        foreach ($dates as $date) {

            $result[$date['date']] = $date['count'];
        }

        return $result;
    }

    private function doMap()
    {
        $dates = Call::find()
            ->select(['DATE(datetime) as date'])
            ->groupBy('DATE(datetime)')
            ->orderBy('DATE(datetime)')
            ->asArray()
            ->all();

        foreach ($dates as $date) {

            /**
             * Проверка на день недели
             */

            $dateClass = new \DateTime($date['date']);

            $numberDateOf = $dateClass->format('N');

            if ($numberDateOf == 6 || $numberDateOf == 7) {

                $this->dayMapping[$date['date']] = mt_rand(2, 5);
            } else {

                $this->dayMapping[$date['date']] = mt_rand(5, 15);
            }
        }
    }

    private function doMapWithFreeCall()
    {
        $dates = Call::find()
            ->select(['DATE(datetime) as date', 'COUNT(*) as count'])
            ->groupBy('DATE(datetime)')
            ->orderBy('DATE(datetime)')
            ->asArray()
            ->all();

        foreach ($dates as $date) {

            if ($this->dayMapping[$date['date']] > $date['count']) {

                $this->dayMapWithFreeCall[$date['date']] = $this->dayMapping[$date['date']] - $date['count'];
            }
        }
    }

    private function addCancelCall()
    {
        $this->findLimitIdClient();

        for ($index = 0; $index < $this->countMissed; $index++) {

            if (empty($this->dayMapWithFreeCall)) {

                break;
            }

            /**
             * Выбрать случайный день
             */
            $day = $this->getRandDayWithFreeCalls();

            /** @var MakesOrdersDate $makeOrderDate */
            $makeOrderDate = MakesOrdersDate::findOne(['DATE(date)' => $day]);

            /** @var Client $client */
            $client = Client::findOne(['id' => $this->getRandomIdClient()]);

            $call = new Call();

            $formatPhone = str_replace('-', '', $client->phone);
            $formatPhone = str_replace('+', '', $formatPhone);
            $formatPhone = str_replace('(', '', $formatPhone);
            $formatPhone = str_replace(')', '', $formatPhone);
            $formatPhone = str_replace(' ', '', $formatPhone);

            $call->datetime            = $makeOrderDate->date;
            $call->format_phone        = $formatPhone;
            $call->id_client           = $client->id;
            $call->id_makes_order_date = $makeOrderDate->id;
            $call->lost                = 0;
            $call->missed              = 1;

            $call->save();

            $this->dayMapWithFreeCall[$day] = $this->dayMapWithFreeCall[$day] - 1;

            if ($this->dayMapWithFreeCall[$day] <= 0) {

                unset($this->dayMapWithFreeCall[$day]);
            }
        }
    }

    private function addLostCall()
    {
        $this->findLimitIdClient();

        /**
         * У свободных дней продублируем звонки
         */
        foreach ($this->dayMapWithFreeCall as $day => $countFreeCall) {

            /** @var MakesOrdersDate $makeOrderDate */
            $makeOrderDate = MakesOrdersDate::findOne(['DATE(date)' => $day]);

            /**
             * Определим случайно делать дубли или нет
             */
            $haveDouble  = mt_rand(0, 1);
            $countDouble = mt_rand(1, 2);

            for ($index = 0; $index < $countFreeCall; ++$index) {

                if ($haveDouble == 1) {

                    if ($countDouble > 0) {

                        $callsInThatDay = Call::find()
                            ->where('DATE(datetime) = "' . $day . '" AND missed = 0')
                            ->all();

                        $countCallsInThatDay = count($callsInThatDay);

                        $randCall = mt_rand(0, $countCallsInThatDay - 1);

                        /** @var Call $newCall */
                        $newCall = new Call();

                        $newCall->datetime            = $callsInThatDay[$randCall]->datetime;
                        $newCall->format_phone        = $callsInThatDay[$randCall]->format_phone;
                        $newCall->id_client           = $callsInThatDay[$randCall]->id_client;
                        $newCall->id_makes_order_date = $callsInThatDay[$randCall]->id_makes_order_date;
                        $newCall->lost                = 1;
                        $newCall->missed              = $callsInThatDay[$randCall]->missed;

                        $newCall->save();

                        --$countDouble;
                    } else {

                        --$haveDouble;

                        /**
                         * Тут пока не смог придумать
                         * как оптимизировать код
                         */

                        /** @var Client $client */
                        $client = Client::findOne(['id' => $this->getRandomIdClient()]);

                        $call = new Call();

                        $formatPhone = str_replace('-', '', $client->phone);
                        $formatPhone = str_replace('+', '', $formatPhone);
                        $formatPhone = str_replace('(', '', $formatPhone);
                        $formatPhone = str_replace(')', '', $formatPhone);
                        $formatPhone = str_replace(' ', '', $formatPhone);

                        $call->datetime            = $makeOrderDate->date;
                        $call->format_phone        = $formatPhone;
                        $call->id_client           = $client->id;
                        $call->id_makes_order_date = $makeOrderDate->id;
                        $call->lost                = 1;
                        $call->missed              = 0;

                        $call->save();
                    }
                } else {

                    /** @var Client $client */
                    $client = Client::findOne(['id' => $this->getRandomIdClient()]);

                    $call = new Call();

                    $formatPhone = str_replace('-', '', $client->phone);
                    $formatPhone = str_replace('+', '', $formatPhone);
                    $formatPhone = str_replace('(', '', $formatPhone);
                    $formatPhone = str_replace(')', '', $formatPhone);
                    $formatPhone = str_replace(' ', '', $formatPhone);

                    $call->datetime            = $makeOrderDate->date;
                    $call->format_phone        = $formatPhone;
                    $call->id_client           = $client->id;
                    $call->id_makes_order_date = $makeOrderDate->id;
                    $call->lost                = 1;
                    $call->missed              = 0;

                    $call->save();
                }

                $this->dayMapWithFreeCall[$day] = $this->dayMapWithFreeCall[$day] - 1;

                if ($this->dayMapWithFreeCall[$day] <= 0) {

                    unset($this->dayMapWithFreeCall[$day]);
                }
            }
        }
    }

    /**
     * @return string
     */
    private function getRandDayWithFreeCalls()
    {
        $countFreeDays = count($this->dayMapWithFreeCall);

        $randDay = mt_rand(0, $countFreeDays - 1);

        return $this->getDayWithFreeCallsByIndex($randDay);
    }

    /**
     * @param int $index
     *
     * @return string
     */
    private function getDayWithFreeCallsByIndex($index)
    {
        $arrayWithFreeDate = [];

        foreach ($this->dayMapWithFreeCall as $date => $countFreeDay) {

            $arrayWithFreeDate[] = $date;
        }

        return $arrayWithFreeDate[$index];
    }

    /**
     * @return int
     */
    public function getRandomIdClient()
    {
        return mt_rand($this->idClientBottomLimit, $this->idClientTopLimit);
    }

    private function findLimitIdClient()
    {
        $result = Client::find()
            ->select('MIN(id) as min')
            ->asArray()
            ->one();

        $this->idClientBottomLimit = $result['min'];

        $result = Client::find()
            ->select('MAX(id) as max')
            ->asArray()
            ->one();

        $this->idClientTopLimit = $result['max'];
    }
}