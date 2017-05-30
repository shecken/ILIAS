<?php


require_once 'Services/Repository/classes/class.ilRepositoryObjectPlugin.php';

class ilReportDiscovery
{

	protected $plugin_admin;
	protected $access;

	public function __construct($plugin_admin, $access)
	{
		$this->plugin_admin = $plugin_admin;
		$this->access = $access;
	}

	/**
	 * Get a list with object data (obj_id, title, type, description, icon_small) of all
	 * Report Objects in the system that are not in the trash. The id is
	 * the obj_id, not the ref_id.
	 *
	 * @return array
	 */
	public function getReportsObjectData()
	{


		$plugins = $this->getPlugins();
		$report_base_plugins = $this->filterPlugins($plugins);

		//return empty array if there are no report plug ins
		if (empty($report_base_plugins)) {
			return array();
		}

		$obj_data = array();
		foreach ($report_base_plugins as $plugin) {
			assert('$plugin instanceof ilReportBasePlugin');

			// this actually is the object type
			$type = $plugin->getId();

			$icon = ilRepositoryObjectPlugin::_getIcon($type, "small");

			$obj_data[] = array_map(function (&$data) use (&$icon) {
					// adjust data to fit the documentation.
					$data["obj_id"] = $data["id"];
					unset($data["id"]);
					$data["icon"] = $icon;
					return $data;
											// second parameter is $a_omit_trash
			}, ilObject::_getObjectsDataForType($type, true));
		}

		return call_user_func_array("array_merge", $obj_data);
	}

	/**
	 * Get a list of all reports visible to the given user. Returns a list with entries
	 * title.obj_id => (obj_id, title, type, description, icon). If a report is visible
	 * via two different ref_ids only one of those will appear in the result.
	 *
	 * @param	ilObjUser $user
	 * @return	array
	 */
	public function getVisibleReportsObjectData(ilObjUser $user)
	{

		$reports = $this->getReportsObjectData();

		$visible_reports = array();

		foreach ($reports as $key => &$report) {
			$obj_id = $report["obj_id"];
			foreach (ilObject::_getAllReferences($report["obj_id"]) as $ref_id) {
				if ($this->access->checkAccessOfUser($user->getId(), "read", null, $ref_id)) {
					$report["ref_id"] = $ref_id;
					$visible_reports[$key] = $report;
					break;
				}
			}
		}

		ksort($visible_reports, SORT_NATURAL | SORT_FLAG_CASE);
		return $visible_reports;
	}

	/**
	 * plugin objects
	 *
	 * @return array
	 */
	protected function getPlugins()
	{

		$c_type = ilRepositoryObjectPlugin::getComponentType();
		$c_name = ilRepositoryObjectPlugin::getComponentName();
		$slot_id = ilRepositoryObjectPlugin::getSlotId();
		$p_a = $this->plugin_admin;
		$plugin_names = $p_a->getActivePluginsForSlot($c_type, $c_name, $slot_id);

		return array_map(function ($plugin_name) use ($p_a, $c_type, $c_name, $slot_id) {
								$plugin = $p_a->getPluginObject($c_type, $c_name, $slot_id, $plugin_name);

			if ($plugin instanceof ilReportBasePlugin) {
				return $plugin;
			}
		}, $plugin_names);
	}

	/**
	 * filterd plugins for ilReportBasePlugin
	 *
	 * @param array $plugins
	 *
	 * @return array
	 */
	protected function filterPlugins($plugins)
	{
		return array_filter($plugins, function ($plugin) {
			if ($plugin instanceof ilReportBasePlugin
				&& !($plugin instanceof ilReportExamBioPlugin)
				&& !($plugin instanceof ilReportEduBioPlugin)
				) {
				return true;
			}
			return false;
		});
	}
}
