<?php

require_once "Services/Repository/classes/class.ilObjectPlugin.php";
require_once "Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php";

abstract class ilOrgUnitExtension extends ilObjectPlugin {

	/**
	 * @var ilObjOrgUnitTree
	 */
	protected $orguTree;

	/**
	 * @var int
	 */
	protected $parentRefId;

	/**
	 * @var ilTree
	 */
	protected $tree;

	/**
	 * ilOrgUnitExtension constructor.
	 * @param int $a_ref_id
	 */
	public function __construct($a_ref_id = 0) {
		global $tree;

		parent::__construct($a_ref_id);
		$this->orguTree = ilObjOrgUnitTree::_getInstance();
		$this->parent_ref_id = $tree->getParentId($a_ref_id?$a_ref_id:$_GET['ref_id']);
		$this->tree = $tree;
	}

	/**
	 * @return null|object
	 * @throws ilPluginException
	 */
	protected function getPlugin() {
		if(!$this->plugin) {
			$this->plugin =
				ilPlugin::getPluginObject(IL_COMP_MODULE, "OrgUnit", "orguext",
					ilPlugin::lookupNameForId(IL_COMP_MODULE, "OrgUnit", "orguext", $this->getType()));
			if (!is_object($this->plugin)) {
				throw new ilPluginException("ilOrgUnitExtension: Could not instantiate plugin object for type " . $this->getType() . ".");
			}
		}
		return $this->plugin;
	}

	/**
	 * @param $ref_id returns all employees of the given org unit.
	 * @param bool $recursively include all employees in the suborgunits
	 * @return int[]
	 */
	public function getEmployees($ref_id, $recursively = false) {
		return $this->orguTree->getEmployees($ref_id, $recursively);
	}

	/**
	 * Get the IDs of the employees of the org unit this plugin belongs to.
	 * @param bool $recursively
	 * @return int[]
	 */
	public function getMyEmployees($recursively = false) {
		return $this->getEmployees($this->parentRefId, $recursively);
	}

	/**
	 * @param $ref_id
	 * @param bool $recursively
	 * @return int[]
	 */
	public function getSuperiors($ref_id, $recursively = false) {
		return $this->orguTree->getSuperiors($ref_id, $recursively);
	}

	/**
	 * @param bool $recursively
	 * @return int[]
	 */
	public function getMySuperiors($recursively = false) {

		return $this->getSuperiors($this->parentRefId, $recursively);
	}

	/**
	 * @return ilObjOrgUnit
	 */
	public function getOrgUnit() {
		return new ilObjOrgUnit($this->parentRefId);
	}

	/**
	 * @return int[] RefIds from the root OrgUnit to the underlying OrgUnit
	 */
	public function getOrgUnitPathRefIds() {
		$path = array();
		foreach ($this->getOrgUnitPath() as $node) {
			$path[] = $node['child'];
		}
		return $path;
	}

	/**
	 *
	 * @return array Returns the path to the underlying OrgUnit starting with the root OrgUnit. The array are nodes of the global $tree.
	 */
	public function getOrgUnitPath() {
		return $this->tree->getPathFull($this->parent_ref_id, ilObjOrgUnit::getRootOrgRefId());
	}

	/**
	 * @return string[] Returns the titles to the underlying OrgUnit starting with the root OrgUnit.
	 */
	public function getOrgUnitPathTitles() {
		$titles = array();
		foreach ($this->getOrgUnitPath() as $node) {
			if ($node["title"] == "__OrgUnitAdministration") {
				$node["title"] = $this->lng->txt("objs_orgu");
			}
			$titles[] = $node['title'];
		}
		return $titles;
	}

	/**
	 * @param bool $with_data if this is set to true, only the ids are delivered
	 * @param string $type what type are you looking for?
	 * @return array
	 */
	public function getOrgUnitSubtree($with_data = true, $type = "") {
		$node = $this->tree->getNodeData($this->parent_ref_id);
		return $this->tree->getSubTree($node, $with_data, $type);
	}
}
