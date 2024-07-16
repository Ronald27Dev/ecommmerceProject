<?php
	namespace Ronald\Model;

	use Exception;
	use \Ronald\DB\Sql;
	use \Ronald\Model;
	use \Ronald\Model\User;
	use \Ronald\Mailer;

	class Cart extends Model {

		const SESSION = "cart";

		public static function getFromSession() {

			$cart = new Cart();

			if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){
				$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
			} else {

				$cart->getFromSessionID();

				if(!(int)$cart->getidcart() > 0){

					$data = [
						"dessessionid" => session_id()
					];

					if(User::checkLogin(false)) {

						$user = User::getFromSession();
						$data['iduser'] = $user->getiduser();
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
				$this->setData($result[0]); 

				if(count($result) > 0) {
					$this->setData($result[0]);
				}
			} catch (Exception $e) {

				$sql->rollBack();
				echo "Erro ao pegar o id da sessão " . $e->getMessage();
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

				if(count($result) > 0) {
					$this->setData($result[0]);
				} 
			} catch (Exception $e) {

				$sql->rollBack();
				echo "Erro ao encontrar carrinho " . $e->getMessage();
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
				$sql->commit();	
				$this->setData($result);
			} catch (\Exception $e) {
				
				$sql->rollBack();
				echo "Erro ao inserir produto no carrinho " . $e->getMessage();
			}
		}
	}
?>