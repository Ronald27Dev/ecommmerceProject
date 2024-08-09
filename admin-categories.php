<?php 

	use \Slim\Slim;
	use \Ronald\Page;
	use \Ronald\PageAdmin;
	use \Ronald\Model\User;
	use \Ronald\Model\Category;

use function Functions\pr;

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

		$_POST["idcategory"] = null;

		// pr($_POST);
		
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

	$app->get("/admin/categories/:idcategory/delete", function($idcategory){

		User::verifyLogin();

		$category = new Category();
		$category->delete($idcategory);

		header("Location: /admin/categories");
		exit;
	});

	$app->get("/admin/categories/:idcategory/products", function($idcategory){

		User::verifyLogin();

		$category = new Category();
		$category->get((int)$idcategory);

		$page = new PageAdmin();
		$page->setTpl("categories-products", array(
			"category" 				=> $category->getValues(),
			"productsRelated"		=> $category->getProducts(),
			"productsNotRelated"	=> $category->getProducts(false)
		));
	});

	$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){

		User::verifyLogin();

		$category = new Category();
		$category->updateProductsList("add", $idcategory, $idproduct);

		header("Location: /admin/categories/" . $idcategory . "/products");
		exit;
	});

	$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){

		User::verifyLogin();

		$category = new Category();
		$category->updateProductsList("remove",  $idcategory, $idproduct);

		header("Location: /admin/categories/" . $idcategory . "/products");
		exit;
	});
?>