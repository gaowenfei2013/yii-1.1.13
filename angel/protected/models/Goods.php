<?php

/**
 * This is the model class for table "{{goods}}".
 *
 * The followings are the available columns in table '{{goods}}':
 * @property string $id
 * @property integer $category_id
 * @property integer $brand_id
 * @property integer $supplier_id
 * @property string $name
 * @property string $sku
 * @property string $cost_price
 * @property string $market_price
 * @property string $sale_price
 * @property integer $stock
 * @property integer $weight
 * @property integer $on_sale
 * @property string $pic
 * @property string $title
 * @property string $keywords
 * @property string $description
 * @property string $content
 */
class Goods extends CActiveRecord implements SShoppingCartItem
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Goods the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{goods}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, content', 'required'),
			array('category_id, brand_id, supplier_id, stock, weight, on_sale', 'numerical', 'integerOnly'=>true),
			array('name, pic, title', 'length', 'max'=>100),
			array('sku', 'length', 'max'=>40),
			array('cost_price, market_price, sale_price', 'length', 'max'=>10),
			array('keywords', 'length', 'max'=>200),
			array('description', 'length', 'max'=>300),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, category_id, brand_id, supplier_id, name, sku, cost_price, market_price, sale_price, stock, weight, on_sale, pic, title, keywords, description, content', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'category_id' => 'Category',
			'brand_id' => 'Brand',
			'supplier_id' => 'Supplier',
			'name' => 'Name',
			'sku' => 'Sku',
			'cost_price' => 'Cost Price',
			'market_price' => 'Market Price',
			'sale_price' => 'Sale Price',
			'stock' => 'Stock',
			'weight' => 'Weight',
			'on_sale' => 'On Sale',
			'pic' => 'Pic',
			'title' => 'Title',
			'keywords' => 'Keywords',
			'description' => 'Description',
			'content' => 'Content',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('brand_id',$this->brand_id);
		$criteria->compare('supplier_id',$this->supplier_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('sku',$this->sku,true);
		$criteria->compare('cost_price',$this->cost_price,true);
		$criteria->compare('market_price',$this->market_price,true);
		$criteria->compare('sale_price',$this->sale_price,true);
		$criteria->compare('stock',$this->stock);
		$criteria->compare('weight',$this->weight);
		$criteria->compare('on_sale',$this->on_sale);
		$criteria->compare('pic',$this->pic,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('keywords',$this->keywords,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('content',$this->content,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function getId()
	{
		return $this->id;
	}

	public function getPrice()
	{
		return $this->sale_price;
	}
}