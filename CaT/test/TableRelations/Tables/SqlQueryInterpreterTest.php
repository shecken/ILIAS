<?php
use CaT\TableRelations as TR;
use CaT\Filter as Filters;
class SqlQueryInterpreterTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		ilUnitUtil::performInitialisation();
		global $ilDB;
		$this->db = $ilDB;

		$this->gf = new TR\GraphFactory();
		$this->pf = new Filters\PredicateFactory();
		$this->tf = new TR\TableFactory($this->pf, $this->gf);
		$this->i = new  SqlQueryInterpreter( new Filters\SqlPredicateInterpreter($this->db), $this->pf, $this->db);
	}

	protected function field($id) {
		return $this->tf->field($id);
	}

	protected function table($id) {
		return $this->tf->table('foo',$id);
	}



	public function test_field() {
		$i = $this->i;
		$f1 = $this->field('f1');
		$f2 = $this->field('f2');
		$f3 = $this->field('f3');
		$table = $this->table('a')->addField($f1)->addField($f2)->addField($f3);
		$this->assertEquals(' a.f1',$i->_interpretField($table->field('f1')));
		$this->assertEquals(' a.f1 + a.f2',$i->_interpretField($this->tf->plus('plus',$table->field('f1'),$table->field('f2'))));
		$this->assertEquals(' a.f1 / a.f2',$i->_interpretField($this->tf->quot('quot',$table->field('f1'),$table->field('f2'))));
		$this->assertEquals(' SUM( a.f1)',$i->_interpretField($this->tf->sum('sum',$table->field('f1'))));
		$this->assertEquals(' COUNT(*)',$i->_interpretField($this->tf->countAll('bal')));
		$this->assertEquals(' MAX( a.f1)',$i->_interpretField($this->tf->max('max',$table->field('f1'))));
		$this->assertEquals(' MIN( a.f1)',$i->_interpretField($this->tf->min('min',$table->field('f1'))));
		$this->assertEquals(' MIN( a.f1)',$i->_interpretField($this->tf->min('foo',$table->field('f1'))));
		$this->assertEquals(' SUM( MIN( a.f1) / MAX( a.f2))',
			$i->_interpretField(
				$this->tf->sum('sum',
					$this->tf->quot('quot',$this->tf->min('min',$table->field('f1')),
						$this->tf->max('max',$table->field('f2'))))));
	}

	public function test_table() {
		$i = $this->i;
		$f1 = $this->field('f1');
		$f2 = $this->field('f2');
		$f3 = $this->field('f3');
		$table = $this->table('a')->addField($f1)->addField($f2)->addField($f3);
		$this->assertEquals($i->_interpretTable($table),'foo AS a');
	}
}

class SqlQueryInterpreter extends TR\SqlQueryInterpreter {

	public function _interpretField(Filters\Predicates\Field $field) {
		return $this->interpretField($field);
	}

	public function _interpretTable(TR\Tables\AbstractTable $table) {
		return $this->interpretTable($table);
	}
}