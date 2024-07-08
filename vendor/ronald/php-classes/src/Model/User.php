<?php 

    namespace Ronald\Model;

    use \Ronald\DB\Sql;
    use \Ronald\Model;
    use \Ronald\Mailer;

    class User extends Model{

        const SESSION = "User";
        const SECRETKEY = "constForSecretKey";

        private static $staticIv;

        public static function login($login, $password){

            $sql = new Sql();

            $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(":LOGIN"=>$login));

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

            return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson ORDER BY b.desperson ASC");
        }

        public function save() {

            $sql = new Sql();

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

            $this->setData($results[0]);
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
        }

        public function delete(){

            $sql = new Sql();

            $sql->query("CALL sp_users_delete(:iduser)", array(
                ":iduser" => $this->getiduser()
            ));
        }

        public static function forgotPassword($email) {

            $sql = new Sql();

            $result = $sql->select("
                SELECT * 
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

                    $link = "http//www.ecommerce.com.br/admin/forgot/reset?code=$code";
                
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

            $result = $sql->select("
                SELECT * 
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

            $sql->query("
                UPDATE tb_userspasswordsrecoveries 
                SET dtrecovery = NOW() 
                WHERE idrecovery = :idrecovery
            ", array(
                ":idrecovery" => $idrecovery
            ));
        }

        public function setPassword($password){

            $sql = new Sql();

            $sql->query("
                UPDATE tb_users 
                SET despassword = :password
                WHERE iduser    = :iduser
            ", array(
                ":password" => $password,
                ":iduser"   => $this->getiduser()
            ));
        }
    }
?>




<!-- 





SELECT *
FROM `fin_parcelaformapagamentodesconto`
WHERE `iddescontoclassificacao` =4
AND `iddescontotipo` =2
LIMIT 0 , 30
 -->