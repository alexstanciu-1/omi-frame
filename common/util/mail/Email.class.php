<?php

namespace Omi\Util;


/**
 * Description of Email
 *
 * @class.name Email
 */
abstract class Email_omi_util_ extends \QModel
{
	public $from;
	public $to;
	public $subject;
	public $templateClass;
	public $headers;
	protected $message;
	protected $headersStr;
	protected $attachments;
	protected $contentTypeStr = "text/plain";
	protected $reply_to = [];
	protected $from_alias = null;
	
	public $is_smtp = false;
	
	public $smtp_host = null;
	public $smtp_port = null;
	public $smtp_username = null;
	public $smtp_password = null;
	public $smtp_encryption = "tls";
	
	/**
	 * imap or pop3 server connection
	 *
	 * @var mixed
	 */
	public $imapConnection;
	public $imapServer = null; // 'yourserver.com'
	public $imapUser   = null; // 'email@yourserver.com'
	public $imapPass   = null; // 'password'
	public $imapPort   = 143;
	
	protected $inbox;
	protected $msg_cnt;
	
	public function __construct($to = null, $subject = null)
	{
		if ($to)
			$this->to = $to;
		if ($subject)
			$this->subject = $subject;
	}

    protected function _send()
    {
        if ((!$this->templateClass && !$this->message) || !$this->to || !$this->subject)
		{
			\QErrorHandler::LogError(new \Exception("Insufficient parameters for sending email!"));
			return false;
            # throw new \Exception("Insufficient parameters for sending email!");
		}

		if (!$this->headers)
			$this->headers = array();

		if (!isset($this->headers["From"]) && !$this->from)
			throw new \Exception("From email must be defined!");

		if ($this->from && !isset($this->headers["From"]))
			$this->headers["From"] = $this->from;
		else
			$this->from = $this->headers["From"];

		if ($this->templateClass)
        {
            $template = new $this->templateClass;
            $template->init();
            ob_start();
            $template->render();
            $this->message = ob_get_clean();
        }

		//$forceSmtp = (defined("EMAIL_SMTP") && EMAIL_SMTP);
		$forceSmtp = false;
		
		if ((!$this->is_smtp && !$forceSmtp)) #  || (empty($this->smtp_username)))
		{
			$has_attach = $this->hasAttachments();

			if ($has_attach)
			{
				//create boundary
				$mime_boundary = "==Multipart_Boundary_x" . md5(time()) . "x";
				//change headers for multipart data = attachments
				$this->headers['MIME-Version'] = '1.0';
				$this->headers['Content-Type'] = 'multipart/mixed; boundary = "'.$mime_boundary.'"';
			}
			else
				$this->headers['Content-Type'] = $this->contentTypeStr;

			$this->reloadHeaders();

			if ($has_attach)
			{
				$message = $this->message;
				$message = "This is a multi-part message in MIME format.\n\n" . "--{$mime_boundary}\n" . "Content-Type: ".$this->contentTypeStr."; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n"; 
				$message .= "--{$mime_boundary}\n";

				//include attachments
				foreach ($this->attachments as $filename)
				{
					$file = fopen($filename, "rb");
					$data = fread($file, filesize($filename));
					fclose ($file);
					$data2 = chunk_split(base64_encode($data));
					$message .= "Content-Type: {\"application/octet-stream\"};\n" . " name=\"".basename($filename)."\"\n" . 
					"Content-Disposition: attachment;\n" . " filename=\"".basename($filename)."\"\n" . 
					"Content-Transfer-Encoding: base64\n\n" . $data2 . "\n\n";
					$message .= "--{$mime_boundary}\n";
				}
				$this->message = $message;			
			}
			return mail($this->to, $this->subject, $this->message, $this->headersStr);
		}
		else 
		{
			return $this->_sendAuthMail();
		}
    }

