<?php

namespace CaT\IliasUserOrguImport;

use User\ExcelUsers as EU;

class Utils
{
	
	const NO_ASSIGNMENT = 'keine Zuordnung';
	public static function getRealOrguPath(array $orgu_path_full)
	{
		$orgu_path_real = [];
		$current = array_shift($orgu_path_full);
		while($current) {
			$orgu_path_real[] = $current;
			$next = array_shift($orgu_path_full);
			if($next === $current || $next === self::NO_ASSIGNMENT) {
				break;
			}
			$current = $next;
		}
		return $orgu_path_real;
	}
}