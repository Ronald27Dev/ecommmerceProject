<?php 

	use \Slim\Slim;
	use \Ronald\Page;
	use \Ronald\PageAdmin;
	use \Ronald\Model\User;
	use \Ronald\Model\Category;
	
	$app->get("/admin/categories", function(){

		User::verifyLogin();

		$category = Category::listAll();

		$page = new PageAdmin();
		$page->setTpl("categories", array(
			"categories" => $category
		));
	});

	$app->get("/admin/categories/create", function(){

		User::verifyLogin();

		$page = new PageAdmin();
		$page->setTpl("categories-create");
	});

	$app->get("/admin/categories/update", function(){

		User::verifyLogin();

		$page = new PageAdmin();
		$page->setTpl("categories-update");
	});

	$app->post("/admin/categories/create", function(){

		User::verifyLogin();

		$category = new Category();
		$category->setData($_POST);
		$category->save();

		header("Location: /admin/categories");
		exit;
	});

	$app->get("/admin/categories/:idcategory", function($idcategory){

		User::verifyLogin();

		$category = new Category();
		$category->get((int)$idcategory);

		$page = new PageAdmin();
		$page->setTpl("categories-update", array(
			"category" => $category->getValues()
		));
	});	

	$app->post("/admin/categories/:idcategory", function($idcategory){

		User::verifyLogin();

		$category = new Category();
		$category->get((int)$idcategory);
		$category->setData($_POST);
		$category->save();

		header("Location: /admin/categories");
		exit;
	});

	$app->get("/admin/categories/:iduser/delete", function($iduser){

		User::verifyLogin();

		$category = new Category();
		$category->delete($iduser);

		header("Location: /admin/categories");
		exit;
	});

	$app->get("/categories/:idcategory", function($idcategory){

		$category = new Category();
		$category->get((int)$idcategory);

		$page = new Page();
		$page->setTpl("category", array(
			"category" => $category->getValues()
		));
	});
?>