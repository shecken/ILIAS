<?php
require_once 'Services/Repository/classes/class.ilObjectPlugin.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catReportTable.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catReportOrder.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catReportQuery.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catReportQueryOn.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilter.php';
require_once 'Services/GEV/Utils/classes/class.gevUserUtils.php';
require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.reportSettingsDataHandler.php");
require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.settingFactory.php");
require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/class.ilReportMasterPlugin.php");
/**
* This class performs all interactions with the database in order to get report-content. Puplic methods may be accessed in 
* in the GUI via $this->object->{method-name}.
*/
use CaT\TableRelations as TableRelations;
use CaT\Filter as Filters;
abstract class ilObjReportBase2 extends ilObjectPlugin{

	const URL_PREFIX = "https://";
	
	public function __construct($a_ref_id = 0) {
		global $ilUser;
		$this->gUser = $ilUser;
		parent::__construct($a_ref_id);
		global $ilDB, $ilUser, $tree;
		$this->user_utils = gevUserUtils::getInstanceByObj($ilUser);
		$this->gIldb = $ilDB;
		$this->gTree = $tree;
		$this->table = null;
		$this->query = null;
		$this->data = false;
		$this->filter = null;
		$this->order = null;

		$this->sf = new settingFactory($this->gIldb);
		$this->master_plugin = new ilReportMasterPlugin();
		$this->settings = array();
		$this->createLocalReportSettings();
		$this->createGlobalReportSettings();
		$this->settings_data_handler = $this->sf->reportSettingsDataHandler();

		$this->validateUrl = new \CaT\Validate\ValidateUrl;
		$this->gf = new TableRelations\GraphFactory();
		$this->pf = new Filters\PredicateFactory();
		$this->tf = new TableRelations\TableFactory($this->pf, $this->gf);
	}

	/**
	 * create the settings, that are relevant for self only
	 */
	abstract protected function createLocalReportSettings();

	protected function createGlobalReportSettings() {

		$this->global_report_settings =
			$this->sf->reportSettings('rep_master_data')
				->addSetting($this->sf
								->settingBool('is_online', $this->master_plugin->txt('is_online'))
								)
				->addSetting($this->sf
								->settingString('pdf_link', $this->master_plugin->txt('rep_pdf_desc'))
									->setFromForm(function ($string) {
										$string = trim($string);
										if($string === "" || $this->validateUrl->validUrlPrefix($string)) {
											return $string;
										}
										return self::URL_PREFIX.$string;
									})
								)
				->addSetting($this->sf
								->settingString('video_link', $this->master_plugin->txt('rep_video_desc'))
									->setFromForm(function ($string) {
										$string = trim($string);
										if($string === "" || $this->validateUrl->validUrlPrefix($string)) {
											return $string;
										}
										return self::URL_PREFIX.$string;
									})
								)
				->addSetting($this->sf
								->settingRichText('tooltip_info', $this->master_plugin->txt('rep_tooltip_desc'))
								);
	}

	/**
	 * configure table for the report according
	 * to local settings and maybe other parameters
	 */
	abstract public function prepareTable(catSelectableReportTableGUI $table);

	/**
	 * configure filter for the report according
	 * to local settings and maybe other parameters
	 */
	abstract public function filter();

	/**
	 * define all the tables relevant for report and
	 * relations between them
	 */
	abstract public function initSpace();

	/**
	 * fetch the sql statement.
	 */
	public function buildQueryStatement() {	
		return $this->getInterpreter()->getSql($this->space->query());
	}

	/**
	 * fetch the query interpreter for the query object
	 * spawned by the space.
	 */
	protected function getInterpreter() {
		if(!$this->interpreter) {
			$this->interpreter = new TableRelations\SqlQueryInterpreter( new Filters\SqlPredicateInterpreter($this->gIldb), $this->pf, $this->gIldb);
		}
		return $this->interpreter;
	}

