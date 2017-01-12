<?php

namespace app\modules\content\models;

use Yii;
use app\components\ActiveQuery;
use app\components\ActiveRecord;
use app\modules\admin\components\behaviors\SettingModel;
use dosamigos\translateable\TranslateableBehavior;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%content_pages}}".
 *
 * @property integer $id
 * @property string $layout
 * @property string $title
 * @property string $slug
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class ContentPages extends ActiveRecord
{
    const STATUS_PUBLISHED = 1;
    const STATUS_DRAFT = 0;

    public function fields()
    {
        return [
            'id',
            'title' => function($model) {
                return $model->title;
            },
            'slug' => function($model) {
                return $model->slug;
            },
            'content' => function($model) {
                return $model->content;
            },
            'description' => function($model) {
                return $model->description;
            },
            'meta_title' => function($model) {
                return $model->meta_title;
            },
            'meta_keywords' => function($model) {
                return $model->meta_keywords;
            },
            'meta_description' => function($model) {
                return $model->meta_description;
            },
            'settings' => function($model) {
                return $model->settings;
            }
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            SettingModel::className(),
            [
                'class' => SluggableBehavior::className(),
                'attribute' => 'title',
                'slugAttribute' => 'slug',
            ],
            [
                'class'                 => \mongosoft\file\UploadImageBehavior::className(),
                'attribute'             => 'background_image',
                'scenarios'             => ['insert', 'update'],
                'placeholder'           => '@webroot/uploads/user/non_image.png',
                'createThumbsOnSave'    => true,
                'path'                  => '@webroot/uploads/background_image/category_{id}',
                'url'                   => '@web/uploads/background_image/category_{id}',
            ],
            'trans' => [
                'class' => TranslateableBehavior::className(),
                'translationAttributes' => [
                    'slug', 'title', 'content', 'description', 'meta_title', 'meta_keywords', 'meta_description', 'settings'
                ]
            ],
        ];
    }

    public function getTranslations()
    {
        return $this->hasMany(ContentPagesLang::className(), ['content_pages_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%content_pages}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content', 'status'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['meta_title', 'meta_keywords', 'meta_description', 'layout'], 'string', 'max' => 255],
            [['title', 'description', 'settings'], 'string'],
            [['slug'], 'string', 'max' => 150],
            [
                ['slug'],
                'unique',
                'targetClass' => ContentPagesLang::className(),
                'targetAttribute' => 'slug',
                'filter' => function ($query) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query->andWhere('`content_pages_id` <> :a_id', ['a_id' => $this->id]);
                    return $query;
                }
            ],
            [['background_image'], 'image', 'extensions' => 'jpg, jpeg, gif, png, JPG', 'on' => ['insert', 'update']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('content', 'ID'),
            'layout' => Yii::t('content', 'Layout'),
            'status' => Yii::t('content', 'Status'),
            'created_at' => Yii::t('content', 'Created At'),
            'updated_at' => Yii::t('content', 'Updated At'),
        ];
    }

    /**
     * @return array
     */
    public static function statusLabels($status = false) {
        $statuses = [
            self::STATUS_PUBLISHED => Yii::t('admin', 'Published'),
            self::STATUS_DRAFT => Yii::t('admin', 'Unpublished'),
        ];
        if($status !== false) {
            return $statuses[$status];
        } else {
            return $statuses;
        }
    }

    public function getStatusLabel() {
        return self::statusLabels($this->status);
    }

    /**
     * @inheritdoc
     */
    public function getFrontendViewLink()
    {
        return ['content/page/view', 'slug' => $this->slug];
    }

    /**
     * @inheritdoc
     */
    public static function frontendViewLink($model)
    {
        return ['content/page/view', 'slug' => $model['slug']];
    }

    /**
     * @inheritdoc
     */
    public function getBackendViewLink()
    {
        return ['/admin/content/page/view', 'id' => $this->id];
    }

    /**
     * @inheritdoc
     */
    public static function backendViewLink($model)
    {
        return ['/admin/content/page/view', 'id' => $model['id']];
    }

    public function afterDelete()
    {
        parent::afterDelete(); // TODO: Change the autogenerated stub

        // Видалення перекладу
        foreach ($this->getTranslations()->all() as $translations) {
            $translations->delete();
        }
    }
}
