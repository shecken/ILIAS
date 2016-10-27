<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.ReportSettingsException.php';
require_once 'Services/Form/classes/class.ilNumberInputGUI.php';
require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';
require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
require_once 'Services/Form/classes/class.ilHiddenInputGUI.php';

/**
 *	This class implements the logic of commmunication between a report object and a FormSettingsGui
 *	It covers writing data into a form and fetching form inputs. It also takes care of pre-
 *	or postprocessing this data by means of ToForm and FromForm closures in settings.
 */
class ReportSettingsFormHandler {
	/**
	 * Add form fields to $settings_form corresponding to settings in $settings
	 * @param	ilPropertyFormGUI	$settings_form
	 * @param	reportSettings	$settings
	 * @return	ilPropertyFormGUI	$settings_form
	 */
	public function addToForm(ilPropertyFormGUI $settings_form, ReportSettings $settings) {
		$fields = $settings->settingIds();

		foreach ($fields as $field) {
			$setting = $settings->setting($field);
			$settings_form->addItem($this->formElementForSetting($setting));
		}
	}

	/**
	 * Get form entries in some ilPropertyFormGUI
	 * @param	ilPropertyFormGUI	$settings_form
	 * @param	reportSettings	$settings
	 * @return	array	$settings_data
	 */
	public function extractValues(ilPropertyFormGUI $settings_form, ReportSettings $settings) {
		$settings_data = array();
		$fields = $settings->settingIds();

		foreach ($fields as $field) {
			$setting = $settings->setting($field);
			$form_member = $settings_form->getItemByPostVar($field);
			$settings_data[$field] = $this->extractSettingFromFormMember($setting, $form_member);
		}
		return $settings_data;
	}

	/**
	 * Adds Form fields to $settings_form corresponding to settings in $settings
	 * @param	ilPropertyFormGUI	$settings_form
	 * @param	reportSettings	$settings
	 * @return	ilPropertyFormGUI	$settings_form
	 */
	public function insertValues(array $settings_data, ilPropertyFormGUI $settings_form, ReportSettings $settings) {
		$fields = $settings->settingIds();

		foreach ($fields as $field) {
			$setting = $settings->setting($field);
			$setting_data = $settings_data[$field];
			$form_member = $settings_form->getItemByPostVar($field);
			$this->insertSettingIntoFormMember($setting_data, $setting, $form_member);
		}
		return $settings_form;
	}

	protected function formElementForSetting(Setting $setting) {
		$name = $setting->name();
		$id = $setting->id();
		if($setting instanceof SettingInt) {
			return new ilNumberInputGUI($name, $id);
		}
		if($setting instanceof SettingFloat) {
			$return = new ilNumberInputGUI($name, $id);
			$return->allowDecimals(true);
			return $return;
		}
		if($setting instanceof SettingBool) {
			$return = new ilCheckboxInputGUI($name, $id);
			$return->setValue(1);
			return $return;
		}
		if($setting instanceof SettingString) {
			return new ilTextInputGUI($name, $id);
		}
		if($setting instanceof SettingText) {
			return new ilTextAreaInputGUI($name, $id);
		}
		if($setting instanceof SettingRichText) {
			return new ilTextAreaInputGUI($name, $id);
		}
		if($setting instanceof SettingListInt) {
			$return = new ilSelectInputGUI($name, $id);
			$return->setOptions($setting->options());
			return $return;

		}
		if($setting instanceof SettingHidden) {
			return new ilHiddenInputGUI($id);
		}
		throw new ReportSettingsException("no formtype defined for setting");
	}

	protected function validSettingGUIRelation(Setting $setting, ilFormPropertyGUI $form_member_gui) {
		if($setting instanceof SettingInt && $form_member_gui instanceof ilNumberInputGUI) {
			return true;
		} elseif($setting instanceof SettingFloat && $form_member_gui instanceof ilNumberInputGUI) {
			return true;
		} elseif($setting instanceof SettingBool && $form_member_gui instanceof ilCheckboxInputGUI) {
			return true;
		} elseif($setting instanceof SettingString && $form_member_gui instanceof ilTextInputGUI) {
			return true;
		} elseif($setting instanceof SettingText && $form_member_gui instanceof ilTextAreaInputGUI) {
			return true;
		} elseif($setting instanceof SettingRichText && $form_member_gui instanceof ilTextAreaInputGUI) {
			return true;
		} elseif($setting instanceof SettingListInt && $form_member_gui instanceof ilSelectInputGUI) {
			return true;
		} elseif($setting instanceof SettingHidden && $form_member_gui instanceof ilHiddenInputGUI) {
			return true;
		} else {
			return false;
		}
	}

	protected function extractSettingFromFormMember(Setting $setting, ilFormPropertyGUI $form_member_gui) {
		assert('$this->validSettingGUIRelation($setting, $form_member_gui)');
		if($setting instanceof SettingBool && $form_member_gui instanceof ilCheckboxInputGUI) {
			return call_user_func($setting->fromForm(), $form_member_gui->getChecked());
		}
		if($setting instanceof SettingListInt && $form_member_gui instanceof  ilSelectInputGUI) {
			if(!in_array((int)$form_member_gui->getValue(), array_keys($setting->options()))) {
				throw new ReportSettingsException("unknown option");
			}
		}
		return call_user_func($setting->fromForm(),  $form_member_gui->getValue());

	}

	protected function insertSettingIntoFormMember($setting_data, Setting $setting, ilFormPropertyGUI $form_member_gui) {
		assert('$this->validSettingGUIRelation($setting, $form_member_gui)');
		if($setting instanceof SettingBool && $form_member_gui instanceof ilCheckboxInputGUI) {
			if($setting_data) {
				$form_member_gui->setChecked(true);
			}
			return;
		}
		$form_member_gui->setValue(call_user_func($setting->toForm(), $setting_data));
		return;
	}
}