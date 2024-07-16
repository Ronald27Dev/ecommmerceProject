<?php
	namespace Ronald\Model;

	use Exception;
	use \Ronald\DB\Sql;
	use \Ronald\Model;
	use \Ronald\Mailer;

	class Category extends Model {

		public static function listAll(){

			$sql = new Sql();
			$result = $sql->select("SELECT * FROM tb_categories ORDER BY descategory ASC");
            $sql->closeConnection();

            return $result;
		}

		public function save() {

			$sql = new Sql();
			$sql->beginTransaction();

			try{

				$result = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
					":idcategory"	=> $this->getidcategory(),
					":descategory" 	=> $this->getdescategory()
				));
            	
            	$sql->commit();
            	$this->setData($result[0]);
			} catch (\Exception $e) {

				$sql->rollBack();
				echo "Erro ao salvar categoria " . $e->getMessage();
			}

			Category::updateFile();

			$sql->closeConnection();
		}

		public function get($idcategory) {

			$sql = new Sql();
			$result = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
				":idcategory" => $idcategory
			));
			
			if(isset($result[0])) {
				$this->setData($result[0]);
			} else {

            	$sql->closeConnection();
				throw new \Exception("Categoria Não Encotrada!");
			}

            $sql->closeConnection();
		}

		public function delete($idcategory){

			$sql = new Sql();
			$sql->beginTransaction();

			try{

				$sql->queryE("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
					":idcategory" => $idcategory
				));
				$sql->commit();
			} catch (\Exception $e) {

				$sql->rollBack();
				echo "Erro ao deletar " . $e->getMessage();
			}

			Category::updateFile();

            $sql->closeConnection();
		}

		public static function updateFile() {
		    $categories = Category::listAll();

		    $html = [];

		    foreach ($categories as $row) {
		        $html[] = '<li><a href="/categories/' . $row['idcategory'] . '">' . $row['descategory'] . '</a></li>';
		    }

		    $filePath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html";
		    $result = file_put_contents($filePath, implode('', $html));

		    if ($result === false) {
		        echo "Failed to write to file: " . $filePath;
		        return false;
		    } else {
		        echo "Successfully updated file: " . $filePath;
		    }
		}

		public function getProducts($related = true){

			$sql = new Sql();

			if($related === true) {
				return $sql->select("
					SELECT * 
					FROM tb_products
					WHERE idproduct IN(
						SELECT a.idproduct
						FROM tb_products a
						INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
						WHERE b.idcategory = :idcategory
					)", 
					array(
						":idcategory" => $this->getidcategory()
				));
			} else {
				return $sql->select("
					SELECT * 
					FROM tb_products
					WHERE idproduct NOT IN(
						SELECT a.idproduct
						FROM tb_products a
						INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
						WHERE b.idcategory = :idcategory
					)",
					array(
						":idcategory" => $this->getidcategory()
				));
			}
		}

		public function updateProductsList($update, $idcategory, $idproduct){

			$sql = new Sql();
			$sql->beginTransaction();
			
			if($update === "remove"){
				
				try{
					
					$sql->queryE("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", array(
						":idcategory" 	=> $idcategory,
						":idproduct"	=> $idproduct
					));
					$sql->commit();
				} catch (\Exception $e) {
					
					$sql->rollBack();
					echo "Erro ao mudar produto de categoria " . $e->getMessage();
				}
			} elseif($update === "add"){
				
				try {
					
					$sql->queryE("
						INSERT INTO tb_productscategories(idcategory, idproduct) VALUES(:idcategory, :idproduct)
					", array(
						":idcategory"	=> $idcategory,
						":idproduct" 	=> $idproduct
					));
					$sql->commit();
				} catch (\Exception $e) {

					$sql->rollBack();
					echo "Erro ao mudar produto de categoria " . $e->getMessage();
				}
			} else {
				throw new \Exception("Alteração Invalida!");
			}
		}

		public function getProductsPage($page = 1, $itemsPerPage = 8){

			$start = ($page-1) * $itemsPerPage;

			$sql = new Sql();
			$sql->beginTransaction();
			
			try{

				$result = $sql->select("
					SELECT SQL_CALC_FOUND_ROWS *
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					INNER JOIN tb_categories c ON c.idcategory = b.idcategory
					WHERE c.idcategory = :idcategory
					LIMIT $start, $itemsPerPage 
				", array(
					":idcategory" => $this->getidcategory()
				));

				$total = $sql->select("SELECT FOUND_ROWS() AS nrtotal");
				$sql->commit();

				return array(
					"data" 	=> Product::checkList($result),
					"total"	=> $total[0]["nrtotal"],
					"pages"	=> ceil($total[0]["nrtotal"] / $itemsPerPage)
				);
			} catch (\Exception $e) {
				$sql->rollBack();
				echo "Erro ao Buscar Produtos " . $e->getMessage();
			}
		}
	}
?>