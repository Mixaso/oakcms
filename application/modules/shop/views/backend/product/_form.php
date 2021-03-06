<?php
/**
 * @package    oakcms
 * @author     Hryvinskyi Volodymyr <script@email.ua>
 * @copyright  Copyright (c) 2015 - 2017. Hryvinskyi Volodymyr
 * @version    0.0.1-alpha.0.4
 */

/**
 * @var $this yii\web\View
 * @var $model app\modules\content\models\ContentArticles
 * @var $form ActiveForm
 * @var $lang Language
 */

use app\modules\admin\widgets\Button;
use app\modules\gallery\widgets\Gallery;
use app\modules\language\models\Language;
use app\modules\shop\models\Category;
use app\modules\shop\models\Producer;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\bootstrap\Html;
use yii\widgets\ActiveForm;

yii\jui\JuiAsset::register($this);
$asset = \app\templates\backend\base\assets\BaseAsset::register($this);
\app\modules\shop\assets\BackendAsset::register($this);

$selectFilter = '';

foreach ($model->getFilters() as $filter) {
    $selectFilter .= '<div>'.$filter->name.'</div>';
    $selectFilter .=  Html::dropDownList(
        'variants[filter_values][][' . $filter->id . ']',
        null,
        ArrayHelper::map($filter->variants, 'id', 'value'),
        ['class' => 'form-control', 'options' => ['data-available' => true]]
    );
}

$variantTemplate = '
<tr>
    <td>
        <div class="control-group">
            <label class="control-label" style="display: none;">
            <span class="btn btn-small p_r" data-url="file">
                <i class="icon-camera"></i>
                <input type="file" name="image{{id}}" title="Главное изображение">
                <input type="hidden" name="variants[mainImageSlug][]" value="{{mainImageSlug}}" class="mainImageName">
                <input type="hidden" name="variants[id][]" value="{{variantsId}}">
                <input type="hidden" name="changeImage[]" value="" class="changeImage">
            </span>
            </label>
            <div class="controls photo_album photo_album-v" style="width:102px;">
                <div class="fon"></div>
                <div class="btn-group btn-group-xs btn-group-solid f-s_0">
                    <button type="button" class="btn green change_image btn-small" data-rel="tooltip" data-title="' . Yii::t('admin', 'Edit') . '" data-original-title=""><i class="fa fa-edit"></i></button>
                    <button type="button" class="btn red delete_image btn-small" data-rel="tooltip" data-title="' . Yii::t('admin', 'Delete') . '" data-original-title=""><i class="icon-trash"></i></button>
                </div>
                <div class="photo-block">
                    <span class="helper"></span>
                    <img src="{{mainImageName}}" class="img-polaroid">
                </div>
            </div>
        </div>
    </td>
    <td>
        ' . $selectFilter . '
    </td>
    <td>
        <input type="text" name="variants[price][]"  id="" class="form-control" value="{{price}}">
    </td>
    <td>
        <input type="text" name="variants[code][]"  id="" class="form-control" value="{{code}}">
    </td>
    <td>
        <input type="text" name="variants[amount][]"  id="" class="form-control" value="{{amount}}">
    </td>
    <td>' . Html::dropDownList(
        'variants[available][]',
        null,
        \app\modules\shop\models\Modification::getAvailableVariants(),
        ['class' => 'form-control', 'options' => ['data-available' => true]]
    ) . '
    </td>
    <td>
        <input type="text" name="variants[name][]"  id="" class="form-control" value="{{name}}">
    </td>
    <td>
        <button class="btn btn-small remove_variant tooltips" type="button" data-placement="top" data-original-title="' . Yii::t('admin', 'Delete') . '">
            <i class="icon-trash"></i>
        </button>
    </td>
</tr>
';
$variantTemplate = \rmrevin\yii\minify\HtmlCompressor::compress($variantTemplate, ['extra' => true]);
$this->registerJs("var variantTemplate = '{$variantTemplate}'", \yii\web\View::POS_HEAD);

