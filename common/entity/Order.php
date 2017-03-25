<?php

namespace common\entity;

use Yii;

/**
 * This is the model class for table "orders".
 *
 * @property integer $id
 * @property string $date
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders';
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
            [['id', 'date'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
        ];
    }
}
