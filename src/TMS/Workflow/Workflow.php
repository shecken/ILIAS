<?php

namespace ILIAS\TMS\Workflow;

/**
 * Definition of a workflow.
 */
interface Workflow {
	/**
	 * Get a unique id for that workflow.
	 *
	 * Must be unique over all similar workflows, perform by different people,
	 * under different circumstances, ...
	 *
	 * It must be garanteed, that the steps do not change over different
	 * instantiations of the same workflow.
	 *
	 * @return	string
	 */
	public function getId();

	/**
	 * Get the steps to be processed.
	 *
	 * @return	Step[]
	 */
	public function getSteps();
}