	/**
	 * query the database and postprocess results using some callback
	 */
	public function deliverData(callable $callable) {
		$res = $this->gIldb->query($this->getInterpreter()->getSql($this->space->query()));
		$return = array();
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$return[] = call_user_func($callable,$rec);
		}
		return $return;
	}

	/**
	 * which parameters are relevant for this report and should be
	 * passed on to sub gui-calls and links insisde report
	 */
	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

	/**
	 * get an associative array of settings for the class instance
	 */
	public function getSettingsData() {
		return $this->settings;
	}

	/**
	 * get a certain setting
	 */
	public function getSettingsDataFor($key) {
		if(!array_key_exists($key, $this->settings)) {
			throw new Exception("ilObjReportBase::getSettingsDataFor: key ".$key." not found in settings.");
		}

		return $this->settings[$key];
	}

	public function setSettingsData(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * add a parameter to relevant parameters referenced to by a key
	 */
	public function addRelevantParameter($key, $value) {
		$this->relevant_parameters[$key] = $value;
	}

		/**
	 * Stores query results to an array after postprocessing with callback
	 *
	 * @param	callable	$callback
	 * @return	sting|int[]	$data
	 */
	protected function fetchData(callable $callback) {
		if ($this->query === null) {
			throw new Exception("catBasicReportGUI::fetchData: query not defined.");
		}
		$query = $this->buildQueryStatement();
		$res = $this->gIldb->query($query);
		$data = array();

		while($rec = $this->gIldb->fetchAssoc($res)) {
			$data[] = call_user_func($callback,$rec);
		}

		return $data;
	}

	/**
	 * interaction with storage
	 */
	final public function doCreate() {
		$this->settings_data_handler->createObjEntry($this->getId(), $this->global_report_settings);
		$this->settings_data_handler->createObjEntry($this->getId(), $this->local_report_settings);
	}

	final public function doRead() {
		$this->settings = array_merge($this->settings_data_handler->readObjEntry($this->getId(), $this->global_report_settings),
							$this->settings_data_handler->readObjEntry($this->getId(), $this->local_report_settings));
	}

	final public function doUpdate() {
		$this->settings_data_handler->updateObjEntry($this->getId(), $this->global_report_settings,$this->settings);
		$this->settings_data_handler->updateObjEntry($this->getId(), $this->local_report_settings,$this->settings);
	}

	final public function doDelete() {
		$this->settings_data_handler->deleteObjEntry($this->getId(), $this->global_report_settings);
		$this->settings_data_handler->deleteObjEntry($this->getId(), $this->local_report_settings);
	}

	final public function doCloneObject($new_obj,$a_target_id,$a_copy_id) {
		$new_obj->settings = $this->settings;
		$new_obj->setDescription($this->getDescription());
		$new_obj->update();
	}

	/**
	 * Get a list with object data (obj_id, title, type, description, icon_small) of all
	 * Report Objects in the system that are not in the trash. The id is
	 * the obj_id, not the ref_id.
	 *
	 * @return array
	 */
	static public function getReportsObjectData() {
		require_once("Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

		$plugins = self::getPlugins();
		$report_base_plugins = self::filterPlugins($plugins);

		//return empty array if there are no report plug ins
		if(empty($report_base_plugins)) {
			return array();
		}

		$obj_data = array();
		foreach ($report_base_plugins as $plugin) {
			assert('$plugin instanceof ilReportBasePlugin');

			// this actually is the object type
			$type = $plugin->getId();

			$icon = ilRepositoryObjectPlugin::_getIcon($type, "small");

			$obj_data[] = array_map(function(&$data) use (&$icon) {
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
	static public function getVisibleReportsObjectData(ilObjUser $user) {
		require_once("Services/Object/classes/class.ilObject.php");

		global $ilAccess;

		$reports = self::getReportsObjectData();

		$visible_reports = array();

		foreach ($reports as $key => &$report) {
			$obj_id = $report["obj_id"];
			$type = $report["type"];
			foreach (ilObject::_getAllReferences($report["obj_id"]) as $ref_id) {
				if ($ilAccess->checkAccessOfUser($user->getId(), "read", null, $ref_id)) {//, $type, $obj_id)) {
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
	* We may need to locate the report inside the tree, so it is possible to perform local evaluations.
	* look for the first parent object of specific @param (string)type,
	* or the first parent object, if no type given. @return array(obj_id => id, ref_id => id).
	*/
	protected function getParentObjectOfTypeIds($type = null) {
		return $this->getParentObjectOfObjOfTypeIds($this->getRefId(), $type);
	}

		/**
	* We may need to locate the report inside the tree, so it is possible to perform local evaluations.
	* look for the first parent object of specific @param (string)type,
	* or the first parent object, if no type given. @return array(obj_id => id, ref_id => id).
	*/
	protected function getParentObjectOfObjOfTypeIds($ref_id, $type = null) {
		$data = $this->gTree->getParentNodeData($ref_id);
		while( null !== $type && $type !== $data['type'] && (string)ROOT_FOLDER_ID !== (string)$data['ref_id'] ) {
			$data = $this->gTree->getParentNodeData($data['ref_id']);
		}
		return (null === $type || $type === $data['type'] )
			? array('obj_id' => $data['obj_id'], 'ref_id' => $data['ref_id']) : array();
	}

	/**
	* It seems to be a common problem to ev2aluate certain types in a subtree.
	*/
	protected function getSubtreeTypeIdsBelowParentType($subtree_type,$parent_type) {
		$parent_cat_ref_id = $this->getParentObjectOfTypeIds($parent_type)['ref_id'];
		if($parent_cat_ref_id === null) {
			return array();
		}
		$subtree_nodes_data = $this->gTree->getSubTree(
			$this->gTree->getNodeData($parent_cat_ref_id),true, $subtree_type);
		$return = array();
		foreach ($subtree_nodes_data as $node) {
			$return[] = $node["obj_id"];
		}
		return $return;
	}

	/**
	 * plugin objects
	 *
	 * @return array
	 */
	protected static function getPlugins() {
		global $ilPluginAdmin;

		$c_type = ilRepositoryObjectPlugin::getComponentType();
		$c_name = ilRepositoryObjectPlugin::getComponentName();
		$slot_id = ilRepositoryObjectPlugin::getSlotId();
		$plugin_names = $ilPluginAdmin->getActivePluginsForSlot($c_type, $c_name, $slot_id);

		return array_map(function($plugin_name) use ($ilPluginAdmin, $c_type, $c_name, $slot_id) {
								$plugin = $ilPluginAdmin->getPluginObject($c_type, $c_name, $slot_id, $plugin_name);

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
	protected static function filterPlugins($plugins) {
		return array_filter($plugins, function($plugin) {
			if ($plugin instanceof ilReportBasePlugin
				&& !($plugin instanceof ilReportExamBioPlugin)
				&& !($plugin instanceof ilReportEduBioPlugin)
				) {
				return true;
			}
			return false;
		});
	}

	public function titile() {
		return parent::getTitle();
	}

	public function description() {
		return parent::getDescription();
	}
}