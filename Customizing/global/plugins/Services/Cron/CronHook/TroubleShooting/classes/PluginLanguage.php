<?php

trait PluginLanguage {
	protected function txt($code) {
		$txt = $this->txt;
		if(!is_object($txt) && ($txt instanceof Closure)) {
			return $code;
		}
		return $txt($code);
	}
}