<?php 
	namespace Ronald;

	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;
	use Rain\Tpl;

	require 'vendor/autoload.php';

	class Mailer {

		const USERNAME = "email@example.com"; 
		const PASSWORD = "password"; 
		const NAME_FROM = "E-commerce";

		private $mail;

		public function __construct($toAddress, $toName, $subject, $tplName, $data = array()) {
			
			$config = array(
				"tpl_dir"    => $_SERVER["DOCUMENT_ROOT"] . "/views/email/",
				"cache_dir"  => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/",
				"debug"      => false
			);

			Tpl::configure($config);

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
			$this->mail->SMTPDebug 	= false;
			$this->mail->Host 		= 'smtp.gmail.com';
			$this->mail->Port 		= 587;

			$this->mail->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' 		=> false,
					'verify_peer_name' 	=> false,
					'allow_self_signed' => true
				)
			);

			$this->mail->SMTPSecure = 'tls';
			$this->mail->SMTPAuth = true;
			$this->mail->Username = self::USERNAME;
			$this->mail->Password = self::PASSWORD;
			$this->mail->Subject = $subject;

			$this->mail->setFrom(self::USERNAME, self::NAME_FROM);
			$this->mail->addAddress($toAddress, $toName);
			$this->mail->msgHTML($html); 

			// Generate a plain-text version of the email content
			$this->mail->AltBody = strip_tags($html); 
		}

		public function send() {
			try {
				if ($this->mail->send()) {
					return 'Email has been sent';
				} else {
					return 'Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo;
				}
			} catch (\Exception $e) {
				return 'Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo;
			}
		}
	}
?>