<?php

use CaT\IliasUserOrguImport\Filesystem\Filesystem as Filesystem;

class ilDeployedUserOrguDataGUI
{

	const GET_FILE = 'file';
	const CMD_SHOW = 'show';
	const CMD_DOWNLOAD_FILE = 'download_file';

	public function __construct($plugin, $parent_gui)
	{
		$this->plugin = $plugin;
		$this->parent = $parent_gui;

		global $DIC;
		$this->tpl = $DIC['tpl'];
		$this->ctrl = $DIC['ilCtrl'];

		$this->filesystem = Filesystem::getInstance();
	}

	public function executeCommand()
	{
		$this->cmd = $this->ctrl->getCmd(self::CMD_SHOW);
		switch ($this->cmd) {
			case self::CMD_SHOW:
				$this->showFiles();
				break;
			case self::CMD_DOWNLOAD_FILE:
				$this->downloadFile();
				break;
		}
		return true;
	}

	protected function showFiles()
	{
		$files = $this->filesystem->readDir($this->filesystem->getAbsolutePath());
		$links = '';
		foreach ($files as $file) {
			$this->ctrl->setParameter($this, self::GET_FILE, base64_encode($file));
			$links .= '<br><a href = "'.$this->ctrl->getLinkTarget($this, self::CMD_DOWNLOAD_FILE, '', true).'">'.$file.'</a>';
			$this->ctrl->setParameter($this, self::GET_FILE, null);
		}
		$this->tpl->setContent($links);
	}

	protected function downloadFile()
	{
		$filename = base64_decode($_GET[self::GET_FILE]);
		$path = $this->filesystem->getAbsolutePath().DIRECTORY_SEPARATOR.$filename;
		\ilUtil::deliverFile($path, $filename);
	}
}
