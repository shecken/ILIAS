<?php

/**
 * Immutable class for blank building block informations
 */
class BlankBuildingBlock {
	protected $content;
	protected $target;
	protected $request_id;
	protected $crs_id;
	protected $bb_id;

	public function __construct($bb_id, $crs_id, $request_id, $content, $target) {
		$this->content = $content;
		$this->target = $target;
		$this->request_id = $request_id;
		$this->crs_id = $crs_id;
		$this->bb_id = $bb_id;
	}

	public function getContent() {
		return $this->content;
	}

	public function getTarget() {
		return $this->target;
	}

	public function getRequestId() {
		return $this->request_id;
	}

	public function getCrsId() {
		return $this->crs_id;
	}

	public function getBbId() {
		return $this->bb_id;
	}
}