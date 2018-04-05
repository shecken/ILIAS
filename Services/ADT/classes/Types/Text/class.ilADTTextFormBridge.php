<?php

require_once "Services/ADT/classes/Bridges/class.ilADTFormBridge.php";

class ilADTTextFormBridge extends ilADTFormBridge
{
	protected $multi; // [bool]
	protected $multi_rows; // [int]
	protected $multi_cols; // [int]
		
	//
	// properties
	// 
	
	/**
	 * Set multi-line
	 *
	 * @param string $a_value
	 * @param int $a_cols
	 * @param int $a_rows
	 */
	public function setMulti($a_value, $a_cols = null, $a_rows = null)
	{		
	 	$this->multi = (bool)$a_value;
		$this->multi_rows = ($a_rows === null) ? null : (int)$a_rows;
		$this->multi_cols = ($a_cols === null) ? null : (int)$a_cols;
	}

	/**
	 * Is multi-line?
	 *
	 * @return bool
	 */
	public function isMulti()
	{
	 	return $this->multi;
	}
	
	
	//
	// form
	// 
	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTText);
	}
	
	public function setMultiValues($multi_vals)
	{
		$this->multi_vals = $multi_vals;
	}

	public function getMultiValues()
	{
		return $this->multi_vals;
	}

	public function addToForm()
	{		
		$def = $this->getADT()->getCopyOfDefinition();
		
		if(!$this->isMulti())
		{
			$text = new ilTextInputGUI($this->getTitle(), $this->getElementId());

			if($this->getMultiValues()) {
				$text->setMulti(true);
			}

			if($def->getMaxLength())
			{
				$max = $def->getMaxLength();
				$size = $text->getSize();

				$text->setMaxLength($max);

				if($size && $max < $size)
				{
					$text->setSize($max);
				}
			}
				
		}
		else
		{
			$text = new ilTextAreaInputGUI($this->getTitle(), $this->getElementId());
			if($this->multi_rows)
			{
				$text->setRows($this->multi_rows);
			}
			if($this->multi_cols)
			{
				$text->setCols($this->multi_cols);
			}
		}
		
		$this->addBasicFieldProperties($text, $def);
	

		$value = $this->getADT()->getText();
		if($this->getMultiValues()) {
			if(!is_null($value)) {
				$value = unserialize($value);
				$text->setValue($value);
			}
		}

		$text->setValue($value);

		$this->addToParentElement($text);
	}
	
	public function importFromPost()
	{
		$value = $this->getForm()->getInput($this->getElementId());
		if($this->getMultiValues()) {
			foreach ($value as $key => $item) {
				if($item == "") {
					unset($value[$key]);
				}
			}
			if(!is_null($value)) {
				$value = serialize($value);
			}
		}
		// ilPropertyFormGUI::checkInput() is pre-requisite
		$this->getADT()->setText($value);

		$field = $this->getForm()->getItemByPostvar($this->getElementId());
		$value = $this->getADT()->getText();

		if($this->getMultiValues()) {
			$value = unserialize($value);
		}
		$field->setValue($value);
	}
}

?>