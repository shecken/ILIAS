<?php

require_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");
require_once("./Services/GEV/CourseSearch/classes/class.gevCourseSearch.php");

/**
 * Create an announcement in an overlay.
 */
class ilAnnouncementUIHookGUI extends ilUIHookPluginGUI
{
	/**
	 * @var ilLanguage
	 */
	protected $g_lng;

	/**
	 * @var ilUser
	 */
	protected $g_user;

	public function __construct()
	{
		global $ilUser, $lng;

		$this->g_lng = $lng;
		$this->g_user = $ilUser;
	}

	/**
	 * @inheritdoc
	 */
	public function getHTML($comp, $part, $par = array())
	{
		if (
			$part != "template_get" ||
			$par["tpl_id"] != "Services/MainMenu/tpl.main_menu.html" //||
			//$_COOKIE["gev_dsgvo"][$this->g_user->getId()] === "dsgvo"
		) {
			return parent::getHTML($comp, $part, $par);
		}

		setcookie("gev_dsgvo[".$this->g_user->getId()."]", "dsgvo", time()+31*24*36000);

		$tpl = new ilTemplate(
			"tpl.dsgvo.html",
			false,
			false,
			"Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Announcement"
		);

		return array(
			"mode" => ilUIHookPluginGUI::APPEND,
			"html" => $tpl->get()
		);
	}
}