// Language
$this->params['actions_buttons'] = [
    [
        'label'        => $model->isNewRecord ? Yii::t('admin', 'Create') : Yii::t('admin', 'Update'),
        'options'      => [
            'form' => 'product-form-id',
            'type' => 'submit',
        ],
        'icon'         => 'fa fa-save',
        'iconPosition' => Button::ICON_POSITION_LEFT,
        'size'         => Button::SIZE_SMALL,
        'disabled'     => false,
        'block'        => false,
        'type'         => Button::TYPE_CIRCLE,
        'color'        => 'btn-success',
    ],
    [
        'label'        => Yii::t('admin', 'Save & Continue Edit'),
        'options'      => [
            'onclick' => 'sendFormReload("#product-form-id")',
        ],
        'icon'         => 'fa fa-check-circle',
        'iconPosition' => Button::ICON_POSITION_LEFT,
        'size'         => Button::SIZE_SMALL,
        'disabled'     => false,
        'block'        => false,
        'type'         => Button::TYPE_CIRCLE,
        'color'        => 'btn-success',
    ],
];
if (!$model->isNewRecord) {
    $this->params['actions_buttons'][] = [
        'label'        => '',
        'options'      => [
            'href'         => Url::toRoute(['product/delete', 'id' => $model->id]),
            'data-confirm' => Yii::t('admin', 'Вы уверены, что хотите удалить этот элемент?'),
            'data-method'  => 'post',
            'data-pjax'    => '0',
        ],
        'icon'         => 'glyphicon glyphicon-trash',
        'iconPosition' => Button::ICON_POSITION_LEFT,
        'size'         => Button::SIZE_SMALL,
        'disabled'     => false,
        'block'        => false,
        'type'         => Button::TYPE_CIRCLE,
        'color'        => 'btn-danger',
        'tagName'      => 'a',
    ];
}
?>

