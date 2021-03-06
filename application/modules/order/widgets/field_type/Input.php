<?php
namespace app\modules\order\widgets\field_type;

use app\modules\order\models\FieldValue;

class Input extends \yii\base\Widget
{
    public $fieldValueModel = null;
    public $fieldModel = null;
    public $form = null;
    public $defaultValue = '';

    public function run()
    {
        $fieldValueModel = new FieldValue;
        $fieldValueModel->value = $this->defaultValue;

        return $this->form->field($fieldValueModel, 'value['.$this->fieldModel->id.']')->label($this->fieldModel->name)->textInput(['value' => $this->defaultValue, 'required' => ($this->fieldModel->required == 'yes')]);
    }
}
