<?php

namespace frontend\services\generator\helpers;

/**
 * Class CountLostCallGenerator
 * @package frontend\services
 */
class CountLostCallGenerator
{
    public function getCountLostCalls()
    {
        return mt_rand(93, 245);
    }

    public function getCountMissedCalls()
    {
        return mt_rand(8, 18);
    }
}