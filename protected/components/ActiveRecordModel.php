<?php

abstract class ActiveRecordModel extends CActiveRecord
{
    const PATTERN_RULAT_ALPHA_SPACES = '/^[а-яa-z ]+$/ui';
    const PATTERN_RULAT_ALPHA        = '/^[а-яa-z]+$/ui';
    const PATTERN_LAT_ALPHA          = '/^[A-Za-z]+$/ui';
    const PATTERN_PHONE              = '/^\+[1-9]-[0-9]+-[0-9]{7}$/';

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';


    abstract public function name();


    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }


    public function behaviors()
    {   
        return array(
            'LangCondition' => array(
                'class' => 'application.components.activeRecordBehaviors.LangConditionBehavior'
            ),
            'NullValue' => array(
                'class' => 'application.components.activeRecordBehaviors.NullValueBehavior'
            ),
            'UserForeignKey' => array(
                'class' => 'application.components.activeRecordBehaviors.UserForeignKeyBehavior'
            ),
            'UploadFile' => array(
                'class' => 'application.components.activeRecordBehaviors.UploadFileBehavior'
            ),
            'DateFormat' => array(
                'class' => 'application.components.activeRecordBehaviors.DateFormatBehavior'
            ),
            'Timestamp' => array(
                'class' => 'application.components.activeRecordBehaviors.TimestampBehavior'
            ),
            'MaxMin' => array(
                'class' => 'application.components.activeRecordBehaviors.MaxMinBehavior'
            ),
            'Scopes' => array(
                'class' => 'application.components.activeRecordBehaviors.ScopesBehavior'
            )
        );
    }


    public function attributeLabels()
    {
        $meta = $this->meta();

        $labels = array();

        foreach ($meta as $field_data)
        {
            $labels[$field_data["Field"]] = Yii::t('main', $field_data["Comment"]);
        }

        return $labels;
    }


    /*VALIDATORS________________________________________________________________________________*/
    public function city($attr) 
    {	
    	$name = trim($this->$attr);
    	
    	if (!empty($name)) 
    	{
    		if (!is_numeric($name)) 
    		{
		    	$city = City::model()->findByAttributes(array('name' => $name));
		    	if ($city) 
		    	{
		    		$this->$attr = $city->id;	
		    	}   
		    	else 
		    	{
		    		$this->addError($attr, Yii::t('main', 'Город не найден'));
		    	} 	    		
    		}
    	}
    	else 
    	{
    		$this->$attr = null;	
    	}
    }
        
    
    public function phone($attr)
    {
        if (!empty($this->$attr))
        {
            if (!preg_match(self::PATTERN_PHONE, $this->$attr))
            {
                $this->addError($attr, Yii::t('main', 'Неверный формат! Пример: +7-903-5492969'));
            }
        }
    }
	
    
    public function latAlpha($attr)
    {
        if (!empty($this->$attr))
        {
            if (!preg_match(self::PATTERN_LAT_ALPHA, $this->$attr))
            {
                $this->addError($attr, Yii::t('main', 'Только латинский алфавит'));
            }
        }    
    }
    
	
    public function ruLatAlpha($attr)
    {
        if (!empty($this->$attr))
        {
            if (!preg_match(self::PATTERN_RULAT_ALPHA, $this->$attr))
            {
                $this->addError($attr, Yii::t('main', 'Только русский или латинский алфавит'));
            }
        }
    }


    public function ruLatAlphaSpaces($attr)
    {
        if (!empty($this->$attr))
        {
            if (!preg_match(self::PATTERN_RULAT_ALPHA_SPACES, $this->$attr))
            {
                $this->addError($attr, Yii::t('main', 'Только русский или латинский алфавит с учетом пробелов'));
            }
        }
    }
    /*___________________________________________________________________________________*/


    /*MAGIC METHODS______________________________________________________________________*/
    public function __get($name)
	{
        try
        {
            return parent::__get($name);
        }
        catch (CException $e)
        {
            $method_name = StringHelper::underscoreToCamelcase($name);
            $method_name = 'get' . ucfirst($method_name);

            if (method_exists($this, $method_name))
            {
                return $this->$method_name();
            }
            else
            {
                throw new CException($e->getMessage());
            }
        }
	}


    public function __toString()
    {
        $attributes = array(
            'name',
            'title',
            'description',
            'id'
        );

        foreach ($attributes as $attribute)
        {
            if (array_key_exists($attribute, $this->attributes))
            {
                return $this->$attribute;
            }
        }
    }
    /*___________________________________________________________________________________*/


    /*SCOPES_____________________________________________________________________________*/



    public function meta()
    {
        $meta = Yii::app()->db
                          ->cache(1000)
                          ->createCommand("SHOW FUll columns FROM " . $this->tableName())
                          ->queryAll();
        
        foreach ($meta as $ind => $field_data)
        {
            $meta[$field_data["Field"]] = $field_data;
            unset($meta[$ind]);
        }
      
        return $meta;
    }


    public function optionsTree($name = 'name', $id = null, $result = array(), $value = 'id', $spaces = 0, $parent_id = null)
    {
        $objects = $this->findAllByAttributes(array(
            'parent_id' => $parent_id
        ));

        foreach ($objects as $object)
        {
            if ($object->id == $id) continue;

            $result[$object->$value] = str_repeat("_", $spaces) . $object->$name;

            if ($object->childs)
            {
                $spaces+=2;

                $result = $this->optionsTree($name, $id, $result, $value, $spaces, $object->id);
            }
        }

        return $result;
    }
}
