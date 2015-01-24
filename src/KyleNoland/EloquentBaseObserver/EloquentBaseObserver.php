<?php namespace KyleNoland\EloquentBaseObserver;

use Illuminate\Database\Eloquent\Model;

class BaseObserver
{
	/**
	 * Clean up model data before any insertion operating
	 *
	 * @param Model $model
	 */
	public function saving(Model $model)
	{
		//
		// Convert all date attributes to Y-m-d H:i:s format
		//

		if($model->hasDateAttributes())
		{
			$this->transformDateFormats($model);
		}

		//
		// Remove non-numeric characters from all numeric attributes, such as phone numbers
		//

		if($model->hasNumericAttributes())
		{
			$this->stripNonNumericCharacters($model);
		}

		//
		// Replace empty string attributes with null values
		//

		$this->convertEmptyStringsToNull($model);
	}


	/**
	 * Convert all date attributes to Y-m-d H:i:s format
	 *
	 * @param Model $model
	 */
	protected function transformDateFormats(Model $model)
	{
		$dateAttributes = $model->getDateAttributes();

		foreach($dateAttributes as $attribute)
		{
			//
			// Replace empty strings with null values
			//

			if(trim($model->getAttribute($attribute) === ''))
			{
				$model->setAttribute($attribute, null);
			}

			if( ! is_null($model->getAttribute($attribute)))
			{
				$model->setAttribute($attribute, date('Y-m-d H:i:s', strtotime($model->getAttribute($attribute))));
			}
		}
	}


	/**
	 * Remove non-numeric characters from all numeric attributes, such as phone numbers
	 *
	 * @param Model $model
	 */
	protected function stripNonNumericCharacters(Model $model)
	{
		$numericAttributes = $model->getNumericAttributes();

		foreach($numericAttributes as $attribute)
		{
			if( ! is_null($model->getAttribute($attribute)))
			{
				$model->setAttribute($attribute, strip_non_numeric($model->getAttribute($attribute)));
			}
		}
	}


	/**
	 * Replace empty string values with null
	 *
	 * @param Model $model
	 */
	protected function convertEmptyStringsToNull(Model $model)
	{
		$allAttributes      = array_keys($model->getAttributes());
		$currencyAttributes = $model->getCurrencyAttributes();
		$dateAttributes     = $model->getDateAttributes();
		$numericAttributes  = $model->getNumericAttributes();
		$otherAttributes    = array('created_at', 'updated_at', 'deleted_at');

		//
		// Get the set of attributes that are not explicitly processed by separate rules,
		// such as numeric, currency and date attributes
		//

		$attributes = array_diff(
			$allAttributes,
			$currencyAttributes,
			$dateAttributes,
			$numericAttributes,
			$otherAttributes
		);

		foreach($attributes as $attribute)
		{
			$value = $model->getAttribute($attribute);

			//
			// Don't try to trim a boolean false value because it will be converted to an empty string
			//

			if($value === false)
			{
				continue;
			}

			$value = trim($value) === '' ? null : trim($value);

			$model->setAttribute($attribute, $value);
		}
	}
}