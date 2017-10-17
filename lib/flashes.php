<?php

define('FLASH_INFO', 'info');
define('FLASH_SUCCESS', 'success');
define('FLASH_WARNING', 'warning');
define('FLASH_ERROR', 'danger');
define('FLASH_KEY', '_fid');

function getFlashParam() {
	static $param;

	if (!isset($param)) {
		$param = isset($_GET[FLASH_KEY]) ? $_GET[FLASH_KEY] : Nette\Utils\Random::generate(4);
	}
	return $param;
}

function getFlashSession() {
	global $App;

	if(!isset($App)) {
		return [];
	}

	$param = getFlashParam();
	$section = $App->getService('session')->getSection('Nette.Application.Flash/' . $param);
  $section->setExpiration('+5 seconds');

	return $section;
}

function flashMessage($message, $type = FLASH_INFO) {
	$session = getFlashSession();

	$messages = $session->flash;
	$messages[] = (object) [
		'message' => $message,
		'type' => $type,
	];
	$session->flash = $messages;

	return getFlashParam();
}

function flashInfo($message) {
	return flashMessage($message, FLASH_INFO);
}

function flashSuccess($message) {
	return flashMessage($message, FLASH_SUCCESS);
}

function flashWarning($message) {
	return flashMessage($message, FLASH_WARNING);
}

function flashError($message) {
	return flashMessage($message, FLASH_ERROR);
}
