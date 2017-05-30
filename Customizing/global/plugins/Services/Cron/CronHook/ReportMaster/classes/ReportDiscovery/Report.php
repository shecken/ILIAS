<?php

namespace CaT\Plugins\ReportMaster\ReportDiscovery;

class Report extends MenuItem
{
	/**
	 * @inheritdoc
	 */
	protected function obligatoryLinkParameters()
	{
		return ['type', 'ref_id'];
	}
}
