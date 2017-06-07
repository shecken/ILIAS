<?php

namespace CaT\Plugins\ReportMaster\ReportDiscovery;

class Group extends MenuItem
{
	/**
	 * @inheritdoc
	 */
	protected function obligatoryLinkParameters()
	{
		return ['type'];
	}
}
