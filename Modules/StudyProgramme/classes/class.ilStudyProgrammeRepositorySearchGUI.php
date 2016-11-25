<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

require_once("./Services/Search/classes/class.ilRepositorySearchGUI.php");

/**
* Custom repository search gui class for study programme to make it possible
* to get a handle on users selected in the repository search gui.
*
* @author	Richard Klees
* @version	$Id$
*/
class ilStudyProgrammeRepositorySearchGUI extends ilRepositorySearchGUI {
	public function addUser() {
		$class = $this->callback['class'];
		$method = $this->callback['method'];
		
		// call callback if that function does give a return value => show error message
		// listener redirects if everything is ok.
		$class->$method($_POST['user']);
		
		// Removed this from overwritten class, as we do not want to show the
		// results again...
		//$this->showSearchResults();
	}

	/**
	 * This is just the same as in the parent class, except for the hardcoded class name.
	 */
	public static function fillAutoCompleteToolbar($parent_object, ilToolbarGUI $toolbar = null, $a_options = array(), $a_sticky = false)
	{
		global $ilToolbar, $lng, $ilCtrl, $tree;

		if(!$toolbar instanceof ilToolbarGUI)
		{
			$toolbar = $ilToolbar;
		}

		// Fill default options
		if(!isset($a_options['auto_complete_name']))
		{
			$a_options['auto_complete_name'] = $lng->txt('obj_user');
		}
		if(!isset($a_options['auto_complete_size']))
		{
			$a_options['auto_complete_size'] = 15;
		}
		if(!isset($a_options['submit_name']))
		{
			$a_options['submit_name'] = $lng->txt('btn_add');
		}

		$ajax_url = $ilCtrl->getLinkTargetByClass(array(get_class($parent_object),'ilStudyProgrammeRepositorySearchGUI'), 
			'doUserAutoComplete', '', true,false);

		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ul = new ilTextInputGUI($a_options['auto_complete_name'], 'user_login');
		$ul->setDataSource($ajax_url);		
		$ul->setSize($a_options['auto_complete_size']);
		$toolbar->addInputItem($ul, true);

		if(count((array) $a_options['user_type']))
		{
			include_once './Services/Form/classes/class.ilSelectInputGUI.php';
			$si = new ilSelectInputGUI("", "user_type");
			$si->setOptions($a_options['user_type']);
			$toolbar->addInputItem($si);
		}

		$toolbar->addFormButton($a_options['submit_name'], 'addUserFromAutoComplete');

		if((bool)$a_options['add_search'] || 
			is_numeric($a_options['add_from_container']))
		{
			$lng->loadLanguageModule("search");

			$toolbar->addSeparator();

			if((bool)$a_options['add_search'])
			{
				$toolbar->addButton($lng->txt("search_users"), $ilCtrl->getLinkTargetByClass('ilStudyProgrammeRepositorySearchGUI',''));
			}

			if(is_numeric($a_options['add_from_container']))
			{
				$parent_ref_id = (int)$a_options['add_from_container'];
				$parent_container_ref_id = $tree->checkForParentType($parent_ref_id, "grp");
				$parent_container_type = "grp";
				if(!$parent_container_ref_id)
				{
					$parent_container_ref_id = $tree->checkForParentType($parent_ref_id, "crs");
					$parent_container_type = "crs";
				}
				if($parent_container_ref_id)
				{
					if((bool)$a_options['add_search'])
					{
						$toolbar->addSpacer();
					}

					$ilCtrl->setParameterByClass('ilStudyProgrammeRepositorySearchGUI', "list_obj", ilObject::_lookupObjId($parent_container_ref_id));
					$toolbar->addButton("search_add_members_from_container_".$parent_container_type, $ilCtrl->getLinkTargetByClass(array(get_class($parent_object),'ilStudyProgrammeRepositorySearchGUI'), 'listUsers'));
				}
			}
		}

		$toolbar->setFormAction(
			$ilCtrl->getFormActionByClass(
				array(
					get_class($parent_object),
					'ilStudyProgrammeRepositorySearchGUI')
			)
		);
		return $toolbar;
	}
}