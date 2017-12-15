<?php

namespace CaT\IliasUserOrguImport\Filesystem;

/**
 * Storage for filesystem related settings, i.e. deployment path.
 */
class FilesystemConfig
{
	const DEPLOYMENT_PATH = 'xuoi_data_deployment_path';

	/**
	 * @param	\ilSetting	$settings
	 */
	public function __construct($settings)
	{
		$this->settings = $settings;
		$this->settings->read();
	}

	/**
	 * Get currently configured deployment path of data source-files.
	 *
	 * @return	string|null
	 */
	public function deploymentPath()
	{
		$deployment_path = $this->settings->get(self::DEPLOYMENT_PATH);
		if (trim((string)$deployment_path) !== '') {
			return $deployment_path;
		}
		return null;
	}

	/**
	 * Set deployment path of data source-files.
	 *
	 * @param	string	$path
	 * @return	self
	 */
	public function withDeploymentPath($path)
	{
		assert('is_string($path)');
		if (!is_readable($path) || !is_dir($path)) {
			throw new \InvalidArgumentException($path.' may not be accessed');
		}
		$this->settings->set(self::DEPLOYMENT_PATH, $path);
		return new self($this->settings);
	}
}
