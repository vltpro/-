<?php 
	header("access-control-allow-origin: *");
	header('Content-Type: application/json; charset=utf-8');
	error_reporting(0);
	$mail = $_GET['mail'];
    $pass = $_GET['pass'];
	$response = array();
	$hostname = "{imap-mail.outlook.com:993/ssl}Inbox";
	$email = $mail;
	$password = $pass;
	
	if (preg_match("/hotmail/i", $email) or preg_match("/outlook/i", $email)) {
		$hostname = "{imap-mail.outlook.com:993/ssl}Inbox";
	}
	elseif (preg_match("/yandex/i", $email)) {
		$hostname = "{imap.yandex.ru:993/imap/ssl}INBOX";
	}
	$inbox = imap_open($hostname,$email,$password);
	if (imap_last_error()) {
		$response['error_message'] = imap_last_error();
		$response['error'] = 10;
		$response['message'] = "Mail Error: ".imap_last_error();
		echo json_encode($response);exit();
	}
	$mailInfo = imap_check($inbox);
	$mailboxes = imap_list($inbox, $hostname, '*');
	$response['mailboxes'] = $mailboxes;
	$response['mailInfo'] = $mailInfo;
	$numberFetch = ($mailInfo->Nmsgs <= 3) ? $mailInfo->Nmsgs : 3;
	for ($i=$mailInfo->Nmsgs; $i > ($mailInfo->Nmsgs-$numberFetch); $i--) { 

		if (preg_match("/hotmail/i", $email) or preg_match("/outlook/i", $email)) {
			$overview = imap_fetch_overview($inbox,$i,0);
			$message = imap_fetchbody($inbox,$i,2.0);

			if ($message == null) {
				$title = $overview[0]->subject;
				$sendto = $overview[0]->to;
				$sendDate = $overview[0]->date;
				$sendUDate = $overview[0]->udate;
				$message = imap_fetchbody($inbox,$i,1);
			}
			else
			{
				$title = imap_utf8($overview[0]->subject);				
				$sendto = imap_utf8($overview[0]->to);				
				$sendDate = imap_utf8($overview[0]->date);				
				$sendUDate = $overview[0]->udate;				
				$message = imap_qprint($message);
			}
		}
		if (preg_match("/yandex/i", $email)) {
			$overview = imap_fetch_overview($inbox,$i,0);
			$message = imap_fetchbody($inbox,$i,2.0);

			if ($message == null) {
				$title = $overview[0]->subject;
				$sendto = $overview[0]->to;
				$sendDate = $overview[0]->date;
				$sendUDate = $overview[0]->udate;
				$message = imap_fetchbody($inbox,$i,1);
			}
			else
			{
				$title = imap_utf8($overview[0]->subject);				
				$sendto = imap_utf8($overview[0]->to);				
				$sendDate = imap_utf8($overview[0]->date);				
				$sendUDate = imap_utf8($overview[0]->udate);				
				$message = imap_qprint($message);
			}
		}
		
		$response['emails'][] = array("id"=>$i,"title"=>$title,"body"=>$message,"sendUDate"=>$sendUDate);
		preg_match('/margin-right:20px;\">(.*?)<\/span>/i',$message,$code);
		if (isset($code[1])) {
			$response['code'] = $code[1];
			$i = 0;
		}
		else
		{
			$response['error'] = 1;
			$response['message'] = "Không tìm thấy Email";
		}
	}
	imap_close($inbox);
	
	echo json_encode($response, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); 
?>
