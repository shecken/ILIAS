<?php
namespace CaT\Plugins\ReportStudyProgramme\Settings;

/**
 * Class for the VA Pass Settings
 */
class ReportStudyProgramme
{
	/**
	 * @var int
	 */
	protected $obj_id;

	/**
	 * @var boolean
	 */
	protected $online;

	/**
	 * @var int
	 */
	protected $sp_node_ref_id;

	public function __construct($obj_id, $sp_node_ref_id, $online = false)
	{
		assert('is_int($obj_id)');
		assert('is_bool($online)');
		assert('is_int($sp_node_ref_id)');
		$this->obj_id = $obj_id;
		$this->online = $online;
		$this->sp_node_ref_id = $sp_node_ref_id;
	}

	/**
	 * Get the object id
	 *
	 * @return int
	 */
	public function getObjId()
	{
		return $this->obj_id;
	}

	/**
	 * Get the online status
	 *
	 * @return boolean
	 */
	public function getOnline()
	{
		return $this->online;
	}

	/**
	 * Set the online status and get a cloned object
	 *
	 * @param boolean 	$online
	 *
	 * @return ReportStudyProgramme
	 */
	public function withOnline($online)
	{
		assert('is_bool($online)');
		$clone = clone $this;
		$clone->online = $online;
		return $clone;
	}

	/**
	 * Get ref id of the a sp node
	 *
	 * @return int
	 */
	public function getSPNodeRefId()
	{
		return $this->sp_node_ref_id;
	}

	/**
	 * Set the sp node ref id and get a cloned object
	 *
	 * @param int 	$sp_node_ref_id
	 *
	 * @return ReportStudyProgramme
	 */
	public function withSPNodeRefId($sp_node_ref_id)
	{
		assert('is_int($sp_node_ref_id)');
		$clone = clone $this;
		$clone->sp_node_ref_id = $sp_node_ref_id;
		return $clone;
	}
}