	protected function _sendAuthMail()
	{
		if (!$this->smtp_host && defined("MAIL_SMTP_HOST"))
			$this->smtp_host = MAIL_SMTP_HOST;

		if (!$this->smtp_encryption && defined("MAIL_SMTP_ENCR"))
			$this->smtp_encryption = MAIL_SMTP_ENCR;

		if (!$this->smtp_port && defined("MAIL_SMTP_PORT"))
			$this->smtp_port = MAIL_SMTP_PORT;

		if (!$this->smtp_username && defined("MAIL_SMTP_USER"))
			$this->smtp_username = MAIL_SMTP_USER;

		if (!$this->smtp_password && defined("MAIL_SMTP_PASS"))
			$this->smtp_password = MAIL_SMTP_PASS;

		//if (!$this->from && defined("MAIL_SMTP_USER"))
		//	$this->from = MAIL_SMTP_USER;

		$this->from = $this->smtp_username;

		return static::SMTPMail($this->smtp_host, $this->smtp_port, $this->smtp_encryption, $this->smtp_username, $this->smtp_password, $this->from, 
			$this->to, $this->subject, $this->message, $this->attachments, $this->from_alias, $this->reply_to);
	}

	/**
	 * Sends SMTP Mail
	 * 
	 * @param string $host
	 * @param int $port
	 * @param string $encryption
	 * @param string $username
	 * @param string $password
	 * @param string $from
	 * @param string $email
	 * @param string $subject
	 * @param string $message
	 * @param array $attachments
	 * @param string $fromAlias
	 * @param array $replyTo
	 * @param int $debugState
	 * @throws \Exception
	 */
	public static function SMTPMail($host, $port, $encryption, $username, $password, $from, $email, $subject, $message = "", $attachments = [], 
		$fromAlias = null, $replyTo = [], $debugState = 0)
	{
		$dir = dirname(__FILE__);
		
		$php_mailer_class = 'PHPMailer\PHPMailer\PHPMailer';
		
		require_once($dir."/include/class.phpmailer.php");
		require_once($dir."/include/class.smtp.php"); 

		// optional, gets called from within class.phpmailer.php if not already loaded
		if (!class_exists($php_mailer_class))
			throw new \Exception("Mailer class not found for sending auth email!");

		if (!$host || !$port || !$username || !$password || !$from || !$email || !$encryption)
			throw new \Exception("Sending email failed!\nSystem emailing is down!\nPlease try again later!\nThanks!");

		if (!$host)
			throw new \Exception("Sending AUTH Email Failed!\nHost not set!");

		if (!$port)
			throw new \Exception("Sending AUTH Email Failed!\nPort not set!");

		if (!$encryption)
			throw new \Exception("Sending AUTH Email Failed!\nEncryption not set!");

		if (!$username)
			throw new \Exception("Sending AUTH Email Failed!\nUsername not set!");
		
		if (!$password)
			throw new \Exception("Sending AUTH Email Failed!\nPassword not set!");

		if (!$from)
			throw new \Exception("Sending AUTH Email Failed!\nFrom not set!");		
		
		try 
		{
			$mail = new $php_mailer_class();

			//Tell PHPMailer to use SMTP
			$mail->isSMTP();

			// setup mail timeout
			$mail->Timeout = 10;

			//$debugState = 2;

			//Enable SMTP debugging
			// 0 = off (for production use)
			// 1 = client messages
			// 2 = client and server messages
			$mail->SMTPDebug = $debugState;

			//Ask for HTML-friendly debug output
			$mail->Debugoutput = 'html';

			// use
			// $mail->Host = gethostbyname('smtp.gmail.com');
			// if your network does not support SMTP over IPv6

			//Set the encryption system to use - ssl (deprecated) or tls
			$mail->SMTPSecure = $encryption;

			//Set the hostname of the mail server
			$mail->Host = $host;

			//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
			$mail->Port = $port;

			// Opportunistic TLS
			$mail->SMTPAutoTLS = false;

			// Set smtp options
			$mail->SMTPOptions = [
				'ssl' => [
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				]
			];

			//Whether to use SMTP authentication
			$mail->SMTPAuth = true;

			//Username to use for SMTP authentication - use full email address for gmail
			$mail->Username = $username;

			//Password to use for SMTP authentication
			$mail->Password = $password;

			//Set who the message is to be sent from
			$mail->setFrom($from, $fromAlias);

			//Set an alternative reply-to address
			if ($replyTo && (count($replyTo) > 0))
			{
				foreach ($replyTo as $rt)
					$mail->addReplyTo($rt);
			}

			//Set who the message is to be sent to
			$mail->addAddress($email);

			//Set the subject line
			$mail->Subject = $subject;

			//Read an HTML message body from an external file, convert referenced images to embedded,
			//convert HTML into a basic plain-text alternative body
			$mail->msgHTML($message);

			//Replace the plain text body with one created manually
			$alt = strip_tags($message);
			$mail->AltBody = (strlen($alt) > 35) ? substr($alt, 0, 35)."..." : $alt;

			if ($attachments && (count($attachments) > 0))
			{
				foreach ($attachments as $file)
					$mail->addAttachment($file);
			}
			
			if (defined('Q_MAIL_SEND_CHAR_SET') && Q_MAIL_SEND_CHAR_SET)
				$mail->CharSet = Q_MAIL_SEND_CHAR_SET;
			if (defined('Q_MAIL_SEND_ENCODING') && Q_MAIL_SEND_ENCODING)
				$mail->Encoding = Q_MAIL_SEND_ENCODING;

			//send the message, check for errors
			# ob_start();
			$sent = $mail->send();
			# $inf = ob_get_clean();
			# if (\QAutoload::GetDevelopmentMode())
				# qvar_dumpk("dev", $sent, $inf);

			//qvardump($sent);
			//die();

			//qvardump("MAIL WAS SENT:", $sent);
			//die();

			//if (!$sent)
			//	throw new \Exception("Sending email failed!\n");

			return $sent;
		}
		catch (\Exception $ex)
		{
			throw $ex;
		}

		return false;
	}

