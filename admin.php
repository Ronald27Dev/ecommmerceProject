<?php 
	use \Slim\Slim;
	use \Ronald\Page;
	use \Ronald\PageAdmin;
	use \Ronald\Model\User;
	use \Ronald\Model\Category;
	
	$app->get('/admin', function() {

		User::verifyLogin();
		
		$page = new PageAdmin();
		$page->setTpl("index-body");

	});

	$app->get('/admin/login', function() {
		
		$page = new PageAdmin(
			[
				"header" => false,
				"footer" => false
			]
		);
		$page->setTpl("login");

	});

	$app->post('/admin/login', function() {

		User::login($_POST["login"], $_POST["password"]);
		
		header("Location: /admin");
		exit;
	});

	$app->get('/admin/logout', function () {

		User::logout();

		header("Location: /admin/login");
		exit;
	});
?>