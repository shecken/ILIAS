<?php

namespace CaT\UserOrguImport\Orgu;

class OrguFactory
{
	public function OrgUnit($id, $metadata = [])
	{
		return new OrgUnit($id, $metadata);
	}

	public function OrgStructure()
	{
		return new AdjacentlyConstructedOS();
	}
}
