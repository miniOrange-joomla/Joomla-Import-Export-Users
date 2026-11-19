<?php
/**
 * @package     Joomla.Component
 * @subpackage  com_miniorange_importexportusers
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Version;

class MoImportExportCustomer
{
	/**
	 * @var string
	 */
	public $email;

	/**
	 * @var string
	 */
	public $phone;

	/**
	 * @var string
	 */
	public $customerKey;

	/**
	 * @var string
	 */
	public $transactionId;

	/*
	Initial values are hardcoded to support the miniOrange framework to generate OTP for email.
	We need the default value for creating the OTP the first time,
	as we don't have the default keys available before registering the user to our server.
	These default values are only required for sending a one time passcode at the user provided email address.
	*/

	/**
	 * @var string
	 */
	private $defaultCustomerKey = "16555";

	/**
	 * @var string
	 */
	private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

	/**
	 * Contact-Us: UserSync-style email payload via /moas/api/notify/send
	 * Keeps timezone as its own line in the email content (not embedded in the query text).
	 */
	public function submitContactUs($qEmail, $qPhone, $query, $timezoneInfo = '')
	{
		if (!MoImportExportUtility::isCurlInstalled())
		{
			return json_encode(array("status" => 'CURL_ERROR', 'statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}

		$url = 'https://login.xecurify.com/moas/api/notify/send';
		$fromEmail = $qEmail;
		$phpVersion = phpversion();
		$jVersion = new Version;
		$jCmsVersion = $jVersion->getShortVersion();
		$moPluginVersion = MoImportExportUtility::getPluginVersion();
		$subject = "Query for Joomla Import Export Free - " . $fromEmail;

		$timezoneSafe = htmlspecialchars(trim((string) $timezoneInfo));
		$queryWithMeta = '[miniOrange Joomla Import Export Users Free | ' . $phpVersion . ' | ' . $jCmsVersion . ' | ' . $moPluginVersion . '] ' . $query;

		$content = '<div >Hello, <br><br>
                    <strong>Company</strong> :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>
                    <strong>Phone Number</strong> :' . $qPhone . '<br><br>
                    <strong>Timezone</strong> :' . $timezoneSafe . '<br><br>
                    <b>Email :<a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a></b><br><br>
                    <b>Query</b>: ' . $queryWithMeta . '</b></div>';

		$fields = array(
			'customerKey' => $this->defaultCustomerKey,
			'sendEmail' => true,
			'email' => array(
				'customerKey' => $this->defaultCustomerKey,
				'fromEmail' => $fromEmail,
				'fromName' => 'miniOrange',
				'toEmail' => 'joomlasupport@xecurify.com',
				'toName' => 'joomlasupport@xecurify.com',
				'subject' => $subject,
				'content' => $content,
			),
		);
		$fieldString = json_encode($fields);

		return self::curlCall($url, $fieldString);
	}

	public static function submitFeedbackForm($email, $phone, $query, $cause, $timezone = '')
	{
		$url = 'https://login.xecurify.com/moas/api/notify/send';

		$customerKey = "16555";
		$apiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
		$fromEmail = $email;
		$phpVersion = phpversion();
		$jCmsVersion = MoImportExportUtility::getJoomlaCmsVersion();
		$moPluginVersion = MoImportExportUtility::getPluginVersion();
		$osVersion    = MoImportExportUtility::getOsInfo();
		$pluginName    = 'Import Export Users Free Plugin';

		$query1 = '[' . $pluginName . ' | ' . $moPluginVersion . ' | PHP ' . $phpVersion . ' | OS ' . $osVersion . '] ';

		$ccEmail = 'joomlasupport@xecurify.com';
		$bccEmail = 'hubspot.mailbox@xecurify.com';
		$timezone = trim((string) $timezone);
		$timezoneLine = $timezone !== '' ? ('<strong>Timezone: </strong>' . htmlspecialchars($timezone, ENT_QUOTES, 'UTF-8') . '<br><br>') : '';
		$content = '<div>Hello, <br><br>'
				. '<strong>Company: </strong><a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank">' . $_SERVER['SERVER_NAME'] . '</a><br><br>'
				. '<strong>Phone Number: </strong>' . $phone . '<br><br>'
				. $timezoneLine
				. '<strong>Feedback: </strong>' . $query . '<br><br>'
				. '<strong>Additional Details: </strong>' . $cause . '<br><br>'
				. '<strong>System Information: </strong>' . $query1
				. '</div>';

		$subject = "Joomla Import Export Free Feedback";

		$fields = array(
			'customerKey' => $customerKey,
			'sendEmail' => true,
			'email' => array(
				'customerKey' 	=> $customerKey,
				'fromEmail' 	=> $fromEmail,
				'bccEmail' 		=> $bccEmail,
				'fromName' 		=> 'miniOrange',
				'toEmail' 		=> $ccEmail,
				'toName' 		=> $bccEmail,
				'subject' 		=> $subject,
				'content' 		=> $content
			),
		);
		$fieldString = json_encode($fields);

		return self::curlCall($url, $fieldString);
	}

	public static function curlCall($url,$fieldString)
	{
		$ch = curl_init($url);
		$customerDetails = (new MoImportExportUtility)->loadDatabaseValues('#__miniorange_importexport_customer_details');
		$customerKey = !empty($customerDetails['customer_key']) ? $customerDetails['customer_key'] : '16555';
		$apiKey = !empty($customerDetails['api_key']) ? $customerDetails['api_key'] : 'fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq';

		// Current time in milliseconds since midnight, January 1, 1970 UTC.

		$currentTimeInMillis = round(microtime(true) * 1000);

		// Creating the Hash using SHA-512 algorithm

		$stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
		$hashValue = hash("sha512", $stringToHash);

		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " . number_format($currentTimeInMillis, 0, '', '');
		$authorizationHeader = "Authorization: " . $hashValue;
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);

		// Required for https urls
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
			$timestampHeader, $authorizationHeader)
		);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldString);
		$content = curl_exec($ch);

		if (curl_errno($ch))
		{
			$error = 'Request Error: ' . curl_error($ch);

			return $error;
		}

		curl_close($ch);

		return $content;
	}
}
