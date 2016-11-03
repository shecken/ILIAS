<?php

class  ilCodingStandard_Sniffs_NameSniffs_VariableNameSniff
	implements PHP_CodeSniffer_Sniff
{

	const VALID_VARIABLE_NAME_REGEXP =
			'#^\\$[a-z]+(_[a-z]+)*$#';
	const INVALID_VARIABLE_NAME_ERROR =
			'Variable name invalid: %s in %s::%s';

	public function register() {
		return array(T_FUNCTION);
	}

	public function process(PHP_CodeSniffer_File $phpcs_file, $stack_ptr) {
		$tokens = $phpcs_file->getTokens();
		$token = $token[$stack_ptr];
		$function_name = null;
		$object_name = name;
		foreach ($token['conditions'] as $token_id => $token_code) {
			if(T_FUNCTION === $token_code) {

			} elseif( in_array($token_code, array(T_CLASS,T_INTERFACE,T_TRAIT))) {

			}
		}

		$variable_name = $token['content'];

		if(!$this->validName($variable_name)) {
			$this->handleError(
				$phpcs_file, sprintf(self::INVALID_METHOD_NAME_ERROR, $variable_name, $object_name, $method_name), $stack_ptr, $tokens[$stack_ptr]);
		}

	}
}