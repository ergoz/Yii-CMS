<?php
echo $this->module->assetsUrl() . 'js/MetaTagForm.js';
Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl() . '/js/MetaTagForm.js');

$this->tabs = array(
    'управление' => $this->createUrl('manage')
);

echo $form;
