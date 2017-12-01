<?php

require __DIR__ . '/../src/MangoPressTemplatingMacroSet.php';
require __DIR__ . '/../src/MangoPressTemplatingFilterSet.php';

function toPath($url) {
	$urlscript = new Nette\Http\UrlScript($url);
	return rtrim($urlscript->scheme . '://' . $urlscript->authority . $urlscript->path, '/');
}

function toRelativePath($url) {
	$urlscript = new Nette\Http\UrlScript($url);
	return rtrim($urlscript->getPath(), '/');
}

function renderLatte($path, $parameters = array()) {
	global $App;
	global $View;
	global $wp_query;
	global $post;

	$assetsDirname = !empty($App->parameters['assetsDirname']) ? trim($App->parameters['assetsDirname'], '/') : 'assets';

	$fullParameters = array(
		'App' => $App,
		'baseUrl' => toPath(WP_HOME),
		'basePath' => toRelativePath(WP_HOME),
		'assetsUrl' => toPath(WP_HOME) . '/' . $assetsDirname,
		'assetsPath' => toRelativePath(WP_HOME) . '/' . $assetsDirname,
		'wp_query' => $wp_query,
		'post' => $post,
		'flashes' => getFlashMessages()
	);

	if(isset($View)) {
		foreach($View as $key => $val) {
			$fullParameters[$key] = $val;
		}
	}

	foreach($parameters as $key => $val) {
		$fullParameters[$key] = $val;
	}

	$latte = new Latte\Engine;
	$latte->setTempDirectory(TEMP_DIR . '/cache/latte');

	MangoPressTemplatingMacroSet::install($latte->getCompiler());
	Nette\Bridges\FormsLatte\FormMacros::install($latte->getCompiler());

	MangoPressTemplatingFilterSet::install($latte);

	return $latte->render($path, (array) $fullParameters);
}

function view($view = NULL, $parameters = NULL) {
	if(is_array($view) && !$parameters) {
		$parameters = $view;
		$view = NULL;
	}
	$parameters = (array)$parameters;
	if(!$view) {
		$bt =  debug_backtrace();
		$view = basename($bt[0]['file'], '.php');
	}
	$path = THEME_VIEWS_DIR . "/$view.latte";
	do_action('pre_render_view');
	return renderLatte($path, $parameters);
}

function viewString($view, $parameters = array()) {
	$path = THEME_VIEWS_DIR . "/$view.latte";
	return renderLatteToString($path, $parameters);
}

function renderLatteToString($path, $parameters = array()) {
	ob_start();
	renderLatte($path, $parameters);
	$str = ob_get_contents();
	ob_end_clean();
	return $str;
}
