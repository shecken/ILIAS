<?php
#DONE

namespace CaT\IliasUserOrguImport\User;

class UserConfig
{

	protected $settings;
	protected $possible_keywords;

	/**
	 * @param	\ilSetting	$settings
	 */
	public function __construct(\ilSetting $settings)
	{
		$this->settings = $settings;
		$this->settings->read();
		$this->possible_keywords = UdfWrapper::possibleUdfKeywords();
		$this->udf_field_ids = [];

		foreach ($this->possible_keywords as $keyword) {
			$id = $this->settings->get($keyword);
			if (trim((string)$id) === '') {
				continue;
			}
			$this->udf_field_ids[$keyword] = (int)$id;
		}
	}

	/**
	 * Udf-fields keyword => id.
	 * Configured ilias-side.
	 *
	 * @return	int[string]
	 */
	public function udfFields()
	{
		return $this->udf_field_ids;
	}

	/**
	 * Store udf-id corresponding to a keyword.
	 *
	 * @param	string	$keyword
	 * @param	int|null	$id
	 * @return void
	 */
	public function withUdfId($keyword, $id)
	{
		assert('is_string($keyword)');
		assert('is_int($id) || is_null($id)');
		if (!in_array($keyword, $this->possible_keywords)) {
			throw new \InvalidArgumentExcepition('invalid keyword '.$keyword);
		}
		$this->settings->set($keyword, $id);
		return new self($this->settings);
	}

	/**
	 * Get the udf-id for a keyword.
	 *
	 * @param	string	$keyword
	 * @return	int|null
	 */
	public function getUdfId($keyword)
	{
		assert('is_string($keyword)');
		if (!in_array($keyword, $this->possible_keywords)) {
			throw new \InvalidArgumentExcepition('invalid keyword '.$keyword);
		}
		return $this->udf_field_ids[$keyword];
	}

	/**
	 * Get a list of possible Udf Keywords.
	 *
	 * @return string[]
	 */
	public function possibleKeywords()
	{
		return $this->possible_keywords;
	}
}
