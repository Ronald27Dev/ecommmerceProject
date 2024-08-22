<?php

	use Ronald\Address;
	use \Ronald\Page;
	use \Ronald\Model\Category;
	use \Ronald\Model\Product;
	use \Ronald\Model\Cart;
	use Ronald\Model\User;

	$app->get('/', function() {

		$products = Product::listAll();
		
		$page = new Page();
		$page->setTpl("index-body", array(
			"products" => Product::checkList($products)
		));

	});

	$app->get("/categories/:idcategory", function($idcategory) {

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

	$app->get("/products/:desurl", function($desurl) {

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

		$page->setTpl("cart", array(
			"cart" 		=> $cart->getValues(),
			"products" 	=> $cart->getProductsForCart(),
			"final"		=> $cart->getProductsTotal()
		));
	});

	$app->get("/cart/:idproduct/add", function($idproduct) {

		$product = new Product();
		$product->get((int)$idproduct);

		$cart = Cart::getFromSession();

		$qtd = (isset($_GET['qtd'])) ? (int) $_GET['qtd'] : 1;

		for($i = 0; $i < $qtd; $i++) $cart->addProduct($product);

		header("Location: /cart");
		exit;
	});

	$app->get("/cart/:idproduct/minus", function($idproduct) {

		$product = new Product();
		$product->get((int)$idproduct);

		$cart = Cart::getFromSession();
		$cart->removeProduct($product);

		header("Location: /cart");
		exit;
	});

	$app->get("/cart/:idproduct/remove", function($idproduct) {

		$product = new Product();
		$product->get((int)$idproduct);

		$cart = Cart::getFromSession();
		$cart->removeProduct($product, true);

		header("Location: /cart");
		exit;
	});

	// Endereco para calculo do Frete
	$app->post("/cart/freight", function() {

		// $cart = Cart::getFromSession();
		// $cart->setFreight($_POST['zipcode']);

		header("Location: /cart");
		exit;
	});

	$app->get("/checkout", function() {

		User::verifyLogin(false);
		
		$cart = Cart::getFromSession();

		$address = new Address;

		$page = new Page();
		$page->setTpl("checkout", array(
			"cart"		=> $cart->getValues(),
			"adress"	=> $address->getValues()
		));
	});

	$app->get("/login", function() {

		$page = new Page();
		$page->setTpl("login", array(
			"error"				=> User::getError(),
			"errorRegister"		=> User::getErrorRegister(),
			"registerValues"	=> isset($_SESSION['registerValues']) ? $_SESSION['registerValues'] : ['name' => '', 'email' => '', 'phone' => '']
		));
	});

	$app->post("/login", function() {

		
		try{
			
			User::login($_POST['login'], $_POST['password']);
		
		} catch(Exception $e) {

			User::setError($e->getMessage());
		}

		header("Location: /login");
		exit;
	});


	$app->get("/logout", function(){

		User::logout();

		header("Location: /");
		exit;
	});

	$app->post("/register", function(){

		$_SESSION["registerValues"] = $_POST;

		if(!isset($_POST['name']) || $_POST['name'] == '') {

			User::setErrorRegister("Preencha o campo nome");
			header("Location: /login");
			exit;
		} else if(!isset($_POST['email']) || $_POST['email'] == '') {

			User::setErrorRegister("Preencha o campo de email");
			header("Location: /login");
			exit;
		} else if(!isset($_POST['phone']) || $_POST['phone'] == '') {

			User::setErrorRegister("Preencha o campo de telefone");
			header("Location: /login");
			exit;
		} else if(!isset($_POST['password']) || $_POST['password'] == '') {

			User::setErrorRegister("Preencha o campo de senha");
			header("Location: /login");
			exit;
		}

		if(User::checkLoginExist($_POST['email'], $_POST['password'])){ 
			
			User::setErrorRegister("Email já esta sendo usado");
			header("Location: /login");
			exit;
		}

		$user = new User();
		$user->setData([
			"inadmin"		=> 0,
			"deslogin"		=> $_POST["email"],
			"desperson"		=> $_POST["name"],
			"desemail"		=> $_POST["email"],
			"despassword"	=> $_POST["password"],
			"nrphone"		=> $_POST["phone"]
		]);

		header("Location: /checkout");
		exit;
	});


	$app->get('/forgot', function(){

		$page = new Page();

		$page->setTpl("forgot", array(
			"error"	=> User::getError(),
		));
	});

	$app->post('/forgot', function(){

		try{
			$user = User::forgotPassword($_POST["email"], false);
		} catch (\Exception $e) {
			User::setError($e->getMessage());
			
			header("Location: /forgot");
			exit;
		}
		header("Location: /forgot/sent");
		exit;
	});

	$app->get("/forgot/sent", function(){

		$page = new Page();
		$page->setTpl("forgot-sent");
	});
	
	$app->get("/forgot/reset", function(){

		$user = User::validForgotDecrypt($_GET["code"]);

		$page = new Page();
		$page->setTpl("forgot-reset", array(
			"name"	=> $user["desperson"],
			"code" 	=> $_GET["code"]	
		));
	});

	$app->post("/forgot/reset", function(){

		$userRecovery = User::validForgotDecrypt($_POST["code"]);

		USER::setForgotUsed($userRecovery["idrecovery"]);
	
		$user = new User();
		$user->get((int)$userRecovery["iduser"]);

		$password = password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost" => 12]);
		
		$user->setPassword($password);

		$page = new Page();
		$page->setTpl("forgot-reset-success");
	});


	$app->get("/profile", function() {

		User::verifyLogin(false);

		$user = User::getFromSession();

		$page = new Page();
		$page->setTpl("profile", array(
			"user"			=> $user->getValues(),
			"profileMsg"	=> User::getSuccess(),
			"profileError"	=> User::getError()
		));
	}); 

	$app->post("/profile", function(){

		User::verifyLogin(false);

		if(!$_POST["desperson"] || $_POST["desperson"] === '') {
			User::setError("Preencha o campo nome");
			header("Location: /profile");
			exit;
		}
		if(!$_POST["desemail"] || $_POST["desemail"] === '') {
			User::setError("Preencha o campo E-mail");
			header("Location: /profile");
			exit;
		}
		if(!$_POST["nrphone"] || $_POST["nrphone"] === ''){
			User::setError("Preencha o campo Telefone");
			header("Location: /profile");
			exit;
		}
		
		$user = User::getFromSession();
		
		if($_POST["desemail"] !== $user->getdesemail()) {
			if(User::checkLoginExist($_POST["desemail"]) === true) {
				User::setError("E-mail já esta em uso");
			}
		}


		$_POST['inadmin'] 		= $user->getinadmin();
		$_POST['despassword']	= $user->getdespassword();
		$_POST["deslogin"]		=$_POST["desemail"];

		$user->setData($_POST);
		$user->update();

		User::setSuccess("Informações Atualizadas");

		header("Location: /profile");
		exit;
	})
?>