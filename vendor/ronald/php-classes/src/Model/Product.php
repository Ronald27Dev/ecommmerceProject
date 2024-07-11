<?php
	namespace Ronald\Model;

	use \Ronald\DB\Sql;
	use \Ronald\Model;
	use \Ronald\Mailer;

	class Product extends Model {

		public static function listAll() {

			$sql = new Sql();
			$result = $sql->select("SELECT * FROM tb_products ORDER BY desproduct ASC");
            $sql->closeConnection();

			return $result;
		}

		public static function checkList($list){

			foreach($list as &$row){

				$prod = new Product();
				$prod->setData($row);
				$row = $prod->getValues();
			}

			return $list;
		} 

		public function save() {

			$sql = new Sql();
			$sql->beginTransaction();
			
			try{

				$result = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
					":idproduct" 	=> $this->getidproduct(),
					":desproduct"	=> $this->getdesproduct(),
					":vlprice"		=> $this->getvlprice(),
					":vlwidth"		=> $this->getvlwidth(),
					":vlheight"		=> $this->getvlheight(),
					":vllength"		=> $this->getvllength(),
					":vlweight"		=> $this->getvlweight(),
					":desurl"		=> $this->getdesurl()
				));
				$sql->commit();
				$this->setData($result[0]);
			} catch(\Exception $e){

				$sql->rollBack();
				error_log("Erro ao inserir Produto " . $e->getMessage());
			}

            $sql->closeConnection();
		}

		public function get($idproduct){

			$sql = new Sql();

			$result = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(
				":idproduct"	=> $idproduct
			));

			if(isset($result[0])) {
				$this->setData($result[0]);
			} else {
				throw new \Exception("Produto Não Encotrado!");
			}
		
            $sql->closeConnection();
		}

		public function delete($idproduct) {

			$sql = new Sql();

			 try {
		        
		        $sql->beginTransaction();
		        $sql->queryE("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
		            ":idproduct" => $idproduct
        		));
		        $sql->commit();
		    } catch (\Exception $e) {

		        $sql->rollBack();
		        error_log("Erro ao deletar: " . $e->getMessage());
		    }
		
            $sql->closeConnection();
		}

		public function checkPhoto() {
		    $productId = $this->getidproduct();

		    $imageDir = $_SERVER["DOCUMENT_ROOT"] . "/res/site/img/products/";
		    $productImage = $imageDir . $productId . ".jpg";
		    $defaultImage = "/res/site/img/products/noproduct.jpg";

		    if (file_exists($productImage)) {
		        $url = "/res/site/img/products/" . $productId . ".jpg";
		    } else {
		        $url = $defaultImage;
		    }

		    return $this->setdesphoto($url);
		}


		public function getValues(){

			$this->checkPhoto();
			$values = parent::getValues();

			return $values;
		}

		public function setPhoto($file) {
		    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
		    $extension = strtolower($extension);

		    switch($extension) {
		        case "jpg":
		        case "jpeg":
		            $image = imagecreatefromjpeg($file["tmp_name"]);
		            break;
		        case "gif":
		            $image = imagecreatefromgif($file["tmp_name"]);
		            break;
		        case "png":
		            $image = imagecreatefrompng($file["tmp_name"]);
		            break;
		        default:
		            return false;
		    }

		    if (!$image) {
		        return false;
		    }

		    $uploadDir = $_SERVER["DOCUMENT_ROOT"] . "/res/site/img/products/";
		    $filename = $this->getidproduct() . ".jpg";
		    $destination = $uploadDir . $filename;

		    $success = imagejpeg($image, $destination);

		    imagedestroy($image);
		}

	}
?>