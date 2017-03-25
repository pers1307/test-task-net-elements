<?php

namespace frontend\services\generator\orders;

/**
 * Class TimeBetweenOrders
 * @package frontend\services
 */
class TimeBetweenOrders
{
    /**
     * @var int
     */
    protected $bottomLine;

    /**
     * @var int
     */
    protected $upperLine;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var int
     */
    protected $countCycles = 0;

    /**
     * @var int
     */
    protected $countGenerateSeconds = 0;

    /**
     * @var int
     */
    protected $lastGenerateSeconds = 0;

    /**
     * @param array $config
     */
    function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param int $bottomLine
     */
    public function setBottomLine($bottomLine)
    {
        $this->bottomLine = $bottomLine;
    }

    /**
     * @param int $upperLine
     */
    public function setUpperLine($upperLine)
    {
        $this->upperLine = $upperLine;
    }

    /**
     * @return int
     */
    public function getSecond()
    {
        if ($this->config['type'] == 'morning') {

            if ($this->countCycles == 0) {

                $this->lastGenerateSeconds = $this->bottomLine + mt_rand(0, 3000);

                ++$this->countCycles;
            } else {

                /**
                 * Случайное значение между доставками более определенного значения
                 */
                $randSecondInterval = mt_rand(3000, 4200);

                $this->lastGenerateSeconds += $randSecondInterval;

                /**
                 * Проверка, что число не выходит за пределы диапазона
                 */
                if ($this->lastGenerateSeconds > $this->upperLine) {

                    $this->lastGenerateSeconds = $this->bottomLine + mt_rand(0, 3000);

                    ++$this->countCycles;
                }
            }
        } else {

            if ($this->countCycles == 0) {

                $this->lastGenerateSeconds = $this->bottomLine + mt_rand(3000, 4200);

                ++$this->countCycles;
            } else {

                /**
                 * Случайное значение между доставками более определенного значения
                 */
                $randSecondInterval = mt_rand(3000, 4200);

                $this->lastGenerateSeconds += $randSecondInterval;

                /**
                 * Проверка, что число не выходит за пределы диапазона
                 */
                if ($this->lastGenerateSeconds > $this->upperLine) {

                    $this->lastGenerateSeconds = $this->bottomLine + mt_rand(3000, 4200);

                    ++$this->countCycles;
                }
            }
        }

        return $this->lastGenerateSeconds;
    }
}