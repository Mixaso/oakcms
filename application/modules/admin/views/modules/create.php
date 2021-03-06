<?php
/**
 * @package    oakcms
 * @author     Hryvinskyi Volodymyr <script@email.ua>
 * @copyright  Copyright (c) 2015 - 2017. Hryvinskyi Volodymyr
 * @version    0.0.1-alpha.0.5
 */

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Modules */

$this->title = Yii::t('admin', 'Create Modules Modules');
$this->params['breadcrumbs'][] = ['label' => Yii::t('admin', 'Modules Modules'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="modules-modules-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
