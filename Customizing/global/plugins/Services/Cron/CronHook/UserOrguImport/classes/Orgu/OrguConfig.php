<?php

namespace CaT\IliasUserOrguImport\Orgu;

/**
 * Orgu related configuration.
 */
class OrguConfig
{

	/**
	 * @param	\ilSetting	$settings
	 */
	public function __construct(\ilSetting $settings)
	{
		$this->settings = $settings;
	}

	const KEYWORD_ROOT_REF_ID = 'xuoi_root_ref_id';
	const KEYWORD_EXIT_REF_ID = 'xuoi_exit_ref_id';

	const ROOT_ID = 'Konzernunternehmen';
	const EXIT_ID = 'Exit-User';

	/**
	 * Read id from setting object by keyword.
	 *
	 * @param	string	$keyword
	 * @return	int|null
	 */
	protected function getSettingByKeyword($keyword)
	{
		assert('is_string($keyword)');
		$this->settings->read();
		$value = $this->settings->get($keyword);
		if (trim((string)$value) === '') {
			return null;
		}
		return $value;
	}

	protected function setSettingByKeyword($keyword, $value)
	{
		assert('is_string($keyword)');
		$this->settings->set($keyword, $value);
		$this->settings->read();
	}

	/**
	 * Get the root ref id of import tree.
	 *
	 * @return	int
	 */
	public function getRootRefId()
	{
		return (int)$this->getSettingByKeyword(self::KEYWORD_ROOT_REF_ID);
	}

	/**
	 * Set the root ref id of import tree.
	 *
	 * @param	int|null	$ref_id
	 * @return 	self
	 */
	public function setRootRefId($ref_id)
	{
		assert('is_int($ref_id)');
		$this->setSettingByKeyword(self::KEYWORD_ROOT_REF_ID, $ref_id);
	}

	/**
	 * Get the ref id of exit orgu for deleted users.
	 *
	 * @return	int
	 */
	public function getExitRefId()
	{
		return (int)$this->getSettingByKeyword(self::KEYWORD_EXIT_REF_ID);
	}


	/**
	 * Set the ref id of exit orgu for deleted users.
	 *
	 * @param	int	$ref_id
	 */
	public function setExitRefId($ref_id)
	{
		assert('is_int($ref_id)');
		$this->setSettingByKeyword(self::KEYWORD_EXIT_REF_ID, $ref_id);
	}
}
