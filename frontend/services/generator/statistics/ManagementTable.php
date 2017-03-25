<?php

namespace frontend\services\generator\statistics;

/**
 * Class DataBaseCleaner
 * @package frontend\services
 */
class ManagementTable
{
    public function makeExcelFile()
    {
        $xls = new \PHPExcel();

        $this->makeMainPage($xls);
        $this->makeClientPage($xls);
        $this->makeOrderListPage($xls);
        $this->makeShippingDatePage($xls);
        $this->makeOrderListWithSortSaleDate($xls);

        /**
         * write file
         */
        $objWriter = new \PHPExcel_Writer_Excel5($xls);
        $objWriter->save('management_table.xls');

        return 'management_table.xls';
    }

    /**
     * @param \PHPExcel $PhpExcelClass
     */
    private function makeMainPage(&$PhpExcelClass)
    {
        $table = \Yii::$app->generator
            ->createCommand(
                '
                SELECT
                `order`.date_time_makes_order date_time,
                client.first_name,
                client.phone,
                client.email,
                id_order_source,
                order_source.`name`,
                `order`.id,
                `order`.total,
                TRUNCATE(
                    (
                    SELECT SUM(product.price_purchase)
                    FROM position_in_order
                    JOIN product ON position_in_order.id_product = product.id
                    WHERE id_order = `order`.id
                    )
                    ,2
                ) as purchase_price,
                TRUNCATE(
                    `order`.total - (
                    SELECT SUM(product.price_purchase)
                    FROM position_in_order
                    JOIN product ON position_in_order.id_product = product.id
                    WHERE id_order = `order`.id
                    )
                    ,2
                ) as margin,
                TRUNCATE(
                    `order`.total / (
                    SELECT SUM(product.price_purchase)
                    FROM position_in_order
                    JOIN product ON position_in_order.id_product = product.id
                    WHERE id_order = `order`.id
                    )
                    ,2
                ) * 100 - 100 as margin_procent,
                DATE(`order`.date_time_sales) as date_sales,
                client.city,
                TIME(date_time_sales) as time_sales,
                client.address,
                client.`comment`,
                `order`.lost,
                `order`.cancel,
                IF((lost = 0 AND cancel = 0), 1, 0) as real_order
                FROM `order`
                JOIN client ON `order`.id_client = client.id
                JOIN order_source ON `order`.id_order_source = order_source.id
                ORDER BY `order`.date_time_makes_order ASC
            '
            )
            ->queryAll();

        $PhpExcelClass->setActiveSheetIndex(0);
        // Получаем активный лист
        $sheet = $PhpExcelClass->getActiveSheet();
        // Подписываем лист
        $sheet->setTitle('Главная');

        $sheet->setCellValue("A1", 'ДАТА И ВРЕМЯ ЗАКАЗА');
        $sheet->getStyle('A1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('A')->setWidth(30);

        $sheet->setCellValue('B1', 'ИМЯ КЛИЕНТА');
        $sheet->getStyle('B1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('B')->setWidth(20);

        $sheet->setCellValue('C1', 'ТЕЛЕФОН КЛИЕНТА');
        $sheet->getStyle('C1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('C')->setWidth(25);

        $sheet->setCellValue('D1', 'EMAIL КЛИЕНТА');
        $sheet->getStyle('D1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('D1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('D')->setWidth(25);

        $sheet->setCellValue('E1', 'ОТКУДА ЗАКАЗ');
        $sheet->getStyle('E1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('E1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('E')->setWidth(20);

        $sheet->setCellValue('F1', 'ЧТО ЗАКАЗАЛ');
        $sheet->getStyle('F1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('F1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('F')->setWidth(20);

        $sheet->setCellValue('G1', 'ЦЕНА КЛИЕНТА');
        $sheet->getStyle('G1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('G1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('G')->setWidth(20);

        $sheet->setCellValue('H1', 'ЦЕНА ЗАКУПКИ');
        $sheet->getStyle('H1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('H1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('H')->setWidth(20);

        $sheet->setCellValue('I1', 'МАРЖА');
        $sheet->getStyle('I1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('I1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('I')->setWidth(20);

        $sheet->setCellValue('J1', 'МАРЖА В %');
        $sheet->getStyle('J1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('J1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('J')->setWidth(15);

        $sheet->setCellValue('K1', 'ДАТА ДОСТАВКИ/ПРОДАЖИ');
        $sheet->getStyle('K1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('K1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('K')->setWidth(40);

        $sheet->setCellValue('L1', 'ТИП ДОСТАВКИ');
        $sheet->getStyle('L1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('L1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('L')->setWidth(20);

        $sheet->setCellValue('M1', 'ВРЕМЯ ДОСТАВКИ');
        $sheet->getStyle('M1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('M1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('M')->setWidth(20);

        $sheet->setCellValue('N1', 'АДРЕС ДОСТАВКИ');
        $sheet->getStyle('N1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('N1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('N')->setWidth(77);

        $sheet->setCellValue('O1', 'НОМЕР ЗАКАЗА В АДМИНКЕ');
        $sheet->getStyle('O1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('O1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('O')->setWidth(30);

        $sheet->setCellValue('P1', 'ЗАКАЗ ТОЛЬКО ДЛЯ АДМИНКИ');
        $sheet->getStyle('P1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('P1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('P')->setWidth(30);

        $sheet->setCellValue('Q1', 'БУДЕТ КУПЛЕН');
        $sheet->getStyle('Q1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('Q1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('Q')->setWidth(30);

        foreach ($table as $key => $item) {

            $sheet->setCellValueByColumnAndRow(0, $key + 2, $item['date_time']);
            $sheet->setCellValueByColumnAndRow(1, $key + 2, $item['first_name']);
            $sheet->setCellValueByColumnAndRow(2, $key + 2, $item['phone']);
            $sheet->setCellValueByColumnAndRow(3, $key + 2, $item['email']);
            $sheet->setCellValueByColumnAndRow(4, $key + 2, $item['name']);
            $sheet->setCellValueByColumnAndRow(5, $key + 2, $item['id']);
            $sheet->setCellValueByColumnAndRow(6, $key + 2, $item['total']);
            $sheet->setCellValueByColumnAndRow(7, $key + 2, $item['purchase_price']);
            $sheet->setCellValueByColumnAndRow(8, $key + 2, $item['margin']);
            $sheet->setCellValueByColumnAndRow(9, $key + 2, $item['margin_procent']);
            $sheet->setCellValueByColumnAndRow(10, $key + 2, $item['date_sales']);
            $sheet->setCellValueByColumnAndRow(11, $key + 2, $item['city']);
            $sheet->setCellValueByColumnAndRow(12, $key + 2, $item['time_sales']);
            $sheet->setCellValueByColumnAndRow(13, $key + 2, $item['address']);

            if ($item['id_order_source'] != 1) {

                $sheet->setCellValueByColumnAndRow(14, $key + 2, $item['id_order_in_shop']);
            } else {

                $sheet->setCellValueByColumnAndRow(14, $key + 2, '(Телефон)');
            }

            if ($item['lost'] == 1) {

                $sheet->setCellValueByColumnAndRow(15, $key + 2, 'ДА');
            } elseif ($item['cancel'] == 1) {

                $sheet->setCellValueByColumnAndRow(15, $key + 2, 'ОТМЕНЕН');
            } else {

                $sheet->setCellValueByColumnAndRow(15, $key + 2, '');
            }

            if ($item['real_order'] == 1) {

                $sheet->setCellValueByColumnAndRow(16, $key + 2, 'ДА');
            } else {

                $sheet->setCellValueByColumnAndRow(16, $key + 2, '');
            }

            // Применяем выравнивание
            for ($index = 0; $index < 17; ++$index) {

                $sheet->getStyleByColumnAndRow($index, $key + 2)->getAlignment()->
                setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
        }
    }

    /**
     * @param \PHPExcel $PhpExcelClass
     */
    private function makeClientPage(&$PhpExcelClass)
    {
        $table = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT *
                    FROM client
                '
            )
            ->queryAll();

        /** @var \PHPExcel $PhpExcelClass */
        //$PhpExcelClass->setActiveSheetIndex(1);
        $sheet = $PhpExcelClass->createSheet(1);

        // Получаем активный лист
        //$sheet = $PhpExcelClass->getActiveSheet();
        // Подписываем лист
        $sheet->setTitle('ТАБЛИЦА КЛИЕНТОВ');

        $sheet->setCellValue("A1", 'ИМЯ');
        $sheet->getStyle('A1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('A')->setWidth(20);

        $sheet->setCellValue('B1', 'ФАМИЛИЯ');
        $sheet->getStyle('B1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('B')->setWidth(20);

        $sheet->setCellValue('C1', 'АДРЕС');
        $sheet->getStyle('C1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('C')->setWidth(60);

        $sheet->setCellValue('D1', 'ТЕЛЕФОН');
        $sheet->getStyle('D1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('D1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('D')->setWidth(25);

        $sheet->setCellValue('E1', 'EMAIL');
        $sheet->getStyle('E1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('E1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('E')->setWidth(25);

        $sheet->setCellValue('F1', 'ФАКС');
        $sheet->getStyle('F1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('F1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('F')->setWidth(20);

        $sheet->setCellValue('G1', 'IP');
        $sheet->getStyle('G1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('G1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('G')->setWidth(20);

        $sheet->setCellValue('H1', 'ГОРОД');
        $sheet->getStyle('H1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('H1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('H')->setWidth(20);

        $sheet->setCellValue('I1', 'ИНДЕКС');
        $sheet->getStyle('I1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('I1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('I')->setWidth(20);

        $sheet->setCellValue('J1', 'СТРАНА');
        $sheet->getStyle('J1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('J1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('J')->setWidth(30);

        $sheet->setCellValue('K1', 'КОММЕНТАРИЙ К ЗАКАЗУ');
        $sheet->getStyle('K1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('K1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('K')->setWidth(40);

        $sheet->setCellValue('L1', 'БРАУЗЕР ПОЛЬЗОВАТЕЛЯ');
        $sheet->getStyle('L1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('L1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('L')->setWidth(100);

        foreach ($table as $key => $item) {

            $sheet->setCellValueByColumnAndRow(0, $key + 2, $item['first_name']);
            $sheet->setCellValueByColumnAndRow(1, $key + 2, $item['last_name']);
            $sheet->setCellValueByColumnAndRow(2, $key + 2, $item['address']);
            $sheet->setCellValueByColumnAndRow(3, $key + 2, $item['phone']);
            $sheet->setCellValueByColumnAndRow(4, $key + 2, $item['email']);
            $sheet->setCellValueByColumnAndRow(5, $key + 2, $item['fax']);
            $sheet->setCellValueByColumnAndRow(6, $key + 2, $item['ip']);
            $sheet->setCellValueByColumnAndRow(7, $key + 2, $item['city']);
            $sheet->setCellValueByColumnAndRow(8, $key + 2, $item['post_code']);
            $sheet->setCellValueByColumnAndRow(9, $key + 2, $item['country']);
            $sheet->setCellValueByColumnAndRow(10, $key + 2, $item['comment']);
            $sheet->setCellValueByColumnAndRow(11, $key + 2, $item['user_agent']);

            // Применяем выравнивание

            for ($index = 0; $index < 11; ++$index) {

                $sheet->getStyleByColumnAndRow($index, $key + 2)->getAlignment()->
                setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
        }
    }

    /**
     * @param \PHPExcel $PhpExcelClass
     */
    private function makeOrderListPage(&$PhpExcelClass)
    {
        $table = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT *
                    FROM position_in_order
                    JOIN product ON position_in_order.id_product = product.id
                    ORDER BY id_order
                '
            )
            ->queryAll();

        /** @var \PHPExcel $PhpExcelClass */
        $sheet = $PhpExcelClass->createSheet(2);

        $sheet->setTitle('ТАБЛИЦА СОСТАВА ЗАКАЗОВ');

        $sheet->setCellValue("A1", 'НОМЕР ЗАКАЗА');
        $sheet->getStyle('A1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('A')->setWidth(20);

        $sheet->setCellValue('B1', 'НАЗВАНИЕ ПРОДУКТА');
        $sheet->getStyle('B1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('B')->setWidth(60);

        $sheet->setCellValue('C1', 'ЦЕНА');
        $sheet->getStyle('C1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('C')->setWidth(15);

        $sheet->setCellValue('D1', 'КОЛИЧЕСТВО');
        $sheet->getStyle('D1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('D1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('D')->setWidth(20);

        $id_order_now = '';

        foreach ($table as $key => $item) {

            if ($id_order_now != $item['id_order']) {

                $id_order_now = $item['id_order'];

                $sheet->setCellValueByColumnAndRow(0, $key + 2, $id_order_now);
            }

            $sheet->setCellValueByColumnAndRow(1, $key + 2, $item['name']);
            $sheet->setCellValueByColumnAndRow(2, $key + 2, $item['price_shop']);
            $sheet->setCellValueByColumnAndRow(3, $key + 2, $item['count']);

            // Применяем выравнивание

            for ($index = 0; $index < 11; ++$index) {

                $sheet->getStyleByColumnAndRow($index, $key + 2)->getAlignment()->
                setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
        }
    }

    /**
     * @param \PHPExcel $PhpExcelClass
     */
    private function makeShippingDatePage(&$PhpExcelClass)
    {
        $table = \Yii::$app->generator
            ->createCommand(
                '
                    SELECT
                    DATE(date_time_sales) as date
                    FROM `order`
                    GROUP BY DATE(date_time_sales)
                '
            )
            ->queryAll();

        /** @var \PHPExcel $PhpExcelClass */
        $sheet = $PhpExcelClass->createSheet(3);

        $sheet->setTitle('ВОЗМОЖНЫЕ ДАТЫ ДОСТАВОК');

        $sheet->setCellValue("A1", 'ДАТЫ');
        $sheet->getStyle('A1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('A')->setWidth(20);

        foreach ($table as $key => $item) {

            $sheet->setCellValueByColumnAndRow(0, $key + 2, $item['date']);

            for ($index = 0; $index < 1; ++$index) {

                $sheet->getStyleByColumnAndRow($index, $key + 2)->getAlignment()->
                setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
        }
    }

    /**
     * @param \PHPExcel $PhpExcelClass
     */
    private function makeOrderListWithSortSaleDate(&$PhpExcelClass)
    {
        $table = \Yii::$app->generator
            ->createCommand(
                '
                SELECT
                date_time_sales sales_date,
                `order`.date_time_makes_order makes_date_time,
                client.first_name,
                client.phone,
                client.email,
                id_order_source,
                order_source.`name`,
                `order`.id,
                `order`.total,
                TRUNCATE(
                    (
                    SELECT SUM(product.price_purchase)
                    FROM position_in_order
                    JOIN product ON position_in_order.id_product = product.id
                    WHERE id_order = `order`.id
                    )
                    ,2
                ) as purchase_price,
                TRUNCATE(
                    `order`.total - (
                    SELECT SUM(product.price_purchase)
                    FROM position_in_order
                    JOIN product ON position_in_order.id_product = product.id
                    WHERE id_order = `order`.id
                    )
                    ,2
                ) as margin,
                TRUNCATE(
                    `order`.total / (
                    SELECT SUM(product.price_purchase)
                    FROM position_in_order
                    JOIN product ON position_in_order.id_product = product.id
                    WHERE id_order = `order`.id
                    )
                    ,2
                ) * 100 - 100 as margin_procent,
                client.city,
                client.address,
                client.`comment`
                FROM `order`
                JOIN client ON `order`.id_client = client.id
                JOIN order_source ON `order`.id_order_source = order_source.id
                WHERE lost = 0 AND cancel = 0
                ORDER BY `order`.date_time_sales ASC
            '
            )
            ->queryAll();

        /** @var \PHPExcel $PhpExcelClass */
        $sheet = $PhpExcelClass->createSheet(5);

        // Подписываем лист
        $sheet->setTitle('ЗАКАЗЫ, КОТОРЫЕ БУДУТ ПРОДАНЫ');

        $sheet->setCellValue("A1", 'ДАТА И ВРЕМЯ ПРОДАЖИ/ДОСТАВКИ');
        $sheet->getStyle('A1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('A')->setWidth(37);

        $sheet->setCellValue('B1', 'ДАТА И ВРЕМЯ ЗАКАЗА');
        $sheet->getStyle('B1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('B')->setWidth(22);

        $sheet->setCellValue('C1', 'ИМЯ КЛИЕНТА');
        $sheet->getStyle('C1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('C')->setWidth(16);

        $sheet->setCellValue('D1', 'ТЕЛЕФОН КЛИЕНТА');
        $sheet->getStyle('D1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('D1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('D')->setWidth(18);

        $sheet->setCellValue('E1', 'EMAIL КЛИЕНТА');
        $sheet->getStyle('E1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('E1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('E')->setWidth(32);

        $sheet->setCellValue('F1', 'ИСТОЧНИК ЗАКАЗА');
        $sheet->getStyle('F1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('F1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('F')->setWidth(17);

        $sheet->setCellValue('G1', 'ЧТО ЗАКАЗАЛ');
        $sheet->getStyle('G1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('G1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('G')->setWidth(13);

        $sheet->setCellValue('H1', 'ЦЕНА КЛИЕНТА');
        $sheet->getStyle('H1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('H1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('H')->setWidth(14);

        $sheet->setCellValue('I1', 'ЦЕНА ЗАКУПКИ');
        $sheet->getStyle('I1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('I1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('I')->setWidth(14);

        $sheet->setCellValue('J1', 'МАРЖА');
        $sheet->getStyle('J1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('J1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('J')->setWidth(10);

        $sheet->setCellValue('K1', 'МАРЖА В %');
        $sheet->getStyle('K1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('K1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('K')->setWidth(11);

        $sheet->setCellValue('L1', 'ТИП ДОСТАВКИ');
        $sheet->getStyle('L1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('L1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('L')->setWidth(14);

        $sheet->setCellValue('M1', 'АДРЕС ДОСТАВКИ');
        $sheet->getStyle('M1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('M1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('M')->setWidth(76);

        $sheet->setCellValue('N1', 'НОМЕР ЗАКАЗА В АДМИНКЕ');
        $sheet->getStyle('N1')->getFill()->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('N1')->getAlignment()->setHorizontal(
            \PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('N')->setWidth(27);

        foreach ($table as $key => $item) {

            $sheet->setCellValueByColumnAndRow(0, $key + 2, $item['sales_date']);
            $sheet->setCellValueByColumnAndRow(1, $key + 2, $item['makes_date_time']);
            $sheet->setCellValueByColumnAndRow(2, $key + 2, $item['first_name']);
            $sheet->setCellValueByColumnAndRow(3, $key + 2, $item['phone']);
            $sheet->setCellValueByColumnAndRow(4, $key + 2, $item['email']);
            $sheet->setCellValueByColumnAndRow(5, $key + 2, $item['name']);
            $sheet->setCellValueByColumnAndRow(6, $key + 2, $item['id']);
            $sheet->setCellValueByColumnAndRow(7, $key + 2, $item['total']);
            $sheet->setCellValueByColumnAndRow(8, $key + 2, $item['purchase_price']);
            $sheet->setCellValueByColumnAndRow(9, $key + 2, $item['margin']);
            $sheet->setCellValueByColumnAndRow(10, $key + 2, $item['margin_procent']);
            $sheet->setCellValueByColumnAndRow(11, $key + 2, $item['city']);
            $sheet->setCellValueByColumnAndRow(12, $key + 2, $item['address']);

            if ($item['id_order_source'] != 1) {

                $sheet->setCellValueByColumnAndRow(13, $key + 2, $item['id_order_in_shop']);
            } else {

                $sheet->setCellValueByColumnAndRow(13, $key + 2, '(Телефон)');
            }

            // Применяем выравнивание
            for ($index = 0; $index < 14; ++$index) {

                $sheet->getStyleByColumnAndRow($index, $key + 2)->getAlignment()->
                setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
        }
    }
}