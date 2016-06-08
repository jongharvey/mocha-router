<?php

namespace Mocha\Routing;

use Exception;

class Router {
	public static $viewRoot = 'viewRoot';
	public static $controllerNamespace = '\Controllers';
	public $uri;

	public function route($uri = null) {
		// If URI is left blank, default to the server requested URI
		if ($uri === null)
			$uri = $_SERVER['REQUEST_URI'];

		// If we receive a blank URI, default to root /
		if (empty($uri))
			$uri = '/';

		// Clean up URI by stripping leading slash
		if ($uri[0] == '/')
			$uri = substr($uri, 1);

		// Get parts of the URI
		$parts = explode('/', $this->uri = $uri);

		// Get the controller class
		$class = self::$controllerNamespace . '\\' . self::cleanFragment(array_shift($parts), 'Home');
		if (!class_exists($class, true))
			throw new PageNotFoundException("Class '$class' doesn't exist");

		// Instantiate controller class
		$controller = new $class();

		// Get the action method
		$action = self::cleanFragment(array_shift($parts), 'Index');
		$method = empty($_SERVER['REQUEST_METHOD'])
			? 'cli'
			: strtolower($_SERVER['REQUEST_METHOD']);

		// Check for method + name
		if (method_exists($controller, $full_method = "{$method}{$action}"))
			return call_user_func_array([$controller, $full_method], $parts);

		// Check for any + name
		if (method_exists($controller, $full_method = "any{$action}"))
			return call_user_func_array([$controller, $full_method], $parts);

		// Route not found
		throw new PageNotFoundException("Route $method doesn't exist for $class");
	}

	/**
	 * Cleans URI fragment
	 * @param $fragment string URI fragment to clean
	 * @param null $default string Default text, if fragment is empty
	 * @return string Cleaned fragment URI string
	 * @throws PageNotFoundException
	 */
	public static function cleanFragment($fragment, $default = null) {
		// If fragment is empty, return default
		if ($default !== null && empty($fragment))
			return $default;

		// Check for invalid characters in fragment
		if (!preg_match('/^[a-zA-Z0-9\.\-\_]*$/', $fragment))
			throw new PageNotFoundException("Invalid character in fragment: $fragment");

		// Convert hyphenated (or otherwise segmented) URL to camelCaseWord
		$fragment = str_replace(['-', '_', '.'], ' ', $fragment);
		$fragment = ucwords($fragment);
		return str_replace(' ', '', $fragment);
	}

	static function redirect($uri) {
		header('Location: ' . $uri);
		exit();
	}

	static function download($path, $name, $contentType, $attachment = true) {
		if (is_string($path))
			$path = fopen($path, 'r');
		header("Content-type: $contentType");
		header('Content-disposition: ' . ($attachment ? 'attachment; ' : '') . 'filename="' . $name . '"');
		fpassthru($path);
		exit();
	}

	static function csv($path, $name) {
		self::download($path, $name, 'text/csv');
	}

	static function json($data) {
		header('Content-type: application/json');
		echo json_encode($data);
		exit();
	}

	static function text($data) {
		header('Content-type: text/plain');
		echo $data;
		exit();
	}

	static function getPostJson($assoc = true) {
		if ($_SERVER['REQUEST_METHOD'] != 'POST')
			throw new Exception("Request method was not POST");

		$raw = file_get_contents('php://input');
		return json_decode($raw, $assoc);
	}
}
