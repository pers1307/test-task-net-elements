<?php

namespace common\entity;

use Yii;

/**
 * This is the model class for table "order_items".
 *
 * @property integer $id
 * @property integer $order_id
 * @property string $date
 * @property integer $plate_id
 * @property integer $qty
 * @property double $tax
 * @property double $price
 */
class OrderItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_items';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dataBase');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'order_id', 'date', 'plate_id', 'qty'], 'integer'],
            [['tax', 'price'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'date' => 'Date',
            'plate_id' => 'Plate ID',
            'qty' => 'Qty',
            'tax' => 'Tax',
            'price' => 'Price',
        ];
    }
}
