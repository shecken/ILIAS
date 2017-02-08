<?php
/**
 * An object carrying settings of an Manual Assessment obj
 * beyond the standart information
 */
class ilManualAssessmentSettings
{
	const DEF_CONTENT = "";
	const DEF_RECORD_TEMPLATE = "";

	/**
	 * @var	string
	 */
	protected $content;

	/**
	 * @var	string
	 */
	protected $record_template;

	/**
	 * @var boolean
	 */
	protected $superior_examinate;

	/**
	 * @var boolean
	 */
	protected $superior_view;

	public function __construct(ilObjManualAssessment $mass, $content = null, $record_template = null, $superior_examinate = false, $superior_view = false)
	{
		assert('is_bool($superior_examinate)');
		assert('is_bool($superior_view)');
		$this->id = $mass->getId();
		$this->content = $content !== null ? $content : self::DEF_CONTENT;
		$this->record_template = $record_template !== null ? $record_template : self::DEF_RECORD_TEMPLATE;
		$this->superior_examinate = $superior_examinate;
		$this->superior_view = $superior_view;
	}

	/**
	 * Get the id of corrwsponding mass-object
	 *
	 * @return	int|string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get the content of this assessment, e.g. corresponding topics...
	 *
	 * @return	string
	 */
	public function content()
	{
		return $this->content;
	}

	/**
	 * Get the record template to be used as default record with
	 * corresponding object
	 *
	 * @return	string
	 */
	public function recordTemplate()
	{
		return $this->record_template;
	}

	/**
	 * Superiors are allowed to examinate their employees
	 *
	 * @return boolean
	 */
	public function superiorExaminate()
	{
		return $this->superior_examinate;
	}

	/**
	 * Superiors are allowed to view results of their employees
	 *
	 * @return boolean
	 */
	public function superiorView()
	{
		return $this->superior_view;
	}

	/**
	 * Set the content of this assessment, e.g. corresponding topics...
	 *
	 * @param	string	$content
	 * @return	ilManualAssessment	$this
	 */
	public function setContent($content)
	{
		assert('is_string($content)');
		$this->content = $content;
		return $this;
	}

	/**
	 * Get the record template to be used as default record with
	 * corresponding object
	 *
	 * @param	string	$record_template
	 * @return	ilManualAssessment	$this
	 */
	public function setRecordTemplate($record_template)
	{
		assert('is_string($record_template)');
		$this->record_template = $record_template;
		return $this;
	}

	/**
	 * Set superiors are allowed to examinate their employees
	 *
	 * @param boolean 	$superior_examinate
	 */
	public function setSuperiorExaminate($superior_examinate)
	{
		assert('is_bool($superior_examinate)');
		$this->superior_examinate = $superior_examinate;
		return $this;
	}

	/**
	 * Set superiors are allowed to view their employees results
	 *
	 * @param boolean 	$superior_view
	 */
	public function setSuperiorView($superior_view)
	{
		assert('is_bool($superior_view)');
		$this->superior_view = $superior_view;
		return $this;
	}
}
