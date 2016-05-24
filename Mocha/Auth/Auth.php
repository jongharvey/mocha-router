<?php

namespace Mocha\Auth;

class Auth {
	public static $loginUrl = '/home/login';

	public static function isAuthenticated() {
		return !empty($_SESSION['user_id']) && !empty($_SESSION['user_level']);
	}

	public static function check($level, $require = false) {
		if (empty($_SESSION['user_id']) || empty($_SESSION['user_level']) || ($_SESSION['user_level'] & $level) <= 0) {
			if ($require)
				throw new InsufficientAccessException("You do not have access to this resource.");
			return false;
		}
		return true;
	}

	public static function requireLevel($level) {
		return self::check($level, true);
	}

	public static function login($user_id, $level) {
		$_SESSION['user_id'] = $user_id;
		$_SESSION['user_level'] = $level;
	}

	public static function logout() {
		unset($_SESSION['user_id']);
		unset($_SESSION['user_level']);
	}
}
