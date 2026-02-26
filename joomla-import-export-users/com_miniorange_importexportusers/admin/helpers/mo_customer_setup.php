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
use Joomla\CMS\Factory;
use Joomla\CMS\Version;

class MoImportExportCustomer
{

    public $email;
    public $phone;
    public $customerKey;
    public $transactionId;

    /*
    ** Initial values are hardcoded to support the miniOrange framework to generate OTP for email.
    ** We need the default value for creating the OTP the first time,
    ** As we don't have the Default keys available before registering the user to our server.
    ** This default values are only required for sending an One Time Passcode at the user provided email address.
    */

    //auth
    private $defaultCustomerKey = "16555";
    private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

    /**
     * Contact-Us: UserSync-style email payload via /moas/api/notify/send
     * Keeps timezone as its own line in the email content (not embedded in the query text).
     */
    function submit_contact_us($q_email, $q_phone, $query, $timezoneInfo = '')
    {
        if (!MoImportExportUtility::is_curl_installed()) {
            return json_encode(array("status" => 'CURL_ERROR', 'statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }

        $url = 'https://login.xecurify.com/moas/api/notify/send';
        $fromEmail = $q_email;
        $phpVersion = phpversion();
        $jVersion = new Version;
        $jCmsVersion = $jVersion->getShortVersion();
        $moPluginVersion = MoImportExportUtility::GetPluginVersion();
        $subject = "Query for miniOrange Joomla Import Export Users Free - " . $fromEmail;

        $currentUser = Factory::getUser();
        $adminEmail = $currentUser->email;

        $timezoneSafe = htmlspecialchars(trim((string) $timezoneInfo));
        $queryWithMeta = '[miniOrange Joomla Import Export Users Free | ' . $phpVersion . ' | ' . $jCmsVersion . ' | ' . $moPluginVersion . '] ' . $query;

        $content = '<div >Hello, <br><br>
                    <strong>Company</strong> :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>
                    <strong>Phone Number</strong> :' . $q_phone . '<br><br>
                    <strong>Timezone</strong> :' . $timezoneSafe . '<br><br>
                    <strong>Admin Email : </strong><a href="mailto:' . $adminEmail . '" target="_blank">' . $adminEmail . '</a><br><br>
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
        $field_string = json_encode($fields);

        return self::curl_call($url, $field_string);
    }

    public static function submit_feedback_form($email, $phone, $query, $cause, $timezone = '')
    {
        $url = 'https://login.xecurify.com/moas/api/notify/send';

        $customerKey = "16555";
        $apiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
        $fromEmail = $email;
        $phpVersion = phpversion();
        $dVar=new JConfig();
        $check_email = $dVar->mailfrom;
        $jCmsVersion =  MoImportExportUtility::getJoomlaCmsVersion();
        $moPluginVersion =  MoImportExportUtility::GetPluginVersion();
        $os_version    = MoImportExportUtility::_get_os_info();
        $pluginName    = 'Import Export Users Free Plugin';
        $admin_email   = !empty($email)?$email:$check_email;
        
        $query1 = '['.$pluginName.' | '.$moPluginVersion.' | PHP ' . $phpVersion.' | OS ' . $os_version.'] ';
        
        $ccEmail = 'joomlasupport@xecurify.com';
        $bccEmail = 'joomlasupport@xecurify.com';
        $timezone = trim((string) $timezone);
        $timezoneLine = $timezone !== '' ? ('<strong>Timezone: </strong>' . htmlspecialchars($timezone, ENT_QUOTES, 'UTF-8') . '<br><br>') : '';
        $content = '<div>Hello, <br><br>'
                . '<strong>Company: </strong><a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank">' . $_SERVER['SERVER_NAME'] . '</a><br><br>'
                . '<strong>Phone Number: </strong>' . $phone . '<br><br>'
                . $timezoneLine
                . '<strong>Admin Email: </strong><a href="mailto:' .$admin_email . '" target="_blank">' . $admin_email . '</a><br><br>'
                . '<strong>Feedback: </strong>' . $query . '<br><br>'
                . '<strong>Additional Details: </strong>' . $cause . '<br><br>'
                . '<strong>System Information: </strong>' . $query1 
                . '</div>';
        
        $subject = "Feedback for miniOrange Joomla Import Export Users Free";

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
        $field_string = json_encode($fields);

        return self::curl_call($url,$field_string);
    }

    public static function curl_call($url,$field_string)
    {
        $ch = curl_init($url);
        $customer_details = (new MoImportExportUtility)->load_database_values('#__miniorange_importexport_customer_details');
        $customerKey = !empty($customer_details['customer_key'])?$customer_details['customer_key']:'16555';
        $apiKey = !empty($customer_details['api_key'])?$customer_details['api_key']:'fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq';
        /* Current time in milliseconds since midnight, January 1, 1970 UTC. */
        $currentTimeInMillis = round(microtime(true) * 1000);
     
        /* Creating the Hash using SHA-512 algorithm */
        $stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue = hash("sha512", $stringToHash);

        $customerKeyHeader = "Customer-Key: " . $customerKey;
        $timestampHeader = "Timestamp: " . number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader = "Authorization: " . $hashValue;
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls

        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
            $timestampHeader, $authorizationHeader));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        $content = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = 'Request Error: ' . curl_error($ch);
            return $error;
        }
        curl_close($ch);
        
        return $content;
    }
} ?>
