<?php

namespace App\Controller;

class EmailController {

	/**
	 * Odešle email o obnovì hesla
	 *
	 * @param $emailFrom
	 * @param $emailTo
	 * @param $subject
	 * @param $body
	 * @throws \Exception
	 * @throws \phpmailerException
	 */
	public static function SendPlainEmail($emailFrom, $emailTo, $subject, $body) {
		$email = new \PHPMailer();
		$email->CharSet = "UTF-8";
		$email->From = $emailFrom;
		//$email->FromName = $
		$email->Subject = $subject;
		$email->Body = $body;
		$email->AddAddress($emailTo);
		$email->Send();
	}

}