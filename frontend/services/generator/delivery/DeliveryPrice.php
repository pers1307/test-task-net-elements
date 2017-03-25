<?php

namespace frontend\services\generator\delivery;

use common\entity\generator\Order;
use common\entity\generator\PositionInOrder;
use common\entity\generator\Product as ProductGenerator;
use common\entity\storage\Project;
use common\repository\storage\ProjectRepository;
use yii\db\Connection;

/**
 * Class DeliveryPrice
 *
 * @package frontend\services
 */
class DeliveryPrice
{
    /**
     * @var float
     */
    private $deliveryPrice;

    /**
     * @var int
     */
    private $idDelivery;

    /**
     * @var float
     */
    private $priceLimit;

    /**
     * @var int
     */
    private $projectId;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param float $price
     */
    public function setDeliveryPrice($price)
    {
        $this->deliveryPrice = $price;
    }

    /**
     * @param float $priceLimit
     */
    public function setPriceLimit($priceLimit)
    {
        $this->priceLimit = $priceLimit;
    }

    /**
     * @param int $projectId
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
    }

    public function add()
    {
        $this->createConnection();

        $this->connection->open();

        $this->addDeliveryPosition();

        $this->addDeliveryPositionInOrder();

        $this->connection->close();
    }

    private function createConnection()
    {
        $projectRepository = new ProjectRepository();

        /** @var Project $project */
        $project = $projectRepository->find($this->projectId);

