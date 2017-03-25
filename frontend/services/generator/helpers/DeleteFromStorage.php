<?php

namespace frontend\services\generator\helpers;

use common\repository\generator\CallRepository;
use common\repository\generator\ClientRepository;
use common\repository\generator\DeliveryRepository;
use common\repository\generator\OrderRepository;
use common\repository\generator\PositionInOrderRepository;
use common\repository\generator\ProductRepository;
use common\repository\storage\CallStorageRepository;
use common\repository\storage\ClientStorageRepository;
use common\repository\storage\DeliveryStorageRepository;
use common\repository\storage\OrderStorageRepository;
use common\repository\storage\PositionInOrderStorageRepository;
use common\repository\storage\ProductStorageRepository;

/**
 * Class Select
 * Модель выборки из базы по параметрам
 * @package frontend\models
 */
class DeleteFromStorage
{
    public function execute()
    {
        $this->delivery();

        $this->call();

        $this->positionInOrder();

        $this->orders();

//        $this->client();

//        $this->product();
    }

    private function delivery()
    {
        $deliveryRepository = new DeliveryRepository();

        $allIdDeliveryStorage = $deliveryRepository->getAllIdDeliveryStorage();

        $deliveryStorageRepository = new DeliveryStorageRepository();

        $deliveryStorageRepository->deleteId($allIdDeliveryStorage);

        $deliveryRepository->deleteAllStorageId();
    }

    private function call()
    {
        $callRepository = new CallRepository();

        $allIdCallStorage = $callRepository->getAllIdCallStorage();

        $callStorageRepository = new CallStorageRepository();

        $callStorageRepository->deleteId($allIdCallStorage);

        $callRepository->deleteAllStorageId();
    }

    private function positionInOrder()
    {
        $positionInOrderRepository = new PositionInOrderRepository();

        $allIdPositionInOrderStorage = $positionInOrderRepository->getAllIdCallStorage();

        $positionInOrderStorageRepository = new PositionInOrderStorageRepository();

        $positionInOrderStorageRepository->deleteId($allIdPositionInOrderStorage);

        $positionInOrderRepository->deleteAllStorageId();
    }

    private function orders()
    {
        $orderRepository = new OrderRepository();

        $allIdOrderStorage = $orderRepository->getAllIdOrderStorage();

        $orderStorageRepository = new OrderStorageRepository();

        $orderStorageRepository->deleteId($allIdOrderStorage);

        $orderRepository->deleteAllStorageId();
    }

    private function client()
    {
        $clientRepository = new ClientRepository();

        $allIdClientStorage = $clientRepository->getAllIdStorage();

        $clientStorageRepository = new ClientStorageRepository();

        $clientStorageRepository->deleteId($allIdClientStorage);

        $clientRepository->deleteAllStorageId();
    }

    private function product()
    {
        $productRepository = new ProductRepository();

        $allIdProductStorage = $productRepository->getAllIdStorage();

        $productStorageRepository = new ProductStorageRepository();

        $productStorageRepository->deleteId($allIdProductStorage);

        $productRepository->deleteAllStorageId();
    }
}