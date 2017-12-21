<?php
require_once("Services/TMS/Roles/classes/class.ilTMSRoleSettings.php");

/**
 * Reads and writes settings into the database
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-traning.de>
 */
class ilTMSRolesDB {
	const TABLE_NAME = "tms_role_settings";

	/**
	 * @var ilDBInterface
	 */
	protected $db;

	public function __construct(ilDBInterface $db) {
		$this->db = $db;
	}

	/**
	 * Create the tms role settings
	 *
	 * @param int 	$role_id
	 *
	 * @return void
	 */
	public function create($role_id) {
		assert('is_int($role_id)');
		$settings = new ilTMSRoleSettings($role_id, false, false);

		$columns = array(
			"role_id" => array("integer", $settings->getRoleId()),
			"hide_breadcrumb" => array("integer", $settings->getHideBreadcrumb()),
			"hide_menu_tree" => array("integer", $settings->getHideMenuTree())
		);

		$this->getDB()->insert(self::TABLE_NAME, $columns);

		return $settings;
	}

	/**
	 * Updates the tms role settings
	 *
	 * @param ilTMSRoleSettings 	$settings
	 *
	 * @return void
	 */
	public function update(ilTMSRoleSettings $settings) {
		$where = array("role_id" => array("integer", $settings->getRoleId()));

		$columns = array(
			"hide_breadcrumb" => array("integer", $settings->getHideBreadcrumb()),
			"hide_menu_tree" => array("integer", $settings->getHideMenuTree())
		);

		$this->getDB()->replace(self::TABLE_NAME, $where, $columns);
	}

	/**
	 * Get the tms role settings for
	 *
	 * @param int 	$role_id
	 *
	 * @return ilTMSRoleSettings
	 */
	public function selectFor($role_id) {
		assert('is_int($role_id)');
		$query = "SELECT hide_breadcrumb, hide_menu_tree".PHP_EOL
				." FROM ".self::TABLE_NAME.PHP_EOL
				." WHERE role_id = ".$this->getDB()->quote($role_id, "integer");

		$res = $this->getDB()->query($query);
		$row = $this->getDB()->fetchAssoc($res);

		return new ilTMSRoleSettings($role_id, (bool)$row["hide_breadcrumb"], (bool)$row["hide_menu_tree"]);
	}

	/**
	 * Delete settings for
	 *
	 * @param int 	$role_id
	 *
	 * @return void
	 */
	public function deleteFor($role_id) {
		assert('is_int($role_id)');
		$query = "DELETE FROM ".self::TABLE_NAME.PHP_EOL
				." WHERE role_id = ".$this->getDB()->quote($role_id, "integer");

		$this->getDB()->manipulate($query);
	}

	/**
	 * Get intance of db
	 *
	 * @throws \Exception
	 *
	 * @return \ilDBInterface
	 */
	protected function getDB()
	{
		if (!$this->db) {
			throw new \Exception("no Database");
		}
		return $this->db;
	}
}