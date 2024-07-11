<?php
	namespace Ronald\Model;

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
	}
?>