<?php

namespace CaT\Plugins\ReportVAPass;

class ilActions
{
	const F_SP_NODE_REF_ID = "sp_node_ref_id";
	const F_ONLINE = "is_online";

	public function __construct($obj_id, $db)
	{
		assert('is_int($obj_id)');
		$this->obj_id = $obj_id;
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

	public function update(array $post)
	{
		$sp_node_ref_id = $post[self::F_SP_NODE_REF_ID];
		$is_online = (bool)$post[self::F_ONLINE];

		$update_va_pass = new Settings\VAPass($this->obj_id, $sp_node_ref_id, $is_online);
		$this->db->update($update_va_pass);
	}

	public function delete()
	{
		$this->db->delete($this->obj_id);
	}
}
