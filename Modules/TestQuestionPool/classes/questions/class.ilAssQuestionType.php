<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssQuestionType
{
	/**
	 * @var ilPluginAdmin
	 */
	protected $pluginAdmin;
	
	/**
	 * @var integer
	 */
	protected $id;
	
	/**
	 * @var string
	 */
	protected $tag;
	
	/**
	 * @var bool
	 */
	protected $plugin;
	
	/**
	 * @var string
	 */
	protected $pluginName;
	
	/**
	 * ilAssQuestionType constructor.
	 */
	public function __construct()
	{
		$this->pluginAdmin = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['ilPluginAdmin'] : $GLOBALS['ilPluginAdmin'];
	}
	
	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}
	
	/**
	 * @return string
	 */
	public function getTag()
	{
		return $this->tag;
	}
	
	/**
	 * @param string $tag
	 */
	public function setTag($tag)
	{
		$this->tag = $tag;
	}
	
	/**
	 * @return bool
	 */
	public function isPlugin()
	{
		return $this->plugin;
	}
	
	/**
	 * @param bool $plugin
	 */
	public function setPlugin($plugin)
	{
		$this->plugin = $plugin;
	}
	
	/**
	 * @return string
	 */
	public function getPluginName()
	{
		return $this->pluginName;
	}
	
	/**
	 * @param string $pluginName
	 */
	public function setPluginName($pluginName)
	{
		$this->pluginName = $pluginName;
	}
	
	/**
	 * @return bool
	 */
	public function isImportable()
	{
		if( !$this->isPlugin() )
		{
			return true;
		}
		
		return $this->pluginAdmin->isActive(
			IL_COMP_MODULE, ilQuestionsPlugin::COMP_NAME, ilQuestionsPlugin::SLOT_ID, $this->getPluginName()
		);
	}
	
	/**
	 * @param array $questionTypeData
	 * @return array
	 */
	public static function conmpleteMissingPluginName($questionTypeData)
	{
		if( $questionTypeData['plugin'] && !strlen($questionTypeData['plugin_name']) )
		{
			$questionTypeData['plugin_name'] = $questionTypeData['type_tag'];
		}
		
		return $questionTypeData;
	}
}