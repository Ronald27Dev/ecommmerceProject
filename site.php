	<?php 
	use \Slim\Slim;
	use \Ronald\Page;
	use \Ronald\PageAdmin;
	use \Ronald\Model\User;
	use \Ronald\Model\Category;
	use \Ronald\Model\Product;
	use \Ronald\Model\Cart;

	$app->get('/', function() {

		$products = Product::listAll();
		
		$page = new Page();
		$page->setTpl("index-body", array(
			"products" => Product::checkList($products)
		));

	});

	$app->get("/categories/:idcategory", function($idcategory){

		$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

		$category = new Category();
		$category->get((int)$idcategory);

		$pagination = $category->getProductsPage();

		$pages = [];

		for ($i=1; $i < $pagination['pages']; $i++) { 
			array_push($pages, [
				'link'	=> '/categories/' . $category->getidcategory() . '?page=' . $i,
				'page'	=> $i
			]);
		}

		$page = new Page();
		$page->setTpl("category", array(
			"category"	=> $category->getValues(),
			"products"	=> $pagination['data'],
			"pages"		=> $pages
		));
	});

	$app->get("/products/:desurl", function($desurl){

		$product = new Product();
		$product->getFromURL($desurl);

		$page = new Page();
		$page->setTpl("product-detail", array(
			'product' 		=> $product->getValues(),
			'categories'	=> $product->getCategories()
		));
	});

	$app->get("/cart", function(){

		$cart = Cart::getFromSession();
		$page = new Page();
		$page->setTpl("cart");
	});
?>