<div class="product-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'enctype' => 'multipart/form-data',
            'id' => 'product-form-id',
            'data' => [
                'sort-url' => Url::to(['modification/sort'])
            ]
        ]
    ]); ?>
    <?php if(!$model->isNewRecord):?>
        <?= $form->field($model, 'id')->hiddenInput(['id' => 'Product_Id'])->label(false) ?>
    <?php endif?>
    <div class="row">
        <div class="col-lg-6 col-xs-6">
            <?= $form->field($model, 'name')->textInput() ?>
        </div>
        <div class="col-lg-6 col-xs-6">
            <?= $form->field($model, 'slug')->textInput(['placeholder' => 'Не обязательно']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 col-xs-6">
            <?= $form->field($model, 'amount')->textInput() ?>
        </div>
        <div class="col-lg-6 col-xs-6">
            <?= $form->field($model, 'code')->textInput() ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-2 col-xs-2">
            <?php if ($model->isNewRecord) $model->available = 'yes'; ?>
            <?= $form->field($model, 'available')->radioList(['yes' => 'Да', 'no' => 'Нет']); ?>
        </div>
        <div class="col-lg-2 col-xs-2">
            <?php if ($model->isNewRecord) $model->is_new = 'no'; ?>
            <?= $form->field($model, 'is_new')->radioList(['yes' => 'Да', 'no' => 'Нет']); ?>
        </div>
        <div class="col-lg-2 col-xs-2">
            <?php if ($model->isNewRecord) $model->is_popular = 'no'; ?>
            <?= $form->field($model, 'is_popular')->radioList(['yes' => 'Да', 'no' => 'Нет']); ?>
        </div>
        <div class="col-lg-2 col-xs-2">
            <?php if ($model->isNewRecord) $model->is_promo = 'no'; ?>
            <?= $form->field($model, 'is_promo')->radioList(['yes' => 'Да', 'no' => 'Нет']); ?>
        </div>
        <div class="col-lg-2 col-xs-2">
            <?= $form->field($model, 'sort')->textInput() ?>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-6 col-xs-6">
            <?= $form->field($model, 'category_id')
                ->widget(Select2::classname(), [
                    'data'          => Category::buildTextTree(),
                    'language'      => 'ru',
                    'options'       => ['placeholder' => 'Выберите категорию ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]); ?>

            <?= $form->field($model, 'producer_id')->widget(Select2::classname(), [
                'data'          => ArrayHelper::map(Producer::find()->all(), 'id', 'name'),
                'language'      => 'ru',
                'options'       => ['placeholder' => 'Выберите бренд ...'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]); ?>
        </div>
        <div class="col-lg-6 col-xs-6">
            <?= $form->field($model, 'category_ids')
                ->label('Прочие категории')
                ->widget(Select2::classname(), [
                    'data'          => Category::buildTextTree(),
                    'language'      => 'ru',
                    'options'       => ['multiple' => true, 'placeholder' => 'Доп. категории ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->hint(Yii::t('shop', 'Tighten Ctrl to select multiple items')); ?>
        </div>
    </div>

    <div class="portlet box box-success<?= (count($model->modifications) == 0) ? ' collapsed-box' : '' ?>">
        <div class="box-header">
            <h3 class="box-title"><i class="fa fa-object-group"></i> <?= Yii::t('shop', 'Product Modifications') ?></h3>
            <div class="box-tools pull-right">
                <?php if(count($model->modifications) == 0):?>
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                <?php else:?>
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <?php endif?>
            </div>
        </div>
        <div class="box-body">
            <table id="modifications-table" class="table table-bordered table-striped table-condensed">
                <thead class="font-12">
                    <tr>
                        <th style="width: 120px"> <?= Yii::t('shop', 'Image') ?> </th>
                        <th style="width: 150px"> <?= Yii::t('shop', 'Filter') ?> </th>
                        <th> <?= Yii::t('shop', 'Price') ?> </th>
                        <th> <?= Yii::t('shop', 'Vendor code') ?> </th>
                        <th> <?= Yii::t('shop', 'Amount') ?> </th>
                        <th> <?= Yii::t('shop', 'Available') ?> </th>
                        <th> <?= Yii::t('shop', 'Name') ?> </th>
                        <th width="56"></th>
                    </tr>
                </thead>
                <tbody class="sortable save_positions"
                       data-path="<?= '/web/uploads/' ?>"
                       data-delete="<?= Url::to(['modification/delete']) ?>">
                <?php if(count($model->modifications)):?>
                    <?php foreach($model->modifications as $k=>$variant) : ?>
                        <?php
                        $selectFilter = unserialize($variant->filter_values);

                        foreach ($selectFilter as $filter=>$value) {
                            $variantTemplate = preg_replace(
                                '/name="variants\[filter_values\]\[\]\['.$filter.'\](.*)<option value="' . $value . '">/',
                                'name="variants[filter_values][]['.$filter.']"$1<option value="' . $value .  '" selected>',
                                $variantTemplate
                            );
                        }

                        if($k == 0) {
                            $template = str_ireplace('<button class="btn btn-small remove_variant tooltips" type="button" data-placement="top" data-original-title="Удалить"><i class="icon-trash"></i></button>', '', $variantTemplate);
                            echo preg_replace(
                                [
                                    '/{{id}}/',
                                    '/{{price}}/',
                                    '/{{code}}/',
                                    '/{{amount}}/',
                                    '/{{variantsId}}/',
                                    '/{{mainImageName}}/',
                                    '/{{mainImageSlug}}/',
                                    '/{{name}}/',
                                    '/name="variants\[available\]\[\]"(.*)<option value="' . $variant->available . '">/'
                                ],
                                [
                                    $k,
                                    $variant->price,
                                    $variant->code,
                                    $variant->amount,
                                    $variant->id,
                                    $variant->getImage()->getUrl('120x120'),
                                    $variant->getImage()->urlAlias,
                                    $variant->name,
                                    'name="variants[available][]"$1<option value="' . $variant->available . '" selected>'
                                ],
                                $template);
                        } else {
                            echo preg_replace(
                                [
                                    '/{{id}}/',
                                    '/{{price}}/',
                                    '/{{code}}/',
                                    '/{{amount}}/',
                                    '/{{variantsId}}/',
                                    '/{{mainImageName}}/',
                                    '/{{mainImageSlug}}/',
                                    '/{{name}}/',
                                    '/name="variants\[available\]\[\]"(.*)<option value="' . $variant->available . '">/'
                                ],
                                [
                                    $k,
                                    $variant->price,
                                    $variant->code,
                                    $variant->amount,
                                    $variant->id,
                                    $variant->getImage()->getUrl('120x120'),
                                    $variant->getImage()->urlAlias,
                                    $variant->name,
                                    'name="variants[available][]"$1<option value="' . $variant->available . '" selected>'
                                ],
                                $variantTemplate);
                        }
                        ?>
                    <?php endforeach; ?>
                <?php else:?>
                    <?php
                    $variantTemplate=str_ireplace('<button class="btn btn-small remove_variant tooltips" type="button" data-placement="top" data-original-title="Удалить"><i class="icon-trash"></i></button>','',$variantTemplate);
                    ?>
                    <?= str_replace(
                        [
                            '{{id}}',
                            '{{price}}',
                            '{{code}}',
                            '{{amount}}',
                            '{{variantsId}}',
                            '{{mainImageName}}',
                            '{{mainImageSlug}}',
                            '{{name}}',
                        ],
                        [
                            0,
                            '',
                            '',
                            '',
                            '',
                            'https://placeholdit.imgix.net/~text?txtsize=20&txt=120%C3%97120&w=120&h=120',
                            '',
                            '',
                        ],
                        $variantTemplate)
                    ?>
                <?php endif?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="8">
                        <button type="button" class="btn purple-plum" id="addVariant">
                            <i class="fa fa-plus"></i>
                            Добавить вариант
                        </button>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <?= $form->field($model, 'text')->widget(\app\widgets\Editor::className()) ?>

    <?= $form->field($model, 'short_text')->textInput(['maxlength' => true]) ?>

    <div class="mb-20">
        <?= Gallery::widget(['model' => $model]); ?>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading"><strong>Связанные продукты</strong></div>
        <div class="panel-body">
            <?= \app\modules\relations\widgets\Constructor::widget(['model' => $model]); ?>
        </div>
    </div>
    <?php if (isset($priceTypes)): ?>
        <?php if ($priceTypes): ?>
            <h3>Цены</h3>
            <?php $i = 1; ?>
            <?php foreach ($priceTypes as $priceType): ?>
                <?= $form->field($priceModel, "[{$priceType->id}]price")->label($priceType->name); ?>
                <?php $i++; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
    <?php ActiveForm::end(); ?>
</div>
