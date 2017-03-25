<?php

namespace frontend\services\generator\products;

/**
 * Class PriceList
 * @package frontend\services
 */
class PriceList
{
    /**
     * @var string
     */
    private $url = '';

    public function setUrl($url)
    {
        $this->url = substr($url, 0, -1);
    }

    public function makeExcelFile()
    {
        $xls = new \PHPExcel();

        $this->makeMainPage($xls);

        /**
         * write file
         */
        $objWriter = new \PHPExcel_Writer_Excel5($xls);
        $objWriter->save('price_list.xls');

        return 'price_list.xls';
    }

    private function makeMainPage(&$PhpExcelClass)
    {
        $table = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT *
                    FROM product
                '
            )
            ->queryAll();

        $PhpExcelClass->setActiveSheetIndex(0);
        // Получаем активный лист
        $sheet = $PhpExcelClass->getActiveSheet();
        // Подписываем лист
        $sheet->setTitle('Прайс лист');

        $sheet->setCellValue("A1", 'Идентификатор в магазине');
        $sheet->getStyle('A1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('A')->setWidth(30);

        $sheet->setCellValue('B1', 'Название товара');
        $sheet->getStyle('B1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('B')->setWidth(50);

        $sheet->setCellValue('C1', 'Цена продажи');
        $sheet->getStyle('C1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('C')->setWidth(25);

        $sheet->setCellValue('D1', 'Цена закупки');
        $sheet->getStyle('D1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('D1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('D')->setWidth(25);

        $sheet->setCellValue('E1', 'Ссылка на товар');
        $sheet->getStyle('E1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('E1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('E')->setWidth(200);

        foreach ($table as $key => $item) {

            $sheet->setCellValueByColumnAndRow(0, $key + 2, $item['id_shop']);
            $sheet->setCellValueByColumnAndRow(1, $key + 2, $item['name']);
            $sheet->setCellValueByColumnAndRow(2, $key + 2, $item['price_shop']);
            $sheet->setCellValueByColumnAndRow(3, $key + 2, $item['price_purchase']);
            $sheet->setCellValueByColumnAndRow(4, $key + 2, $this->url . $item['url']);

            $sheet->getCellByColumnAndRow(4, $key + 2)->getHyperlink()->setUrl($this->url . $item['url']);

            // Применяем выравнивание

            for ($index = 0; $index < 6; ++$index) {

                if ($index != 1 && $index != 4) {

                    $sheet->getStyleByColumnAndRow($index, $key + 2)->getAlignment()->
                    setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }
            }
        }
    }
}