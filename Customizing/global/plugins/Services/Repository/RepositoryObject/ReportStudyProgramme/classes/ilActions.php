<?php
namespace CaT\Plugins\ReportStudyProgramme;

require_once("Services/Object/classes/class.ilObject.php");

class ilActions
{
	const F_TITLE = "title";
	const F_DESCRIPTION = "description";
	const F_SP_NODE_REF_ID = "sp_node_ref_id";
	const F_ONLINE = "is_online";

	public function __construct(\ilObjReportStudyProgramme $obj, $db)
	{
		$this->obj = $obj;
		$this->obj_id = $this->obj->getId();
		$this->db = $db;
	}

	public function create($sp_node_ref_id)
	{
		$new_xsp = new Settings\ReportStudyProgramme($this->obj_id, $sp_node_ref_id);
		$this->db->insert($new_xsp);

		return $new_xsp;
	}

	public function read()
	{
		return $this->db->read($this->obj_id);
	}

	public function update(Settings\ReportStudyProgramme $xsp_pass)
	{
		$this->db->update($xsp_pass);
	}

	public function delete()
	{
		$this->db->delete($this->obj_id);
	}

	public function getObject()
	{
		return $this->obj;
	}

	public function getSettingsFromArray($post)
	{
		$sp_node_ref_id = (int)$post[self::F_SP_NODE_REF_ID];
		$online = (bool)$post[self::F_ONLINE];

		return new Settings\ReportStudyProgramme($this->obj_id, $sp_node_ref_id, $online);
	}

	public function updateObjectFromArray(array $post)
	{
		$title = $post[self::F_TITLE];
		$description = $post[self::F_DESCRIPTION];

		$this->obj->setTitle($title);
		$this->obj->setDescription($description);

		$new_settings = $this->getSettingsFromArray($post);
		$this->obj->setSettings($new_settings);

		$this->obj->update();
	}


	public function isSPId($id)
	{
		if (\ilObject::_lookupType($id, true) == "prg") {
			return true;
		}
		return false;
	}
}
