<?php
namespace app\modules\text\models;

use app\modules\admin\components\behaviors\SettingModel;
use app\validators\JsonValidator;
use dosamigos\translateable\TranslateableBehavior;
use himiklab\sortablegrid\SortableGridBehavior;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class Text
 * @package app\modules\text\models
 *
 * @property $published_at;
 * @property $created_at;
 * @property $updated_at;
 * @property $status;
 * @property $order;
 * @property $settings;
 *
 * @mixin SettingModel Settings Model
 */
class Text extends ActiveRecord
{
    const CACHE_KEY = 'oakcms_text';

    const STATUS_PUBLISHED = 1;
    const STATUS_DRAFT = 0;

    public $output = '';

    public static function tableName()
    {
        return 'texts';
    }

    public static function getWereToPlace() {
        return [
            '0' => Yii::t('text', 'On all pages'),
            '-' => Yii::t('text', 'Not on the same page'),
            '1' => Yii::t('text', 'On these pages only'),
            '-1' => Yii::t('text', 'On all pages, except for the above'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class' => SettingModel::className(),
                'settingsField' => 'settings',
                'module' => false
            ],
            [
                'class' => SluggableBehavior::className(),
                'attribute' => 'title',
                'slugAttribute' => 'slug',
            ],
            'trans' => [
                'class' => TranslateableBehavior::className(),
                'translationAttributes' => [
                    'title', 'subtitle', 'text', 'settings'
                ]
            ],
            'sortable' => [
                'class' => \kotchuprik\sortable\behaviors\Sortable::className(),
                'query' => self::find(),
            ],
        ];
    }

    public function getTranslations()
    {
        return $this->hasMany(TextsLang::className(), ['texts_id' => 'id']);
    }

    public function rules()
    {
        return [
            [['id', 'order'], 'number', 'integerOnly' => true],
            [['text', 'title'], 'required'],
            [['title', 'subtitle', 'layout', 'links'], 'string'],
            ['text', 'trim'],
            [['slug', 'where_to_place'], 'string', 'max' => 150],

            ['published_at', 'filter', 'filter' => 'strtotime', 'skipOnEmpty' => true],
            ['published_at', 'default', 'value' => time()],

            //[['slug'], 'unique'],
            ['slug', 'default', 'value' => null],
            //[['settings'], JsonValidator::className()],
            [['settings'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'text' => Yii::t('text', 'Content'),
            'slug' => Yii::t('text', 'Position'),
            'title' => Yii::t('text', 'Title'),
            'subtitle' => Yii::t('text', 'Subtitle'),
            'layout' => Yii::t('text', 'Layout'),
            'links' => Yii::t('text', 'Links'),
            'where_to_place' => Yii::t('text', 'Where To Place'),
        ];
    }

    public function beforeValidate()
    {
        if(is_array($this->links))
            $this->links = implode(",", $this->links);

        return parent::beforeValidate();
    }

    public function afterFind()
    {
        $this->links = explode(",", $this->links);
        parent::afterFind();
    }

    public function afterDelete()
    {
        parent::afterDelete(); // TODO: Change the autogenerated stub

        foreach ($this->getTranslations()->all() as $translation) {
            $translation->delete();
        }
    }
}
