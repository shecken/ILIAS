<?php

require_once "./Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.SettingFactory.php";
require_once "./Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/class.ilReportMasterPlugin.php";
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
/* Copyright (c) 2016 Denis Klöpfer, Extended GPL, see docs/LICENSE */

/**
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */

class SettingsTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		global $ilDB;
		$this->db = $ilDB;

		global $ilDB;
		$this->db = $ilDB;
		$this->s_f = new SettingFactory($this->db);
		$this->master_plugin = new ilReportMasterPlugin();
		date_default_timezone_set("Europe/Berlin");
	}

	public function test_Create() {
		$settings = $this->reportSettingsWDBMock('rep_master_data')
							->addSetting($this->s_f->settingBool('is_online',"is_online_name"))
							->addSetting($this->s_f->settingString('video_link',"pdf_link_name")
											   ->setFromForm(function ($string) {
													if(preg_match("/^(https:\/\/)|(http:\/\/)[\w]+/", $string) === 1) {
														return $string;
													}
													return 'https://'.$string;
												}
											)
										);
		$name = $settings->setting('is_online')->name();
		$redid = call_user_func($settings->setting('video_link')->fromForm(),'www.google.de');
		$this->assertEquals($name, "is_online_name");
		$this->assertEquals($redid, 'https://www.google.de');
	}

	/**
	 * @expectedException ReportSettingsException
	 */
	public function test_Create_WrongTables() {
		$settings = $this->s_f->reportSettings('some_weirdo_table')
						->addSetting($this->s_f->settingBool('is_online',"is_online_name"))
						->addSetting($this->s_f->settingString('video_link',"pdf_link_name")
											   ->setFromForm(function ($string) {
													if(preg_match("/^(https:\/\/)|(http:\/\/)[\w]+/", $string) === 1) {
														return $string;
													}
													return 'https://'.$string;
												}
											)
										);

	}

	public function test_query() {
		$settings = $this->s_f->reportSettings('object_data')
						->addSetting($this->s_f->settingInt('obj_id',"is_online_name"))
						->addSetting($this->s_f->settingString('title',"pdf_link_name")
											   ->setFromForm(function ($string) {
													if(preg_match("/^(https:\/\/)|(http:\/\/)[\w]+/", $string) === 1) {
														return $string;
													}
													return 'https://'.$string;
												}
											)
										);
		$this->assertTrue(is_array($this->s_f->reportSettingsDataHandler()->query(array('title' => '1'),$settings)));

	}

	/**
	 * @expectedException ReportSettingsException
	 */
	public function test_quote_error() {
		//quotes are used during query
		$settings = $this->reportSettingsWDBMock('rep_master_data')
						->addSetting($this->s_f->settingBool('is_online',"is_online_name"))
						->addSetting((new SettingFake('video_link',"pdf_link_name"))
											   ->setFromForm(function ($string) {
													if(preg_match("/^(https:\/\/)|(http:\/\/)[\w]+/", $string) === 1) {
														return $string;
													}
													return 'https://'.$string;
												}
											)
										);
		$data_handler = $this->s_f->reportSettingsDataHandler();
		$data_handler->query(array('video_link' => '1'),$settings);
	}


	public function test_build_form() {
		$settings = $this->reportSettingsWDBMock('any_table')
						->addSetting($this->s_f->settingBool('setting_1','1'))
						->addSetting($this->s_f->settingInt('setting_2','2'))
						->addSetting($this->s_f->settingFloat('setting_3','3'))
						->addSetting($this->s_f->settingHiddenInt('setting_4','4'))
						->addSetting($this->s_f->settingHiddenString('setting_5','5'))
						->addSetting($this->s_f->settingString('setting_6','6'))
						->addSetting($this->s_f->settingText('setting_7','7'));
		$form = new ilPropertyFormGUI();
		$f_h = $this->s_f->reportSettingsFormHandler();
		$f_h->addToForm($form,$settings);
		$vals = array('setting_1'=>true,'setting_2'=>2,'setting_3'=>0.1,'setting_4'=>1,'setting_5'=>'aaa','setting_6'=>'bbb'
			,'setting_7'=>'loremipsumAAAAASDADDASDASDASDAS');
		$f_h->insertValues($vals,$form,$settings);
		$back = $f_h->extractValues($form,$settings);
		$this->assertSame($vals,$back);
	}

	/**
	 * @expectedException ReportSettingsException
	 */
	public function test_build_form_false_settings() {
		$settings = $this->reportSettingsWDBMock('any_table')
						->addSetting($this->s_f->settingBool('setting_1','1'))
						->addSetting($this->s_f->settingInt('setting_2','2'))
						->addSetting($this->s_f->settingFloat('setting_3','3'))
						->addSetting($this->s_f->settingHiddenInt('setting_4','4'))
						->addSetting($this->s_f->settingHiddenString('setting_5','5'))
						->addSetting($this->s_f->settingString('setting_6','6'))
						->addSetting($this->s_f->settingText('setting_7','7'))
						->addSetting(new SettingFake('video_link',"pdf_link_name"));
		$form = new ilPropertyFormGUI();
		$f_h = $this->s_f->reportSettingsFormHandler();
		$f_h->addToForm($form,$settings);
	}

	private function reportSettingsWDBMock($id) {
		$mock_db = $this->getMockBuilder('ilDB')
			->disableOriginalConstructor()
			->disableOriginalClone()
			->disableArgumentCloning()
			->disallowMockingUnknownTypes()
			->getMock();
		$mock_db->method('tableColumnExists')->willReturn(true);
		$mock_db->method('tableExists')->willReturn(true);
		return new ReportSettings($id,$mock_db);
	}

}

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.Setting.php';
class SettingFake extends Setting {
	protected function defaultDefaultValue() {
		return '';
	}
}