<?php

namespace CaT\TRTFixtures;

use ILIAS\TMS\Filter as Filters;
use ILIAS\TMS\TableRelations as TR;

class SqlQueryInterpreterWrap extends TR\SqlQueryInterpreter {

	public function __construct()
	{

	}

	public function _interpretField(Filters\Predicates\Field $field) {
		return $this->interpretField($field);
	}

	public function _interpretTable(TR\Tables\AbstractTable $table) {
		return $this->interpretTable($table);
	}
}

