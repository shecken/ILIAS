<?php

trait ilObjCategoryExtension {
	public function CategoryDB() {
		if($this->category_db === null) {
			global $DIC;
			require_once("Services/TMS/Category/CategoryDB.php");
			$this->category_db = new CategoryDB($DIC->database());
		}

		return $this->category_db;
	}

	/**
	 * Should the cockpit be displayed in the cockpit
	 *
	 * @return bool
	 */
	public function getShowInCockpit() {
		return $this->tms_settings->getShowInCockpit();
	}

	/**
	 * Should the cockpit be displayed in the cockpit
	 *
	 * @param bool 	$show_in_cockpit
	 *
	 * @return void
	 */
	public function setShowInCockpit($show_in_cockpit) {
		$this->tms_settings = $this->tms_settings->withShowInCockpit($show_in_cockpit);
	}

	/**
	 * Update the TMS special settings
	 *
	 * @return void
	 */
	protected function updateTMSSettings() {
		$this->CategoryDB()->upsert($this->tms_settings);
	}

	/**
	 * Selects the TMS special settings
	 *
	 * @return void
	 */
	protected function selectTMSSettings() {
		$this->tms_settings = $this->CategoryDB()->selectFor((int)$this->getId());
	}
}