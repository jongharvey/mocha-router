<?php

namespace Mocha\Controllers;


use Exception;
use Mocha\Auth\Auth;
use Mocha\Routing\Router;

abstract class BaseController {
	/** @var  string View path of master layout */
	protected $layout = 'layout';

	/** @var int Auth levels required for this controller */
	protected $authRequired = 0;

	/** @var array Data to pass to master layout */
	public $layoutData = [];

	public function __construct() {
		$this->__checkAuth();
	}

	/**
	 * @param $view Relative path to the view. Extension optional if .php
	 * @param array $viewData Data to be made available to the view file
	 * @return string 
	 * @throws Exception
	 */
	public function render($view, array $viewData = []) {
		$path = Router::$viewRoot . '/' . ($view != null ? $view : $this->layout);

		ob_start();
		extract($viewData);
		if (file_exists($path))
			require($path);
		else if (file_exists($extPath = "$path.php"))
			require($extPath);
		else
			throw new Exception("View does not exist: '$view' at '$path'");

		$html = ob_get_clean();
		if ($view == null)
			return $html;

		$this->layoutData['content'] = $html;
		$this->layoutData['title'] = isset($viewData['title']) ? $viewData['title'] : 'Untitled';

		return $this->render(null, $this->layoutData);
	}

	protected function __checkAuth() {
		if (empty($this->authRequired))
			return;

		if (!Auth::isAuthenticated())
			Router::redirect(Auth::$loginUrl);

		Auth::requireLevel($this->authRequired);
	}

	function redirect($uri) {
		Router::redirect($uri);
	}

	function json($data) {
		Router::json($data);
	}
}