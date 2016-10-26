<?php
/**
 * This sniff prohibits the use of Perl style hash comments.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @author   Your Name <you@domain.net>
 * @license  https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */


/**
 * This sniff prohibits the use of Perl style hash comments.
 *
 * An example of a hash comment is:
 *
 * <code>
 *  # This is a hash comment, which is prohibited.
 *  $hello = 'hello';
 * </code>
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @author   Your Name <you@domain.net>
 * @license  https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class ilCodingStandard_Sniffs_Scope_ScopeOpenersSniff implements PHP_CodeSniffer_Sniff
{

	protected static $w_argument = array(
											T_FUNCTION
										,    T_FOREACH
										,    T_IF
										,    T_WHILE
										,    T_FOR
										,    T_SWITCH
										);
	protected static $w_o_argument = array(T_CLASS, T_INTERFACE, T_TRAIT);

	const SCOPE_OPENER_KEY = 'scope_opener';
	const SCOPE_CLOSER_KEY = 'scope_closer';

	public function register()
	{
		return array_merge(self::$w_argument, self::$w_o_argument);
	}//end register()


	/**
	 * Processes the tokens that this sniff is interested in.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
	 * @param int                  $stackPtr  The position in the stack where
	 *                                        the token was found.
	 *
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();
		if (in_array($tokens[$stackPtr]['code'], self::$w_o_argument)) {
		}
	}

	private function processWOArgument($tokens, $stackPtr)
	{
	}

	private function checkCloser($tokens, $stackPtr)
	{
	}
}
