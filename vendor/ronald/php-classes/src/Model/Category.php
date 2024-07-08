<?php
	namespace Ronald\Model;

	use \Ronald\DB\Sql;
	use \Ronald\Model;
	use \Ronald\Mailer;

	class Category extends Model {

		public static function listAll(){

			$sql = new Sql();

			return $sql->select("SELECT * FROM tb_categories ORDER BY descategory ASC");
		}

		public function save(){

			$sql = new Sql();

			$result = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
				":idcategory"	=> $this->getidcategory(),
				":descategory" 	=> $this->getdescategory()
			));

			$this->setData($result[0]);
		}

		public function get($idcategory){

			$sql = new Sql();

			$result = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
				":idcategory" => $idcategory
			));

			$this->setData($result[0]);
		}

		public function delete($idcategory){

			$sql = new Sql();
			$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
				":idcategory" => $idcategory
			));
		}
	}
?>