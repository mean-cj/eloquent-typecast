<?php namespace Cviebrock\EloquentTypecast;


trait EloquentTypecastTrait {

	/**
	 * Attributes to type cast.  Array key is the attribute name, value is the
	 * PHP variable type to cast to.
	 *
	 * NOTE: you need to define this in your models.
	 *
	 * @var array $cast
	 */


	/**
	 * Boot the typecasting trait for a model, which will add all our typecast-able
	 * attributes to the mutator cache.  This way, they get mutated without
	 * us needing to write a mutator function for each one.
	 *
	 * @return void
	 */
	protected static function bootEloquentTypecastTrait()
	{

		$class = get_called_class();
		$instance = new $class();

		foreach($instance->getCastAttributes() as $attribute=>$type)
		{
			static::$mutatorCache[$class][] = $attribute;
		}
	}


	/**
	 * Get the value of an attribute using its mutator.  If the attribute
	 * is typecast-able, then return the cast value instead.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return mixed
	 * @see  Illuminate\Database\Eloquent\Model::mutateAttribute()
	 */
	protected function mutateAttribute($key, $value)
	{
		if ($this->isCastableAttribute($key))
		{
			return $this->castAttribute($key, $value);
		}

		return parent::mutateAttribute($key, $value);
	}


	/**
	 * Set a given attribute on the model.  If the attribute is typecast-able,
	 * then cast the value before setting it.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 * @see  Illuminate\Database\Eloquent\Model::setAttribute()
	 */
	public function setAttribute($key, $value)
	{
		if ($this->castOverride() && $this->isCastableAttribute($key))
		{
			$value = $this->castAttribute($key, $value);
		}
		return parent::setAttribute($key, $value);
	}

	/**
	 * Get a given attribute on the model.  If the attribute is typecast-able,
	 * then cast the value before getting it.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function getAttributeValue($key)
	{
		$value = parent::getAttributeValue($key);

		if ($this->isCastableAttribute($key))
		{
			$value = $this->castAttribute($key, $value);
		}

		return $value;
	}

	/**
	 * Return the array of attributes to cast.
	 *
	 * @return array
	 */
	protected function getCastAttributes()
	{
		return isset($this->casts) ? $this->casts : array();
	}


	/**
	 * Return the array of attributes to cast.
	 *
	 * @return array
	 */
	protected function castOverride()
	{
		return isset($this->castOverride) ? $this->castOverride : false;
	}


	/**
	 * Is the given attribute typecast-able.
	 *
	 * @return bool
	 */
	protected function isCastableAttribute($key)
	{
		return array_key_exists($key, $this->getCastAttributes());
	}


	/**
	 * Cast an attribute to a PHP variable type.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 * @throws EloquentTypecastException
	 * @return  mixed
	 */
	protected function castAttribute($key, $value)
	{
	       if( empty($key) || ! isset($this->casts[$key]) ) return $value;
	       
		$type = $this->casts[$key];
		
		try {
			if ( settype($value, $type) ) {
				return $value;
			}
			throw new EloquentTypecastException("Value could not be cast to type \"$type\"", 1);
		} catch (\Exception $e) {
			throw new EloquentTypecastException("Value could not be cast to type \"$type\"", 1);
		}
	}

}
