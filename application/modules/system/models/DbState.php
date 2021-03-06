<?php
/**
 * @package    oakcms
 * @author     Hryvinskyi Volodymyr <script@email.ua>
 * @copyright  Copyright (c) 2015 - 2017. Hryvinskyi Volodymyr
 * @version    0.0.1-alpha.0.5
 */

namespace app\modules\system\models;

use Yii;
use yii\base\Event;
use yii\behaviors\TimestampBehavior;
use yii\caching\ChainedDependency;
use yii\caching\ExpressionDependency;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class DbState extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%system_db_state}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'timestamp'], 'required'],
            [['timestamp'], 'integer'],
            [['id'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('system', 'ID'),
            'timestamp' => Yii::t('system', 'Timestamp'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => 'timestamp',
                    self::EVENT_BEFORE_UPDATE => 'timestamp',
                ]
            ]
        ];
    }

    /**
     * Возвращает время последненго изменения в таблице $table
     * @param $table string table name
     * @return mixed
     */
    public static function timestamp($table)
    {
        return ArrayHelper::getValue(self::getData(), self::getDb()->getSchema()->getRawTableName($table), ['timestamp' => 0])['timestamp'];
    }

    /**
     * @param $table string|array table name
     * @return ExpressionDependency
     */
    public static function dependency($table)
    {
        if (is_array($table)) {
            $dependencies = [];

            foreach ($table as $t) {
                $dependencies[] = new ExpressionDependency(['expression' => self::className().'::timestamp("'.$t.'")']);
            }

            return new ChainedDependency([
                'dependencies' => $dependencies
            ]);
        }

        return new ExpressionDependency(['expression' => self::className().'::timestamp("'.$table.'")']);
    }

    private static $data;

    private static function getData()
    {
        if(!isset(self::$data)) {
            self::$data = self::getDb()->noCache(function(){
                    return self::find()->indexBy('id')->asArray()->all();
                });
        }

        return self::$data;
    }

    private static function clearData()
    {
        self::$data = null;
    }

    /**
     * Вешаем на глобальные события изменения БД прослушку.
     */
    public static function bootstrap()
    {
        static $initialized;
        if (!isset($initialized)) {
            $initialized = true;
            Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_DELETE, [self::className(), 'updateState']);
            Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_INSERT, [self::className(), 'updateState']);
            Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_UPDATE, [self::className(), 'updateState']);
        }
    }

    /**
     * @param $value Event|ActiveRecord|string
     */
    public static function updateState($value)
    {
        self::clearData();

        if ($value instanceof Event) {
            $tableName = $value->sender->tableName();
        } elseif ($value instanceof ActiveRecord) {
            $tableName = $value->tableName();
        } else {
            $tableName = $value;
        }

        self::getDb()->createCommand("INSERT INTO ".self::tableName()." (id, timestamp) VALUES (:id, :timestamp) ON DUPLICATE KEY UPDATE [[timestamp]]=:timestamp",
            [
                ':id' => self::getDb()->getSchema()->getRawTableName($tableName),
                ':timestamp' => time()
            ]
        )->execute();
    }
}
