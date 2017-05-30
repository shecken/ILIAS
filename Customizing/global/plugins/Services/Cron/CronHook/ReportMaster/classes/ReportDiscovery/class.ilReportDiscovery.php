<?php

use CaT\Plugins\ReportMaster\ReportDiscovery as RD;

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
		$report_base_plugins = $this->getReportPlugins();

		$obj_data = array();
		foreach ($report_base_plugins as $plugin) {
			assert('$plugin instanceof ilReportBasePlugin');

			// this actually is the object type
			$type = $plugin->getId();
			$icon = ilRepositoryObjectPlugin::_getIcon($type, "small");

			$obj_data[] = array_map(function ($data) use ($icon, $type) {
					// adjust data to fit the documentation.
					$data["obj_id"] = $data["id"];
					unset($data["id"]);
					$data["icon"] = $icon;
					$data['type'] = $type;
					return $data;
											// second parameter is $a_omit_trash
			}, ilObject::_getObjectsDataForType($type, true));
		}

		return call_user_func_array("array_merge", $obj_data);
	}

	public function getReportPlugins()
	{
		$report_base_plugins = $this->filterPlugins($this->getPlugins());

		//return empty array if there are no report plug ins
		if (empty($report_base_plugins)) {
			return array();
		}
		return $report_base_plugins;
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
	 *
	 * @param	ilObjUser	$usr
	 * @return	MenuItemCollection
	 */
	public function getVisibleReportItemsForUser(ilObjUser $user)
	{
		$grouped_reports = [];
		foreach ($this->getVisibleReportsObjectData($user) as $report_data) {
			$type = $report_data['type'];
			if (!array_key_exists($type, $grouped_reports)) {
				$grouped_reports[$type] = [];
			}
			$grouped_reports[$type][] = $report_data;
		}

		$coll = new RD\MenuItemCollection();
		foreach ($grouped_reports as $type => $reports) {
			if (count($reports) > 1) {
				$plugin = ilPlugin::getRepoPluginObjectByType($type);
				$plugin->loadLanguageModule();
				$coll = $coll->withMenuItem(new RD\Group($plugin->txt('objs_'.$type), ['type' => $type]));
			} elseif (count($reports) === 1) {
				$report = current($reports);
				$coll = $coll->withMenuItem(new RD\Report($report['title'], ['type' => $type, 'ref_id' => $report['ref_id']]));
			}
		}
		return $coll;
	}

	public function getVisibleReportItemsByType($type)
	{
		assert('is_string($type)');
		$reports = [];
		foreach ($this->getVisibleReportsObjectData($user) as $report_data) {
			if ($type === $report_data['type']) {
				$reports[] = $report_data;
			}
		}
		$coll = new RD\MenuItemCollection();
		foreach ($reports as $report) {
			$coll->withMenuItem(new RD\Report($report['title'], ['type' => $type, 'ref_id' => $report['ref_id']]));
		}
		return $coll;
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
								return $p_a->getPluginObject($c_type, $c_name, $slot_id, $plugin_name);
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
