<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase2GUI.php';
require_once 'Modules/Test/classes/class.ilObjTest.php';

/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportExamBioGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportExamBioGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportExamBioGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportExamBioGUI extends ilObjReportBase2GUI {

	public function getType() {
		return 'xexb';
	}

	protected function addGetParametersToReport() {
		if(isset($_GET['target_user_id'])) {
			$target_user_id = $_GET['target_user_id'];
			$this->object->addRelevantParameter('target_user_id',$target_user_id);
		} else {
			$target_user_id = null;
		}
		$this->object->setTargets($this->gUser->getId(), $target_user_id);
	}

	public function transformResultRowTable($rec) {
		if($this->object->forTrainerView() && isset($rec['lastname'])) {
			$this->gCtrl->setParameter($this, 'target_user_id', $rec['usr_id']);
			$this->enableRelevantParametersCtrl();
			$this->gCtrl->setParameter($this,'filter_params',null);
			$user_link = $this->gCtrl->getLinkTarget($this,'');
			$this->disableRelevantParametersCtrl();
			$this->gCtrl->setParameter($this, 'target_user_id', null);
			$rec['lastname']
			= '<a href="'
				.$user_link 
				.'">'.$rec['lastname'].'</a>';

			$rec['firstname']
			= '<a href="'
				.$user_link
				.'">'.$rec['firstname'].'</a>';
		}
		$ref_id = current(ilObject::_getAllReferences($rec['obj_id']));
		$active_id = ilObjTest::_getActiveIdOfUser($rec['usr_id'], ilObjTest::_getTestIDFromObjectID($rec['obj_id']));
		$this->gCtrl->setParameterByClass('ilTestEvaluationGUI','ref_id',$ref_id);
		$this->gCtrl->setParameterByClass('ilTestEvaluationGUI','active_id',$active_id);
		$rec['test_title'] 
			= '<a href="'
				.$this->gCtrl->getLinkTargetByClass(array('ilRepositoryGUI','ilObjTestGUI','ilTestEvaluationGUI'),'outParticipantsResultsOverview')
				.'">'.$rec['test_title'].'</a>';
		$this->gCtrl->setParameterByClass('ilTestEvaluationGUI','ref_id',null);
		$this->gCtrl->setParameterByClass('ilTestEvaluationGUI','active_id',null);
		return $this->transformResultRowCommon($rec);
	}



	protected function transformResultRowCommon($rec) {
		$rec['test_date'] = (new DateTime())->setTimestamp($rec['test_date'])->format('d.m.Y');
		$rec['average'] = number_format(100*$rec['average'],0).'%';
		$rec['max'] = number_format(100*$rec['max'],0).'%';
		$rec['passed'] = (int)$rec['passed'] === 1 ? 'Ja' : 'Nein';
		return $rec;
	}

	/**
	* Settings menu of the report. Note that any setting query will be performed inside ilObjBaseReport.
	* Allways call parent methods in final plugin-classmethod static::settingFrom, static::getSettingsData and static::saveSettingsData.
	*/

	public static function examBiographyLinkForTraining($training_id) {
		global $ilCtrl;
		$ref_id = current(ilObject::_getAllReferences(current(ilObject::_getObjectsDataForType('xexb', true))["id"]));
		$ilCtrl->setParameterByClass('ilObjReportExamBioGUI', 'target_training_id', $training_id);
		$ilCtrl->setParameterByClass('ilObjReportExamBioGUI', 'ref_id', $ref_id);
		$return = $ilCtrl->getLinkTargetByClass(array('ilObjPluginDispatchGUI', 'ilObjReportExamBioGUI'), '');
		$ilCtrl->setParameterByClass('ilObjReportExamBioGUI', 'ref_id', null);
		$ilCtrl->setParameterByClass('ilObjReportExamBioGUI', 'target_training_id', null);
		return $return;
	}

	public static function examBiographyReferenceForUsers() {
		global $ilCtrl,$ilDB;
		$s_f = new settingFactory($ilDB);
		$settings_data_handler = $s_f->reportSettingsDataHandler();
	
		$obj_id = $s_f->reportSettingsDataHandler()
			->query($s_f->reportSettings('rep_robj_rexbio')
						->addSetting($s_f
							->settingBool('for_trainer','')))[0]['obj_id'];
		$ref_id = current(ilObject::_getAllReferences($obj_id));
		return $ref_id;
	}

	public static function examBiographyLinkForUser($ref_id,$usr_id = null) {
		global $ilCtrl;
		$ilCtrl->setParameterByClass('ilObjReportExamBioGUI', 'ref_id', $ref_id);
		$ilCtrl->setParameterByClass('ilObjReportExamBioGUI', 'target_user_id', $usr_id);
		$return = $ilCtrl->getLinkTargetByClass(array('ilObjPluginDispatchGUI', 'ilObjReportExamBioGUI'), '');
		$ilCtrl->setParameterByClass('ilObjReportExamBioGUI', 'ref_id', null);
		$ilCtrl->setParameterByClass('ilObjReportExamBioGUI', 'target_user_id', null);
		return $return;
	}

}