<?php

// TODO: add docstring
class ilIndividualPlanEntry
{
	// TODO: add docstrings
	protected $title;
	protected $sp_ref_id;
	protected $sp_obj_id;
	protected $status;
	protected $finish_until;
	protected $finished;
	protected $has_lp_children;
	protected $has_children;

	// TODO: make this an immutable object.

	public function setTitle($title)
	{
		assert('is_string($title)');
		$this->title = $title;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function setObjId($obj_id)
	{
		assert('is_integer($obj_id)');
		$this->sp_obj_id = $obj_id;
	}

	public function getObjId()
	{
		return $this->sp_obj_id;
	}

	public function setRefId($ref_id)
	{
		assert('is_integer($ref_id)');
		$this->sp_ref_id = $ref_id;
	}

	public function getRefId()
	{
		return $this->sp_ref_id;
	}

	public function setStatus($status)
	{
		$this->status = $status;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setHasLpChildren($has_lp_children)
	{
		$this->has_lp_children = $has_lp_children;
	}

	public function getHasLpChildren($has_lp_children)
	{
		return $this->has_lp_children;
	}

	public function setHasChildren($has_children)
	{
		$this->has_children = $has_children;
	}

	public function getHasChildren($has_children)
	{
		return $this->has_children;
	}

	public function setFinished($finished)
	{
		$this->finished = $finished;
	}

	public function getFinished()
	{
		return $this->finished;
	}

	public function setFinishUntil(ilDate $finish_until)
	{
		$this->finish_until = $finish_until;
	}

	public function getFinishUntil()
	{
		return $this->finish_until;
	}
}
