<?php

namespace CaT\Plugins\ReportVAPass;

class ilActions
{
	const F_TITLE = "title";
	const F_DESCRIPTION = "description";
	const F_SP_NODE_REF_ID = "sp_node_ref_id";
	const F_ONLINE = "is_online";

	public function __construct(\ilObjReportVAPass $obj, $db)
	{
		$this->obj = $obj;
		$this->obj_id = $this->obj->getId();
		$this->db = $db;
	}

	public function create(array $post)
	{
		$sp_node_ref_id = $post[self::F_SP_NODE_REF_ID];

		$new_va_pass = new Settings\VAPass($this->obj_id, $sp_node_ref_id);
		$this->db->insert($new_va_pass);

		return $new_va_pass;
	}

	public function read()
	{
		return $this->db->read($this->obj_id);
	}

	public function update(Settings\VAPass $va_pass)
	{
		$this->db->update($va_pass);
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

		return new Settings\VAPass($this->obj_id, $sp_node_ref_id, $online);
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
}
