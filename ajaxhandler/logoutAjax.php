<?php
session_start();
// clear session and destroy
$_SESSION = [];
if (ini_get("session.use_cookies")) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'], $params['secure'], $params['httponly']
	);
}
session_unset();
session_destroy();

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'success']);
exit;

?>