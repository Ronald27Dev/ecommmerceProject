<?php
	namespace Ronald\Model;

	use PDOException;
	use \Ronald\DB\Sql;
	use \Ronald\Model;
	use \Ronald\Model\User;

	class Cart extends Model {

		const SESSION = "cart";

		public static function getFromSession() {

			$cart = new Cart();
 
			if(isset($_SESSION[Cart::SESSION]) && isset($_SESSION[Cart::SESSION]['idcart']) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){

				$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

			} else {

				$cart->getFromSessionID();
				
				if(!(int)$cart->getidcart() > 0) {

					$data = [
						"dessessionid" => session_id()
					];

					if(User::checkLogin(false)) {

						$user = User::getFromSession();
						$data['iduser'] = $user->getiduser();
					}
					
					if(!isset($data['idcart']) && !isset($data['iduser'])){
						
						$data["idcart"] = null;
						$data["iduser"] = null;

					}

					$cart->setData($data);
					$cart->save();
					$cart->setToSession();
				}
			}

			return $cart;
		}

		public function setToSession() {

			$_SESSION[Cart::SESSION] = $this->getValues();
		}

		public function getFromSessionID(){

			$sql = new Sql();
			$sql->beginTransaction();

			try{
				
				$result = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", array(
					":dessessionid" => session_id()
				));

				$sql->commit();

				// pr($result);
				if(count($result) > 0) {
					
					$this->setidcart($result[0]['idcart']);
					$this->setdessessionid($result[0]['dessessionid']);
					$this->setiduser($result[0]['iduser']);
					$this->setdeszipcode($result[0]['deszipcode']);
					$this->setvlfreight($result[0]['vlfreight']);
					$this->setnrdays($result[0]['nrdays']);
					$this->setdtregister($result[0]['dtregister']);
				}

			} catch (PDOException $e) {

				$sql->rollBack();
				die("Erro ao pegar o id da sessão " . $e->getMessage());
			}
		}

		public function get(int $idcart){

			$sql = new Sql();

			$sql->beginTransaction();

			try{

				$result = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", array(
					":idcart" => $idcart
				));
				$sql->commit();

				if(count($result) > 0) $this->setData($result[0]);
				
			} catch (PDOException $e) {

				$sql->rollBack();
				die("Erro ao encontrar carrinho " . $e->getMessage());
			}
		}

		public function save(){

			$sql = new Sql();
			$sql->beginTransaction();

			try{
				$result = $sql->select("CALL sp_carts_save(:pidcart, :pdessessionid, :piduser, :pdeszipcode, :pvlfreight, :pnrdays)", array(
					":pidcart" 			=> $this->getidcart(),
					":pdessessionid"	=> $this->getdessessionid(),
					":piduser"			=> $this->getiduser(),
					":pdeszipcode"		=> $this->getdeszipcode(),
					":pvlfreight"		=> $this->getvlfreight(),
					":pnrdays"			=> $this->getnrdays()
				));
				$this->setData($result);
				$sql->commit();	
			} catch (PDOException $e) {
				
				$sql->rollBack();
				error_log("Erro ao inserir produto no carrinho " . $e->getMessage());
			}
		}

		public function addProduct(Product $product) {

			$sql = new Sql();
			$sql->beginTransaction();

			try {
				$sql->queryE("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)", array(
					":idcart"		=> $this->getidcart(),
					":idproduct" 	=> $product->getidproduct()
				));
				$sql->commit();
			} catch (PDOException $e) {

				$sql->rollBack();
				die("Erro ao inserir produto no carrinho " . $e->getMessage());
			}
		}

		public function removeProduct(Product $product, $all = false) {

			$sql = new Sql();
			$sql->beginTransaction();

			if($all) {

				try {

					$sql->queryE("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", array(
						":idcart"		=> $this->getidcart(),
						":idproduct"	=> $product->getidproduct()
					));
					$sql->commit();
				} catch (PDOException $e) {

					$sql->rollBack();
					die("Erro ao remover todos productos do carrinho " . $e->getMessage());
				}
			} else {

				try {
					
					$sql->queryE("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", array(
						":idcart"		=> $this->getidcart(),
						":idproduct"	=>$product->getidproduct()
					));
					$sql->commit();
				} catch (PDOException $e) {

					$sql->rollBack();
					die("Erro ao remover um produto do carrinho " . $e->getMessage());
				}
			}
		}

		public function getProductsForCart(){

			$sql = new Sql();

			try {
				
				$sql->beginTransaction();

				$result = $sql->select(
				    "SELECT 
				   		p.idproduct, 
				   		p.desproduct, 
						p.vlwidth, 
						p.vlheight,
						p.vllength, 
						p.vlweight, 
						p.desurl, 
						p.vlprice, 
						COUNT(*) AS 'quant', 
						SUM(p.vlprice) AS 'total'
					
					FROM 
						tb_cartsproducts c
					
					INNER JOIN 
						tb_products p ON p.idproduct = c.idproduct
					
					WHERE 
						c.idcart = :idcart
					AND 
						c.dtremoved IS NULL

					GROUP BY 
						p.idproduct, 
				   		p.desproduct, 
						p.vlwidth, 
						p.vlheight,
						p.vllength, 
						p.vlweight, 
						p.desurl, 
						p.vlprice
					
					ORDER BY
						p.desproduct
					", array(
						":idcart" => $this->getidcart()
					)
				);

				$sql->commit();
				
				return Product::checkList($result);
			} catch (PDOException $e) {
				
				$sql->rollBack();
				die("Erro ao retornar os Produtos: " . $e->getMessage());
			}
		}

		public function getProductsTotal(){

			$sql = new Sql();

			try {
				
				$sql->beginTransaction();

				$result = $sql->select(
					"SELECT 
						SUM(p.vlprice) 	AS 'vlprice',
						SUM(p.vlwidth) 	AS 'vlwidth',
						SUM(p.vlheight) AS 'vlheight',
						SUM(p.vllength)	AS 'vllength',
						SUM(p.vlweight) AS 'vlweight',
						COUNT(*)		AS 'quant'
					
					FROM 
						tb_products p
					
					INNER JOIN 
						tb_cartsproducts c ON p.idproduct = c.idproduct
					
					WHERE
						c.dtremoved IS NULL
					AND
						c.idcart = :idcart;
					", array(
						":idcart" => $this->getidcart()
					)
				);

				$sql->commit();
				return $result[0] ?? [];

			} catch (PDOException $e) {

				$sql->rollBack();
				die("Erro ao calcular valores dos produtos no carrinho. " . $e->getMessage());
			}
		}

		// Função para calculo do Frete 
		// Nao Usada devido a necessidade de cadastro nos correios
		// public function setFreight($zipcode) {
		// 	// Clean ZIP code
		// 	$zipcode = str_replace("-", "", $zipcode);
		
		// 	// Retrieve product totals
		// 	$totals = $this->getProductsTotal();
		
		// 	// Check if there are products
		// 	if ($totals['quant'] > 0) {
		// 		// Ensure minimum dimensions
		// 		$totals["vlheight"] = max($totals["vlheight"], 2);
		// 		$totals["vllength"] = max($totals["vllength"], 16);
		// 		$totals["vlwidth"] = max($totals["vlwidth"], 11);
		
		// 		// Build the request payload
		// 		$payload = [
		// 			"cepOrigem"    		=> "35338000",
		// 			"cepDestino"   		=> $zipcode,
		// 			"peso"         		=> $totals["vlweight"],
		// 			"comprimento"  		=> $totals["vllength"],
		// 			"altura"       		=> $totals["vlheight"],
		// 			"largura"      		=> $totals["vlwidth"],
		// 			"valorDeclarado" 	=> $totals["vlprice"],
		// 			"maoPropria"   		=> 'S',
		// 			"avisoRecebimento" 	=> 'S'
		// 		];
		
		// 		// Set API URL and headers (adjust as needed based on the new API documentation)
		// 		$url = "https://api.correios.com.br/v1/calculo-frete";
		// 		$headers = [
		// 			"Authorization: Bearer YOUR_API_KEY",
		// 			"Content-Type: application/json"
		// 		];
		
		// 		// Perform the API request
		// 		$ch = curl_init($url);
		// 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// 		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		// 		curl_setopt($ch, CURLOPT_POST, true);
		// 		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		
		// 		$response = curl_exec($ch);
		// 		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		// 		curl_close($ch);
		
		// 		if ($httpCode != 200) {
		// 			// Handle errors
		// 			echo "Failed to retrieve shipping data. HTTP Code: $httpCode";
		// 			return;
		// 		}
		
		// 		// Decode JSON response
		// 		$responseData = json_decode($response, true);
		
		// 		if (json_last_error() !== JSON_ERROR_NONE) {
		// 			// Handle JSON parsing errors
		// 			echo "Failed to parse JSON response.";
		// 			return;
		// 		}
		// 	}
		// }		

		// public function setidcart($value) {
		// 	// error_log("Setting idcart to: " . $value);
		// 	$this->values['idcart'] = isset($value) ? $value : null;
		// }

		// public function setdessessionid($value) {
		// 	// error_log("Setting dessessionid to: " . $value);
		// 	$this->values['dessessionid'] = $value;
		// }

		// public function setiduser($value) {
		// 	// error_log("Setting iduser to: " . $value);
		// 	$this->values['iduser'] = isset($value) ? $value : null;
		// }

		// public function setdeszipcode($value) {
		// 	// error_log("Setting deszipcode to: " . $value);
		// 	$this->values['deszipcode'] = isset($value) ? $value : null;
		// }

		// public function setvlfreight($value) {
		// 	// error_log("Setting vlfreight to: " . $value);
		// 	$this->values['vlfreight'] = isset($value) ? $value : null;
		// }

		// public function setnrdays($value) {
		// 	// error_log("Setting nrdays to: " . $value);
		// 	$this->values['nrdays'] = isset($value) ? $value : null;
		// }

		// public function setdtregister($value) {
		// 	// error_log("Setting dtregister to: " . $value);
		// 	$this->values['dtregister'] = isset($value) ? $value : null;
		// }
	}
?>