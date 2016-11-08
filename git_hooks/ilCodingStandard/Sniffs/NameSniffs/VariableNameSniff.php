<?php

use ilCodingStandard\Sniffs\NameSniff as NameSniff;

class ilCodingStandard_Sniffs_NameSniffs_VariableNameSniff extends NameSniff\NameSniffBase
{

	protected static $valid_name_regexp =
			'#^\\$[a-z]+(_[a-z]+)*$#';

	const INVALID_VARIABLE_NAME_IN_CLASS_METHOD_ERROR =
			'Variable name invalid: %s in %s::%s';
	const INVALID_VARIABLE_NAME_IN_CLASS_ERROR =
			'Variable name invalid: %s in %s';


	public function register()
	{
		return array(T_VARIABLE);
	}

	public function process(PHP_CodeSniffer_File $phpcs_file, $stack_ptr)
	{

		$tokens = $phpcs_file->getTokens();

		$token = $tokens[$stack_ptr];
		$function_name = null;
		$object_name = null;
		foreach ($token['conditions'] as $token_ptr => $token_code) {
			$condition_token = $tokens[$token_ptr];
			if (T_FUNCTION === $token_code) {
				$function_name = $this->getTokenName($tokens, $token_ptr, $condition_token['parenthesis_opener']);
			} elseif (in_array($token_code, array(T_CLASS,T_INTERFACE,T_TRAIT))) {
				$object_name = $this->getTokenName($tokens, $token_ptr, $condition_token['scope_opener']);
			}
		}

		$variable_name = $token['content'];

		if (!$this->validName($variable_name) && $object_name !== null) {
			if ($function_name !== null) {
				$error = sprintf(self::INVALID_VARIABLE_NAME_IN_CLASS_METHOD_ERROR, $variable_name, $object_name, $function_name);
			} else {
				$error = sprintf(self::INVALID_VARIABLE_NAME_IN_CLASS_ERROR, $variable_name, $object_name);
			}
			$this->handleError(
				$phpcs_file,
				$error,
				$stack_ptr,
				$tokens[$stack_ptr]
			);
		}
	}
}
