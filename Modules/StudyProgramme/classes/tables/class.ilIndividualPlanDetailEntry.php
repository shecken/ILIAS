<?php

// TODO: add docstring
class ilIndividualPlanDetailEntry
{
	// TODO: add docstring
	protected $title;
	protected $accountable;
	protected $finished;
	protected $result;
	protected $type_of_pass;
	protected $status;
	protected $study_programme;
	protected $course_where_user_is_member;

	// TODO: Add a constructor that takes all the attributes and checks them
	// remove setters then.

	public function setTitle($title)
	{
		assert('is_string($title)');
		$this->title = $title;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function setAccountable($accountable)
	{
		assert('is_string($accountable)');
		$this->accountable = $accountable;
	}

	public function getAccountable()
	{
		return $this->accountable;
	}

	public function setFinished($finished)
	{
		$this->finished = $finished;
	}

	public function getFinished()
	{
		return $this->finished;
	}

	public function setResult($result)
	{
		assert('is_string($result)');
		$this->result = $result;
	}

	public function getResult()
	{
		return $this->result;
	}

	public function setTypeOfPass($type_of_pass)
	{
		assert('is_string($type_of_pass)');
		$this->type_of_pass = $type_of_pass;
	}

	public function getTypeOfPass()
	{
		return $this->type_of_pass;
	}

	public function setStatus($status)
	{
		assert('is_int($status)');
		$this->status = $status;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setStudyProgramme(\ilObjStudyProgramme $sp) {
		$this->study_programme = $sp;
	}

	public function getStudyProgramme() {
		return $this->study_programme;
	}

	public function setCourseWhereUserIsMember(\ilObjCourse $course_where_user_is_member = null) {
		$this->course_where_user_is_member = $course_where_user_is_member;
	}

	public function getCourseWhereUserIsMember() {
		return $this->course_where_user_is_member;
	}
}
