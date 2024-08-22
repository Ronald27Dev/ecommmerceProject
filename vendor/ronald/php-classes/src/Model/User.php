<?php 
    namespace Ronald\Model;

use PDOException;
use \Ronald\DB\Sql;
    use \Ronald\Model;
    use \Ronald\Mailer;

    class User extends Model{

        const SESSION           = "User";
        const SECRETKEY         = "constForSecretKey";
        const ERROR             = "UserError";
        const SUCCESS           = "UserSuccess";
        const ERROR_REGISTER    = "UserErrorRegister";

        private static $staticIv;

        public static function checkLogin($inadmin = true) {

            if(
                !isset($_SESSION[User::SESSION])                ||
                !$_SESSION[User::SESSION]                       ||
                !(int)$_SESSION[User::SESSION]['iduser'] > 0
            ) {
                //Não está logado
                return false;
            } else {
                
                if($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {
                
                    return true;
                } else if($inadmin === false) {
                
                    return true;
                } else {
                
                    return false;
                }
            }
        }

        public static function getFromSession(){

            $user = new User();
            if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){

                $user->setData($_SESSION[User::SESSION]);
            }
            return $user; 
        }

        public static function login($login, $password){

            $sql = new Sql();

            $results = $sql->select("SELECT * FROM tb_users u INNER JOIN tb_persons p ON p.idperson = u.idperson  WHERE deslogin = :LOGIN", array(
                ":LOGIN" => $login
            ));

            if(count($results) === 0){

                throw new \Exception("Usuario Inexistente ou Senha Invalida.");
            }

            $data = $results[0];

            if(password_verify($password, $data["despassword"]) === true){

                $user = new User();
                $user->setData($data);
                
                $_SESSION[User::SESSION] = $user->getValues();
                

                return $user;
            } else {

                throw new \Exception("Usuario Inexistente ou Senha Invalida.");
            }

        } 

        public static function verifyLogin($inadmin = true) {

            if(
                !isset($_SESSION[User::SESSION])                        ||
                !$_SESSION[User::SESSION]                               ||
                !(int)$_SESSION[User::SESSION]["iduser"] > 0            ||
                (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
            ){

                header("Location: /admin/login");
                exit;
            }
        }

        public static function logout(){

            $_SESSION[User::SESSION] = NULL;
        }

        public static function listAll() {

            $sql = new Sql();
            $result = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson ORDER BY b.desperson ASC");

            return $result;
        }

        public function save() {

            $sql = new Sql();
            $sql->beginTransaction();

            try{
                $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",
                    array(
                        ":desperson"    => $this->getdesperson(),
                        ":deslogin"     => $this->getdeslogin(),
                        ":despassword"  => $this->getdespassword(),
                        ":desemail"     => $this->getdesemail(),
                        ":nrphone"      => $this->getnrphone(),
                        ":inadmin"      => $this->getinadmin()
                    )
                );
                
                $sql->commit();
                $this->setData($results[0]);
            } catch (\Exception $e) {

                $sql->rollBack();
                throw new \Exception("Erro ao Salva Usuario " . $e->getMessage());
            }

        }

        public function get($iduser) {
            $sql = new Sql();

            $result = $sql->select("SELECT * FROM tb_users u INNER JOIN tb_persons p ON u.idperson = p.idperson WHERE u.iduser = :iduser", array(
                ":iduser" => $iduser
            ));

            $this->setData($result[0]); 
        }

        public function update() {
            $sql = new Sql();
            $sql->beginTransaction();

            try{
                $result = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",
                    array(
                        ":iduser"       => $this->getiduser(),
                        ":desperson"    => $this->getdesperson(),
                        ":deslogin"     => $this->getdeslogin(),
                        ":despassword"  => $this->getdespassword(),
                        ":desemail"     => $this->getdesemail(),
                        ":nrphone"      => $this->getnrphone(),
                        ":inadmin"      => $this->getinadmin()
                    )
                );
                $this->setData($result[0]);
                $sql->commit();
            } catch (\Exception $e) {

                $sql->rollBack();
                throw new \Exception("Erro ao Atualizar Usuario " . $e->getMessage());
                 
            }

        }

        public function delete(){

            $sql = new Sql();
            $sql->beginTransaction();

            try{
                $sql->queryE("CALL sp_users_delete(:iduser)", array(
                    ":iduser" => $this->getiduser()
                ));
                $sql->commit();
            } catch (\Exception $e) {

                $sql->rollBack();
                throw new \Exception("Erro ao Deletar Usuario " . $e->getMessage());
            }
        
        }

        public static function forgotPassword($email, $inadmin = true) {

            $sql = new Sql();

            $result = $sql->select(
                "SELECT * 
                FROM 
                    tb_persons a 
                INNER JOIN 
                    tb_users b ON a.idperson = b.idperson 
                WHERE 
                    a.desemail = :desemail",
                array(
                    ":desemail" => $email
                )
            );

            if(!isset($result[0])) {

                throw new \Exception("Não foi possivel recuperar a senha");
            } else {
                
                $data = $result[0];

                $recovery = $sql->select("CALL  sp_userspasswordsrecoveries_create(:iduser, :desip)", 
                    array(
                        ":iduser"   => $data["iduser"],
                        ":desip"    => $_SERVER["REMOTE_ADDR"]
                    )
                );

                if(count($recovery) == 0) {
                    
                    throw new \Exception("Não foi possivel recuperar a senha");
                } else {

                    $dataRecovery = $recovery[0];

                    $code = base64_encode(openssl_encrypt($dataRecovery["idrecovery"], 'aes-256-cbc', User::SECRETKEY, 0, self::getStaticIv()));

                    if($inadmin === true) {
                        
                        $link = "http//www.ecommerce.com.br/admin/forgot/reset?code=$code";
                    } else {
                        
                        $link = "http//www.ecommerce.com.br/forgot/reset?code=$code";
                    }
                    $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha de Login", "forgot",
                        array(
                            "name"  => $data["desperson"],
                            "link"  => $link
                        )
                    );

                    $mailer->send();

                    return $data;
                }
            }

        }

        private static function getStaticIv() {
            
            if (!isset(self::$staticIv)) {
                self::$staticIv = openssl_random_pseudo_bytes(16);
            }
            return self::$staticIv;
        }

        public static function validForgotDecrypt($code){

            $decoded = openssl_decrypt(base64_decode($code), 'aes-256-cbc', User::SECRETKEY, 0, self::getStaticIv());

            $sql = new Sql();

            $result = $sql->select(
                "SELECT * 
                FROM tb_userspasswordsrecoveries r 
                INNER JOIN tb_users u ON r.iduser = u.iduser 
                INNER JOIN tb_persons p ON p.idperson = u.idperson 
                WHERE
                    idrecovery = :idrecovery
                AND
                    DATE_ADD(r.dtregister, INTERVAL 1 HOUR) >= NOW()
                AND 
                    a.dtrecovery IS NULL;
            ", array(
                ":idrecovery" => $decoded
            ));

            if (!isset($result[0])) {
                
                throw new \Exception("Não foi possivel recuperar a senha.");
            } else {
                
                return $result[0];
            }
        
        }

        public static function setForgotUsed($idrecovery){
            $sql = new Sql();
            $sql->beginTransaction();

            try {
                $sql->queryE(
                    "UPDATE tb_userspasswordsrecoveries 
                    SET dtrecovery = NOW() 
                    WHERE idrecovery = :idrecovery
                ", array(
                    ":idrecovery" => $idrecovery
                ));
                $sql->commit();
            } catch (\Exception $e) {

                $sql->rollBack();
                throw new \Exception("Não foi possivel alterar a recuperacao de senha " . $e->getMessage());
            }

        }

        public function setPassword($password){

            $sql = new Sql();
            $sql->beginTransaction();

            try{
                $sql->queryE(
                    "UPDATE tb_users 
                    SET despassword = :password
                    WHERE iduser    = :iduser
                ", array(
                    ":password" => $password,
                    ":iduser"   => $this->getiduser()
                ));
                $sql->commit();
            } catch (\Exception $e) {

                $sql->rollBack();
                throw new \Exception("Erro em alterar a senha " . $e->getMessage());
            }
        
        }
    
        public static function setError($msg) {

            $_SESSION[User::ERROR] = $msg;
        }

        public static function getError() {

            $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

            User::clearError();

            return $msg;
        }

        
        public static function clearError() {
            
            $_SESSION[User::ERROR] = NULL;
        }
        
        public static function clearErrorRegister() {
            
            $_SESSION[User::ERROR_REGISTER] = NULL;
        }

        public static function getErrorRegister() {

            $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

            User::clearErrorRegister();

            return $msg;
        }

        public static function setErrorRegister($msg) {

            $_SESSION[User::ERROR_REGISTER] = $msg;
        }

        public static function setSuccess($msg) {

            $_SESSION[User::SUCCESS] = $msg;
        }

        public static function getSuccess() {

            $msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';

            User::clearSuccess();

            return $msg;
        }

        
        public static function clearSuccess() {
            
            $_SESSION[User::SUCCESS] = NULL;
        }

        public static function checkLoginExist($login, $senha = '') {

            $sql = new Sql();

            try{
                $sql->beginTransaction();

                $result = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin OR desenha = :desenha", array(
                    ":deslogin" => $login,
                    ":dessenha" => $senha
                ));
                $sql->commit();
                
                return (count($result) > 0);
            } catch (PDOException $e) {

                $sql->rollBack();
                throw new PDOException("Erro ao checar login");
            }        
        }
    }
?>