<?php

class ArticleSection extends ActiveRecordModel
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


	public function tableName()
	{
		return 'articles_sections';
	}


	public function rules()
	{
		return array(
			array('name, lang', 'required'),
			array('in_sidebar, parent_id', 'numerical', 'integerOnly' => true),
			array('name', 'length', 'max' => 100),
           	array('name', 'unique', 'attributeName' => 'name', 'className' => 'ArticleSection'),
			array('id, name, date_create', 'safe', 'on' => 'search'),
		);
	}


	public function relations()
	{
		return array(
			'articles' => array(self::HAS_MANY, 'Article', 'section_id'),
			'parent'   => array(self::BELONGS_TO, 'ArticleSection', 'parent_id'),
			'childs'   => array(self::HAS_MANY, 'ArticleSection', 'parent_id'),
		    'language' => array(self::BELONGS_TO, 'Language', 'lang')
		);
	}


	public function search()
	{
		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id, true);
		$criteria->compare('name', $this->name, true);
		$criteria->compare('date_create', $this->date_create, true);

        $page_size = 10;
        if (isset(Yii::app()->session[get_class($this) . "PerPage"]))
        {
            $page_size = Yii::app()->session[get_class($this) . "PerPage"];
        }

		return new CActiveDataProvider(get_class($this), array(
			'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $page_size,
            ),
		));
	}


    public function delete()
    {
        foreach ($this->articles as $article)
        {
            $article->delete();
        }

        parent::delete();
    }


	public function beforeSave() 
	{
		if (parent::beforeSave()) 
		{
			if ($this->in_sidebar == 1) 
			{	
				$this->updateAll(array('in_sidebar' => 0));
			}

			return true;
		}		
	}			
}
