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
					$visible_reports[$key.'.'.$ref_id] = $report;
				}
			}
		}

		ksort($visible_reports, SORT_NATURAL | SORT_FLAG_CASE);
		return $visible_reports;
	}


	/**
	 * Get an Item Collection for all the visible reports of a user.
	 * There will be no groupings. This should serve as a transition
	 * from current logic.
	 *
	 * @param	ilObjUser	$usr
	 * @return	MenuItemCollection
	 */
	public function getVisibleReportItemsForUserUngrouped(ilObjUser $user)
	{
		$coll = new RD\MenuItemCollection();
		foreach ($this->getVisibleReportsObjectData($user) as $report_data) {
			$coll = $coll->withMenuItem(
				new RD\Report(
					$report_data['title'],
					['type' =>  $report_data['type'], 'ref_id' => $report_data['ref_id']],
					$report_data['icon']
				)
			);
		}
		return $coll;
	}

	/**
	 * Get an Item Collection containing all Reports visible by a user.
	 * If a report type happens to occur several times in the list, it will be grouped.
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

	/**
	 * Get an Item Collection containing all Reports visible by a user corresponding to
	 * some particular type.
	 *
	 * @param	ilObjUser	$usr
	 * @return	MenuItemCollection
	 */
	public function getVisibleReportItemsByType($type, ilObjUser $user)
	{
		assert('is_string($type)');
		$reports = [];

		$coll = new RD\MenuItemCollection();
		foreach ($this->getVisibleReportsObjectData($user) as $report_data) {
			if ($type === $report_data['type']) {
				$object = ilObjectFactory::getInstanceByRefId($report_data['ref_id']);

				if ($object->showInReportMenu()) {
					$coll = $coll->withMenuItem(new RD\Report($object->getReportMenuTitle(), ['type' => $type, 'ref_id' => $report_data['ref_id']]));
				}
			}
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
				&& !($plugin instanceof ilReportEduBioPlugin)
				) {
				return true;
			}
			return false;
		});
	}
}
