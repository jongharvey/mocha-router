<?php

namespace Mocha\Application;


use Exception;
use Mocha\Auth\InsufficientAccessException;
use Mocha\Routing\PageNotFoundException;
use Mocha\Routing\Router;

class BaseApplication {
	public function __construct() {
		session_start();
	}

	public function route() {
		$router = new Router();

		try {
			echo $router->route();
		} catch (UserException $e) {
			header('HTTP/1.1 503 User Exception');
			die("User exception: ".$e->getMessage());
		} catch (PageNotFoundException $e) {
			header('HTTP/1.1 404 Not Found');
			die($e->getMessage());
		} catch (InsufficientAccessException $e) {
			header('HTTP/1.1 403 Insufficient Privileges');
			die($e->getMessage());
		} catch (Exception $e) {
			header('HTTP/1.1 503 Internal Server Error');
			die("<h1>HTTP/503 Server Error</h1><p>Please contact technical support for assistance.</p>");
		}
	}
}