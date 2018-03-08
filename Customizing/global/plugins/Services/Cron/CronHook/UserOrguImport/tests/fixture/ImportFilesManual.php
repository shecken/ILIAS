<?php
namespace CaT\IliasUOITestObjects;

use CaT\IliasUserOrguImport\Filesystem\ImportFiles as ImportFiles;

class ImportFilesManual extends ImportFiles
{
	public $_user;
	public $_orgu_vvw;
	public $_orgu_loga;
	public $_user_orgu_vvw;
	public $_user_orgu_loga;

	protected $base_dir;

	public function getCurrentUserFilePath()
	{
		return $this->base_dir ? $this->base_dir.$this->_user : null;
	}

	public function getCurrentOrguVVWFilePath()
	{
		return $this->base_dir ? $this->base_dir.$this->_orgu_vvw : null;
	}

	public function getCurrentOrguLOGAFilePath()
	{
		return $this->base_dir ? $this->base_dir.$this->_orgu_loga : null;
	}

	public function getCurrentUserOrguVVWFilePath()
	{
		return $this->base_dir ? $this->base_dir.$this->_user_orgu_vvw : null;
	}

	public function getCurrentUserOrguLOGAFilePath()
	{
		return $this->base_dir ? $this->base_dir.$this->_user_orgu_loga : null;
	}

	public function __construct()
	{
		$this->base_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR;
	}
}
