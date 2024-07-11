	<?php 
	use \Slim\Slim;
	use \Ronald\Page;
	use \Ronald\PageAdmin;
	use \Ronald\Model\User;
	use \Ronald\Model\Category;
	use \Ronald\Model\Product;

	$app->get('/', function() {

		$products = Product::listAll();
		
		$page = new Page();
		$page->setTpl("index-body", array(
			"products" => Product::checkList($products)
		));

	});

	$app->get("/categories/:idcategory", function($idcategory){

		$category = new Category();
		$category->get((int)$idcategory);

		$page = new Page();
		$page->setTpl("category", array(
			"category"	=> $category->getValues(),
			"products"	=> Product::checkList($category->getProducts())
		));
	});
?>