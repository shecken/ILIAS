<?php

// cat-tms-patch start

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */
require_once './libs/composer/vendor/autoload.php';

use Whoops\Exception\Formatter;

/**
 * Saves error informations into file
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilLoggingErrorFileStorage {
	const KEY_SPACE = 25;
	const FILE_FORMAT = ".json";

	public function __construct($inspector, $file_path, $file_name) {
		$this->inspector = $inspector;
		$this->file_path = $file_path;
		$this->file_name = $file_name;
	}

	protected function createDir($path) {
		if(!is_dir($this->file_path)) {
			ilUtil::makeDirParents($this->file_path);
		}
	}

	protected function content() {
		$part1 = array(
			"error_id" => $this->file_name,
			"Exception" =>  $this->exceptionContent()
		);
		$part2 = $this->tables();
		$content = array_merge($part1, $part2);
		return json_encode($content);
	}

	public function write() {
		$this->createDir($this->file_path);

		$file_name = $this->file_path."/".$this->file_name.self::FILE_FORMAT;
		$stream = fopen($file_name, 'w+');
		fwrite($stream, $this->content());
		fclose($stream);
		chmod($file_name, 0755);
	}

	/**
	 * Get a short info about the exception.
	 *
	 * @return string
	 */
	protected function exceptionContent() {
		return Formatter::formatExceptionPlain($this->inspector);
	}

	/**
	 * Get the tables that should be rendered.
	 *
	 * @return array 	$title => $table
	 */
	protected function tables() {
		$post = $_POST;
		$server = $_SERVER;

		$post = $this->hidePassword($post);
		$server = $this->shortenPHPSessionId($server);

		return array
			( "GET Data" => $_GET
			, "POST Data" => $post
			, "Files" => $_FILES
			, "Cookies" => $_COOKIE
			, "Session" => isset($_SESSION) ? $_SESSION : array()
			, "Server/Request Data" => $server
			, "Environment Variables" => $_ENV
			);
	}

	/**
	 * Replace passwort from post array with security message
	 *
	 * @param array $post
	 *
	 * @return array
	 */
	private function hidePassword(array $post) {
		if(isset($post["password"])) {
			$post["password"] = "REMOVED FOR SECURITY";
		}

		return $post;
	}

	/**
	 * Shorts the php session id
	 *
	 * @param array 	$server
	 *
	 * @return array
	 */
	private function shortenPHPSessionId(array $server) {
		global $ilLog;

		$cookie_content = $server["HTTP_COOKIE"];
		$cookie_content = explode(";", $cookie_content);

		foreach ($cookie_content as $key => $content) {
			$content_array = explode("=", $content);
			if(trim($content_array[0]) == session_name()) {
				$content_array[1] = substr($content_array[1], 0, 5)." (SHORTENED FOR SECURITY)";
				$cookie_content[$key] = implode("=", $content_array);
			}
		}

		$server["HTTP_COOKIE"] = implode(";", $cookie_content);

		return $server;
	}
}

// cat-tms-patch end