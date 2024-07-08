<?php 
	namespace Ronald;

	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

	require 'vendor/autoload.php';
	
	use Rain\Tpl;

	class Mailer {

		const USERNAME = "ronald.mrodrigues02@gmail.com";
		const PASSWORD = "passwordToThis";
		const NAME_FROM = "E-commerce";

		private $mail;

		public function __construct($toAddress, $toName, $subject, $tplName, $data = array()) {
				
			$config = array(
				"tpl_dir" 		=> $_SERVER["DOCUMENT_ROOT"]."/views/email/",
				"cache_dir"		=> $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
				"debug"			=> false
			);

			Tpl::configure( $config );

			$tpl = new Tpl();

			foreach ($data as $key => $value) {

				$tpl->assign($key, $value);
			}

			$html = '';
			try {

			    $html = $tpl->draw($tplName, true);
			} catch (\Exception $e) {

			    error_log('Error loading template: ' . $e->getMessage());
			}

			if (empty($html)) {
			    
			    error_log('Template ' . $tplName . ' not found or failed to load.');
			}


			$this->mail = new \PHPMailer(true);

			$this->mail->isSMTP();

			$this->mail->SMTPDebug 		= false;
			$this->mail->Host 			= 'smtp.gmail.com';
			$this->mail->Port 			= 587;

			$this->mail->SMTPOptions 	= array(
				'ssl' => array(
					'verify_peer' 		=> false,
					'verify_peer_name'	=> false,
					'allow_sef_signed'	=> true 
				)
			);

			$this->mail->SMTPSecure 	= 'tls';
			$this->mail->SMTPAuth 		= true;
			$this->mail->Username 		= Mailer::USERNAME;
			$this->mail->Password 		= Mailer::PASSWORD;
			$this->mail->Subject 		= $subject;

			$this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);
			$this->mail->addAddress($toAddress, $toName);
			$this->mail->msgHTML($html); 
			$this->mail->AltBody = 'This is a plain-text message body';
		}

		public function send(){
			try {
    			
    			// Attempt to send the email
			    echo 'Email has been sent';
			    return $this->mail->send();
			} catch (\Exception $e) {

			    echo 'Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo;
			}
		}
	}
?>