        /** @var Connection $opencartConnection */
        $this->connection = new Connection([
            'dsn'         => 'mysql:host=' . $project->opencart_host . ';dbname=' . $project->opencart_dbname,
            'username'    => $project->opencart_username,
            'password'    => $project->opencart_password,
            'charset'     => $project->opencart_charset,
            'tablePrefix' => $project->opencart_tablePrefix,
        ]);
    }

    private function addDeliveryPosition()
    {
        /**
         * Попытка найти доставку
         */
        $deliveryProducts = $this->connection->createCommand('
                  SELECT *
                  FROM {{%product}}
                  WHERE `model` = :model
                ');

        $deliveryProducts->bindValue(':model', 'Доставка');

        $deliveryProducts = $deliveryProducts->query();

        if (!empty($deliveryProducts)) {

            foreach ($deliveryProducts as $item) {

                if ($item['price'] == $this->deliveryPrice) {

                    $deliveryProduct = $item;
                }
            }

            if (empty($deliveryProduct)) {

                $deliveryProduct = $this->createDeliveryProductInOpenCart();
            }
        } else {

            $deliveryProduct = $this->createDeliveryProductInOpenCart();
        }

        /**
         * Проверяем существование такой позиции
         */
        $deliveryProductInGenerator = ProductGenerator::findOne(
            [
                'name'    => 'Доставка',
                'id_shop' => $deliveryProduct['product_id']
            ]
        );

        /**
         * Переносим позицию к себе в генератор
         */
        if (empty($deliveryProductInGenerator)) {

            $deliveryProductInGenerator = new ProductGenerator();

            $deliveryProductInGenerator->id_shop        = $deliveryProduct['product_id'];
            $deliveryProductInGenerator->name           = 'Доставка';
            $deliveryProductInGenerator->price_shop     = $this->deliveryPrice;
            $deliveryProductInGenerator->price_purchase = $this->deliveryPrice;
            $deliveryProductInGenerator->url            = '';

            $deliveryProductInGenerator->save();
        }

        $this->idDelivery = $deliveryProductInGenerator->id;
    }

    private function addDeliveryPositionInOrder()
    {
        /**
         * Проходим и добавляем к заказам доставку
         */
        $orders = Order::find()->all();

        foreach($orders as $order) {

            if ($order->total < $this->priceLimit) {

                $position = new PositionInOrder();

                $position->id_product = $this->idDelivery;
                $position->count      = 1;
                $position->id_order   = $order->id;

                $position->save();

                unset($position);

                $order->total += $this->deliveryPrice;

                $order->save();
            }
        }
    }

    private function createDeliveryProductInOpenCart()
    {
        /**
         * Создаем позицию доставки в OpenCart
         */
        $deliveryProduct = $this->connection->createCommand('
                  INSERT {{%product}}
                  (
                      `model`,
                      `sku`,
                      `upc`,
                      `ean`,
                      `jan`,
                      `isbn`,
                      `mpn`,
                      `quantity`,
                      `stock_status_id`,
                      `image`,
                      `manufacturer_id`,
                      `shipping`,
                      `price`,
                      `points`,
                      `tax_class_id`,
                      `date_available`,
                      `weight`,
                      `weight_class_id`,
                      `length`,
                      `width`,
                      `height`,
                      `length_class_id`,
                      `subtract`,
                      `minimum`,
                      `sort_order`,
                      `status`,
                      `viewed`,
                      `date_added`,
                      `date_modified`
                  )
                  VALUES
                  (
                      :model,
                      :sku,
                      :upc,
                      :ean,
                      :jan,
                      :isbn,
                      :mpn,
                      :quantity,
                      :stock_status_id,
                      :image,
                      :manufacturer_id,
                      :shipping,
                      :price,
                      :points,
                      :tax_class_id,
                      :date_available,
                      :weight,
                      :weight_class_id,
                      :length,
                      :width,
                      :height,
                      :length_class_id,
                      :subtract,
                      :minimum,
                      :sort_order,
                      :status,
                      :viewed,
                      :date_added,
                      :date_modified
                  )
                ');

        $deliveryProduct->bindValue(':model', 'Доставка');
        $deliveryProduct->bindValue(':sku', '');
        $deliveryProduct->bindValue(':upc', '');
        $deliveryProduct->bindValue(':ean', '');
        $deliveryProduct->bindValue(':jan', '');
        $deliveryProduct->bindValue(':isbn', '');
        $deliveryProduct->bindValue(':mpn', '');
        $deliveryProduct->bindValue(':quantity', 1);
        $deliveryProduct->bindValue(':stock_status_id', 7);
        $deliveryProduct->bindValue(':image', '');
        $deliveryProduct->bindValue(':manufacturer_id', 0);
        $deliveryProduct->bindValue(':shipping', 1);
        $deliveryProduct->bindValue(':price', $this->deliveryPrice);
        $deliveryProduct->bindValue(':points', 0);
        $deliveryProduct->bindValue(':tax_class_id', 0);
        $deliveryProduct->bindValue(':date_available', 0);
        $deliveryProduct->bindValue(':weight', 0);
        $deliveryProduct->bindValue(':weight_class_id', 0);
        $deliveryProduct->bindValue(':length', 0);
        $deliveryProduct->bindValue(':width', 0);
        $deliveryProduct->bindValue(':height', 0);
        $deliveryProduct->bindValue(':length_class_id', 0);
        $deliveryProduct->bindValue(':subtract', 1);
        $deliveryProduct->bindValue(':minimum', 1);
        $deliveryProduct->bindValue(':sort_order', 1);
        $deliveryProduct->bindValue(':status', 1);
        $deliveryProduct->bindValue(':viewed', 0);
        $deliveryProduct->bindValue(':date_added', 0);
        $deliveryProduct->bindValue(':date_modified', 0);

        $deliveryProduct->execute();

        $descriptionProduct = $this->connection->createCommand('
                  INSERT {{%product_description}}
                  (
                      `product_id`,
                      `language_id`,
                      `name`,
                      `description`
                  )
                  VALUES
                  (
                      :product_id,
                      :language_id,
                      :name,
                      :description
                  )
                ');

        $productId = $this->connection->getLastInsertID();

        $descriptionProduct->bindValue(':product_id', $productId);
        $descriptionProduct->bindValue(':language_id', 1);
        $descriptionProduct->bindValue(':name', 'Доставка');
        $descriptionProduct->bindValue(':description', '');

        $descriptionProduct->execute();

        $product = $this->connection->createCommand('
                  SELECT *
                  FROM {{%product}}
                  WHERE `product_id` = :product_id
                ');

        $product->bindValue(':product_id', $productId);

        return $product->queryOne();
    }
}