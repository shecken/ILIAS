<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catSelectableReportTableGUI.php';
use CaT\TableRelations as TableRelations;
use CaT\Filter as Filters;

class ilObjReportExamBio extends ilObjReportBase {

	public function __construct($a_ref_id = 0) {
		global $ilUser;
		$this->gUser = $ilUser;
		parent::__construct($a_ref_id);
		$this->gf = new TableRelations\GraphFactory();
		$this->pf = new Filters\PredicateFactory();
		$this->tf = new TableRelations\TableFactory($this->pf, $this->gf);

	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rcpn');
	}

	public function initType() {
		$this->setType("xexb");
	}

	public function prepareTable(catSelectableReportTableGUI $table) {

		return $table;
	}

	public function initSpace() {
		$aux = $this->tf->histUsertestrun('recent_pass_aux');
		$aux = $aux->addConstraint($aux->field('hist_historic')->EQ()->int(0));

		$recent_pass_case = $this->tf->derivedTable('recent_pass_case'
			,$this->tf->TableSpace()
				->addTablePrimary($aux)
				->request($aux->field('usr_id'))
				->request($aux->field('obj_id'))
				->request($this->tf->maxSql('recent_pass',$aux->field('pass')))
				->groupBy($aux->field('usr_id'))
				->groupBy($aux->field('obj_id')));

		$all_pass = $this->tf->histUsertestrun('all_pass');
		$all_pass	= $all_pass->addConstraint($all_pass->field('hist_historic')->EQ()->int(0));

		$recent_pass_data = $this->tf->histUsertestrun('recent_pass_data');
		$recent_pass_data = $recent_pass_data->addConstraint($recent_pass_data->field('hist_historic')->EQ()->int(0));

		$usr = $this->tf->hist_user('usr');
		$usr = $usr->addConstraint($usr->field('hist_historic')->EQ()->int(0));

		$orgus = $this->tf->allOrgusOfUsers('orgu_all',$users);

		$this->space = $this->tf->TableSpace()
			->addTablePrimary($recent_pass_case)
			->addTableSecondary($recent_pass_data)
			->addTableSecondary($all_pass)
			->addTableSecondary($orgus)
			->addTableSecondary($usr)
			->setRootTable($recent_pass_case)
			->addDependency($this->tf->TableJoin($recent_pass_case,$all_passes,
				$recent_pass_cases->field('usr_id')->EQ($all_passes->field('usr_id'))
					->_AND($recent_passes->field('obj_id')->EQ($all_passes->field('obj_id'))))
				)
			->addDependency($this->tf->TableJoin($recent_pass_case,$usr,
				$recent_pass_cases->field('usr_id')->EQ($usr->field('user_id')))
				)
			->addDependency($this->tf->TableJoin($recent_pass_case,$orgu,
				$recent_pass_cases->field('usr_id')->EQ($orgu->field('usr_id')))
				)
			->addDependency($this->tf->TableJoin($recent_pass_case,$recent_pass_data,
				$recent_pass_cases->field('usr_id')->EQ($all_passes->field('usr_id'))
					->_AND($recent_pass_case->field('obj_id')->EQ($recent_pass_data->field('obj_id')))
					->_AND($recent_pass_case->field('pass')->EQ($recent_pass_data->field('pass'))))
				)
			->groupBy($recent_pass_case->field('usr_id'))
			->groupBy($recent_pass_case->field('obj_id'));
	}
}