    public function setTextMessage($text_message)
    {
        $this->message = $text_message;
    }
	
	public function setFrom($from)
	{
		$this->from = $from;
	}

    public function setTemplateClass($templateClass)
    {
        $this->templateClass = $templateClass;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
        $this->reloadHeaders();
    }

    public function setAsHTML()
    {
        $this->contentTypeStr = "text/html";
    }
	/**
	 * reload headers
	 */
    public function reloadHeaders()
    {
        $this->headersStr = "";
        $headers = $this->headers;
        if ($headers && !empty($headers))
		{
            foreach ($headers as $key => $value)
				$this->headersStr.= "{$key}: {$value}\r\n";
		}
    }
    /**
	 * 
	 * @param type $atts
	 * @return boolean
	 * @throws \Exception
	 */
    public function addAttachments($atts)
    {
        if (!$this->attachments)
            $this->attachments = array();
        
        if ($atts && !empty($atts))
        {
            foreach ($atts as $filename)
            {
                if (file_exists($filename))
                    $this->attachments[] = $filename;
                else
					throw new \Exception("File: {$filename} cannot be found!");
            }
            
            return count($atts);
        }
        return false;
    }
	/**
	 * 
	 * @return boolean
	 */
    public function hasAttachments()
    {
        return (!$this->attachments || empty($this->attachments)) ? 0 : count($this->attachments);
    }
	/**
	 * Returns true if an email is valid, false otherwise
	 * 
	 * @param string $email
	 * @return boolean
	 */
	public static function IsValid($email)
	{
		return (filter_var($email, FILTER_VALIDATE_EMAIL) !== false);
	}
	/**
	 * @api.enable
	 * 
	 * @param \Omi\MailSender|string $mailSender
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 * @param string $attachments
	 * @param string $headers
	 * @param boolean $isHtml
	 * @return boolean
	 */
	public static function Send($mailSender, $to, $subject, $message, $attachments = [], $headers = [], $isHtml = true)
	{
		if (!($to && filter_var($to, FILTER_VALIDATE_EMAIL)))
		{
			return false;
		}
		
		if ((!defined("IS_LIVE")) || (!IS_LIVE))
		{
			if (!Dev_Email)
				return;

			$origMail = $to;
			$to = Dev_Email;
			$subject .= " - [to {$origMail}]";
		}

		$mail = new Email($to, $subject);		
		$mail->is_smtp = true;
		
		$initial_mailSender = $mailSender;

		if (($mailSender !== null) && is_object($mailSender))
		{
			# it is object
		}
		else
		{
			// get mail sender
			$mailSender = ($q = \QApi::Query("MailsSenders")) ? $q[0] : null;
			
			if (!$mailSender)
			{
				$current_owner = \Omi\User::GetCurrentUser() ? \Omi\App::GetCurrentOwner() : null;
				
				if (!$current_owner)
				{
					if ((!$mailSender) && defined('APP_DEFAULT_MAIL_ACCOUNT') && APP_DEFAULT_MAIL_ACCOUNT['Host'] && APP_DEFAULT_MAIL_ACCOUNT['Username'])
					{
						$mailSender = new \stdClass();
						$mailSender->Host = APP_DEFAULT_MAIL_ACCOUNT['Host'];
						$mailSender->Port = APP_DEFAULT_MAIL_ACCOUNT['Port'];
						$mailSender->Username = APP_DEFAULT_MAIL_ACCOUNT['Username'];
						$mailSender->Password = APP_DEFAULT_MAIL_ACCOUNT['Password'];
						$mailSender->Encryption = APP_DEFAULT_MAIL_ACCOUNT['Encryption'];
					}
					else
					{
						$mailSender = \QQuery("MailsSenders.{* WHERE Owner.Id=? LIMIT 1}", [\Omi\App::GetHolder()->Id])->MailsSenders;
						$mailSender = isset($mailSender[0]) ? $mailSender[0] : null;
					}
				}
				else
				{
					while ((!$mailSender) && $current_owner)
					{
						$in_partner = \QQuery("Partners.{* WHERE Id=?}", $current_owner->Gid)->Partners;
						if (!$in_partner && $in_partner[0] && $in_partner[0]->Owner)
							break;
						$partner_id = q_reset($in_partner)->Owner->Id;
						if (defined('MAIL_SMTP_HOST') && defined('Omi\User::NFON_ID') && ($partner_id == \Omi\User::NFON_ID))
						{
							$mailSender = new \stdClass();
							$mailSender->Host = MAIL_SMTP_HOST;
							$mailSender->Port = MAIL_SMTP_PORT;
							$mailSender->Username = MAIL_SMTP_USER;
							$mailSender->Password = MAIL_SMTP_PASS;
							$mailSender->Encryption = MAIL_SMTP_ENCR;
						}
						else if (defined('CLOUDPBX_SMTP_HOST') && defined('Omi\User::CLOUDPBX_WH_ID') && defined('Omi\User::CLOUDPBX_DL_ID') && 
									(($partner_id == \Omi\User::CLOUDPBX_WH_ID) || ($partner_id == \Omi\User::CLOUDPBX_DL_ID)))
						{
							$mailSender = new \stdClass();
							$mailSender->Host = CLOUDPBX_SMTP_HOST;
							$mailSender->Port = CLOUDPBX_SMTP_PORT;
							$mailSender->Username = CLOUDPBX_SMTP_USER;
							$mailSender->Password = CLOUDPBX_SMTP_PASS;
							$mailSender->Encryption = CLOUDPBX_SMTP_ENCR;
						}
						else
						{
							// $partner = \QQuery("Companies.{* WHERE Id=?}", $partner_id)->Companies;
							$mailSender_list = \QQuery('MailsSenders.{* WHERE Owner.Id=?}', $partner_id)->MailsSenders;
							if ($mailSender_list && q_reset($mailSender_list))
								$mailSender = q_reset($mailSender_list);

							$current_owner = \QQuery("Companies.{* WHERE Id=?}", $partner_id)->Companies;
							$current_owner = $current_owner ? q_reset($current_owner) : null;
						}
					}
				}
			}
		}
		
		if ((!$mailSender) && defined('APP_DEFAULT_MAIL_ACCOUNT') && APP_DEFAULT_MAIL_ACCOUNT['Host'] && APP_DEFAULT_MAIL_ACCOUNT['Username'])
		{
			$mailSender = new \stdClass();
			$mailSender->Host = APP_DEFAULT_MAIL_ACCOUNT['Host'];
			$mailSender->Port = APP_DEFAULT_MAIL_ACCOUNT['Port'];
			$mailSender->Username = APP_DEFAULT_MAIL_ACCOUNT['Username'];
			$mailSender->Password = APP_DEFAULT_MAIL_ACCOUNT['Password'];
			$mailSender->Encryption = APP_DEFAULT_MAIL_ACCOUNT['Encryption'];
		}
		
		if (!$mailSender)
			return;
		
		$mail->smtp_host = $mailSender->Host;
		$mail->smtp_port = $mailSender->Port;
		$mail->smtp_username = $mailSender->Username;
		$mail->smtp_password = $mailSender->Password;
		$mail->smtp_encryption = $mailSender->Encryption;
		$mail->from_alias = $mailSender->FromAlias;
		$mail->reply_to = $mailSender->ReplyTo;

		$mail->setFrom(is_string($initial_mailSender) ? $initial_mailSender : ($mailSender->Email ?: $mailSender->Username));

		$mail->setTextMessage($message);
		$mail->setHeaders($headers);
		
		if ($isHtml)
			$mail->setAsHTML();

		try
		{
			$owner = \Omi\App::GetCurrentOwner();
		}
		catch (\Exception$ex)
		{
			$owner = null;
		}

		$_unlink_attachments = [];
		foreach ($attachments ?: [] as $k => $attach)
		{
			$fn = null;
			if (is_array($attach))
				list($attach, $fn) = $attach;

			$is_url = (substr($attach, 0, 7) == "http://") || (substr($attach, 0, 8) == "https://");
			if ($is_url)
			{
				// setup the extension if it doesn't match
				if (!$fn)
				{
					$fn = substr($attach, strrpos($attach, '/') + 1);
				}
				else if (!pathinfo($fn, PATHINFO_EXTENSION))
				{
					$ext = strrpos($attach, '.') ? substr($attach, strrpos($attach, '.') + 1) : "";
					if ($ext)
						$fn	.= ".".$ext;
				}
			}
			else 
			{
				if (!$fn)
					$fn = basename($attach);
				
				if (!file_exists($attach))
					throw new \Exception('Can not find attached file: '.($attach ? basename($attach) : '[empty]'));

				// setup the extension if it doesn't match
				$ext = pathinfo($attach, PATHINFO_EXTENSION);
				$_fn_ext = pathinfo($fn, PATHINFO_EXTENSION);

				if ($_fn_ext != $ext)
					$fn	.= ".".$ext;
			}
			
			// temporary - each attach should go into owner folder and this will be decided on a top level and not here
			$owner_dir = rtrim(\QAutoload::GetTempWebPath(), "\//")."/mails_attach_".($owner ? $owner->getId() : "general")."/";
			if (!file_exists($owner_dir))
				qmkdir($owner_dir);

			$new_attach = $owner_dir.$fn;
			file_put_contents($new_attach, file_get_contents($attach, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]])));

			$attach = $new_attach;
			$_unlink_attachments[$attach] = $attach;
			$attachments[$k] = $attach;
		}

		$mail->addAttachments($attachments);
		
		$sent = $mail->_send();
		
		foreach ($_unlink_attachments ?: [] as $u)
			unlink($u);
		
		return $sent;
	}
	
	/**
	 * close the server connection
	 */
	function imapClose()
	{
		$this->inbox = array();
		$this->msg_cnt = 0;

		imap_close($this->imapConnection);
	}
	
	public function getImapServer()
	{
		return $this->imapServer ?: $this->smtp_host;
	}
	
	public function getImapUser()
	{
		return $this->imapUser ?: $this->smtp_username;
	}
	
	public function getImapPassword()
	{
		return $this->imapPass ?: $this->smtp_password;
	}
	
	/**
	 * open the server connection
	 */
	function imapConnect($custom_mailbox = null)
	{
		// {localhost:110/pop3}INBOX
		$ret = $this->imapConnection = imap_open((($custom_mailbox) ?: '{'.$this->getImapServer().':'.$this->imapPort.'/pop3/notls}'), $this->getImapUser(), $this->getImapPassword());
		return $ret;
	}
	
	/**
	 * move the message to a new folder
	 * 
	 * @param int $msg_index
	 * @param string $folder
	 */
	function moveEmail($msg_index, $folder = 'INBOX.Processed')
	{
		// move on server
		imap_mail_move($this->imapConnection, $msg_index, $folder);
		imap_expunge($this->imapConnection);

		// re-read the inbox
		$this->getInbox();
	}
	
	/**
	 * Mark as read
	 * 
	 * @param int $msg_index
	 * @param string $folder
	 */
	function markEmailAsRead($msg_index, $folder = 'INBOX.Processed', bool $imap_expunge = true)
	{
		// move on server
		imap_mail_move($this->imapConnection, $msg_index, $folder);
		if ($imap_expunge)
			imap_expunge($this->imapConnection);
	}

	/**
	 * get a specific message (1 = first email, 2 = second email, etc.)
	 * 
	 * @param int $msg_index
	 * @return mixed
	 */
	function getEmail($msg_index = null)
	{
		if (count($this->inbox) <= 0)
			return array();
		else if ( (!is_null($msg_index)) && isset($this->inbox[$msg_index]))
			return $this->inbox[$msg_index];

		return $this->inbox[0];
	}
	
	/**
	 * 
	 * @return int
	 */
	function getMessageCount()
	{
		return imap_num_msg($this->imapConnection);
	}
	
	/**
	 * read the inbox
	 */
	function getInbox($limit = null, $reverse = false, $filterEmails = null, int $offset = null)
	{
		global $charset, $htmlmsg, $plainmsg, $attachments;
		// imap_setflag_full($this->imapConnection, "5", "\\Seen");
		//qvardump($this->getmsg(2));die;
		
		$this->msg_cnt = imap_num_msg($this->imapConnection);

		$in = [];
		if (!$reverse)
		{
			$passed_mails = 0;
			for ($k = (($offset !== null) ? $offset : 1); $k <= $this->msg_cnt; $k++)
			{
				if (($message_data = $this->getInboxMailData($charset, $htmlmsg, $plainmsg, $attachments, $k, $header, $structure, $filterEmails)))
				{
					$in[] = $message_data;
					$passed_mails++;
				}

				if ($limit && ($passed_mails === $limit))
					break;
			}
		}
		else
		{
			$passed_mails = 0;
			for ($k = $this->msg_cnt; $k >= 1; $k--)
			{	
				if (($message_data = $this->getInboxMailData($charset, $htmlmsg, $plainmsg, $attachments, $k, $header, $structure, $filterEmails)))
				{
					$in[] = $message_data;
					$passed_mails++;
				}
				
				if ($limit && ($passed_mails === $limit))
					break;
			}
		}
		
		return $this->inbox = $in;
	}

	function getInboxMailData($charset, $htmlmsg, $plainmsg, $attachments, $k, $header, $structure, $filterEmails)
	{
		$charset = null;
		$htmlmsg = null; 
		$plainmsg = null; 
		$attachments = null;
		$extracted_msg = $this->getmsg($k);
			
		$header = imap_headerinfo($this->imapConnection, $k);
		$structure = imap_fetchstructure($this->imapConnection, $k);
		$attachments = [];
		$body = null;

		if ($filterEmails && !call_user_func_array($filterEmails, [$header, $structure]))
			return null;

		if ($structure->parts)
		{
			for ($i = 0; $i < count($structure->parts); $i++)
			{
				// set up an empty attachment
				$attachments[$i] = array(
					'is_attachment' => FALSE,
					'is_body'		=> false,
					'filename'      => '',
					'name'          => '',
					'attachment'    => ''
				);

				// if this attachment has idfparameters, then proceed
				if ($structure->parts[$i]->ifdparameters) {
					foreach ($structure->parts[$i]->dparameters as $object) {
						// if this attachment is a file, mark the attachment and filename
						if (strtolower($object->attribute) == 'filename') {
							$attachments[$i]['is_attachment'] = TRUE;
							$attachments[$i]['filename']      = $object->value;
						}
					}
				}

				// if this attachment has ifparameters, then proceed as above
				if ($structure->parts[$i]->ifparameters) {
					foreach ($structure->parts[$i]->parameters as $object) {

						if (strtolower($object->attribute) == 'name') {
							$attachments[$i]['is_attachment'] = TRUE;
							$attachments[$i]['name']          = $object->value;
						}
						/*else if (strtolower($object->attribute) == 'boundary')
						{
							$attachments[$i]['is_attachment'] = false;
							$attachments[$i]['name']          = $object->value;
							die("uuuf");
						}*/
					}
				}

				if (!$attachments[$i]['is_attachment'])
				{
					$attachments[$i]['is_body'] = true;
				}

				// if we found a valid attachment for this 'part' of the email, process the attachment
				if ($attachments[$i]['is_attachment'] || $attachments[$i]['is_body']) {
					// get the content of the attachment
					$attachments[$i]['attachment'] = imap_fetchbody($this->imapConnection, $k, $i + 1);

					// $attachments[$i]['attachment'] = str_replace("\r\n", "", $attachments[$i]['attachment']);
					// check if this is base64 encoding
					if ($structure->parts[$i]->encoding == 3 || $structure->encoding == 3) { // 3 = BASE64
						$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
					}
					// otherwise, check if this is "quoted-printable" format
					elseif ($structure->parts[$i]->encoding == 4 || $structure->encoding == 4) { // 4 = QUOTED-PRINTABLE
						$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
					}
				}
			}
		}
		else
		{
			$body = imap_body($this->imapConnection, $k);
		}

		return [	
			'index'     => $k,
			'header'    => $header,
			'structure' => $structure,
			'body'      => $body,
			'attachments' => $attachments,
			'extracted_body' => $extracted_msg
		];
	}
	
	function getmsg($mid)
	{
		// input $mbox = IMAP stream, $mid = message id
		// output all the following:
		global $charset, $htmlmsg, $plainmsg, $attachments;
		
		$mbox = $this->imapConnection;
		$htmlmsg = $plainmsg = $charset = '';
		$attachments = array();

		// HEADER
		$h = imap_headerinfo($mbox, $mid);
		// add code here to get date, from, to, cc, subject...

		// BODY
		$s = imap_fetchstructure($mbox, $mid);
		if (!$s->parts)  // simple
			$this->getpart($mid,$s,0);  // pass 0 as part-number
		else {  // multipart: cycle through each part
			foreach ($s->parts as $partno0=>$p)
				$this->getpart($mid,$p,$partno0+1);
		}
		
		return [$plainmsg, $htmlmsg, $attachments, $charset];
	}

	function getpart($mid, $p, $partno)
	{
		// $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple
		global $htmlmsg, $plainmsg, $charset, $attachments;
		
		$mbox = $this->imapConnection;

		// DECODE DATA
		$data = ($partno) ?
			imap_fetchbody($mbox,$mid,$partno):  // multipart
			imap_body($mbox,$mid);  // simple
		// Any part may be encoded, even plain text messages, so check everything.
		if ($p->encoding==4)
			$data = quoted_printable_decode($data);
		elseif ($p->encoding==3)
			$data = base64_decode($data);

		// PARAMETERS
		// get all parameters, like charset, filenames of attachments, etc.
		$params = array();
		if ($p->parameters)
			foreach ($p->parameters as $x)
				$params[strtolower($x->attribute)] = $x->value;
		if ($p->dparameters)
			foreach ($p->dparameters as $x)
				$params[strtolower($x->attribute)] = $x->value;

		// ATTACHMENT
		// Any part with a filename is an attachment,
		// so an attached text file (type 0) is not mistaken as the message.
		if ($params['filename'] || $params['name']) {
			// filename may be given as 'Filename' or 'Name' or both
			$filename = ($params['filename'])? $params['filename'] : $params['name'];
			// filename may be encoded, so see imap_mime_header_decode()
			$attachments[$filename] = $data;  // this is a problem if two files have same name
		}

		// TEXT
		if (($p->type==0) && $data) {
			// Messages may be split in different parts because of inline attachments,
			// so append parts together with blank row.
			if (strtolower($p->subtype) == 'plain')
				$plainmsg .= trim($data) ."\n\n";
			else
				$htmlmsg .= $data ."<br><br>";
			$charset = $params['charset'];  // assume all parts are same charset
		}

		// EMBEDDED MESSAGE
		// Many bounce notifications embed the original message as type 2,
		// but AOL uses type 1 (multipart), which is not handled here.
		// There are no PHP functions to parse embedded messages,
		// so this just appends the raw source to the main message.
		elseif ($p->type==2 && $data) {
			$plainmsg .= $data."\n\n";
		}

		// SUBPART RECURSION
		if ($p->parts)
		{
			foreach ($p->parts as $partno0=>$p2)
				$this->getpart($mid,$p2,$partno.'.'.($partno0 + 1));  // 1.2, 1.2.1, etc.
		}
	}
}