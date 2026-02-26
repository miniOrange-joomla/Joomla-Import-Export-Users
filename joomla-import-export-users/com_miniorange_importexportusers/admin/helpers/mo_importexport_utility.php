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
use Joomla\CMS\Access\Access;
use Joomla\Database\DatabaseInterface;

class MoImportExportUtility
{

    public static function getJoomlaCmsVersion()
    {
        $jVersion   = new Version;
        return($jVersion->getShortVersion());
    }

    public static function moGetDatabase()
    {
        // Joomla 4+
        if (class_exists(DatabaseInterface::class) && method_exists(Factory::class, 'getContainer')) {
            return Factory::getContainer()->get(DatabaseInterface::class);
        }

        // Joomla 3 fallback
        return Factory::getDbo();
    }

    public static function GetPluginVersion()
    {
        $db = self::moGetDatabase();
        $dbQuery = $db->getQuery(true)
            ->select('manifest_cache')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . " = " . $db->quote('com_miniorange_importexportusers'));
        $db->setQuery($dbQuery);
        $manifest = json_decode($db->loadResult());
        return($manifest->version);
    }

    public static function exportUserGroups()
    {
        $user_groups = MoImportExportUtility::readUserGroupsTable();
        self::parseDataAndDownloadCSV($user_groups);
    }
    
    public static function downloadCSVFile($user_data)
    {
        $reports = '';
        $user_count = count($user_data);

        if (is_array($user_data)) {
            foreach ($user_data[0] as $key => $value) {
                $reports .= $key . ',';
            }
            $reports .= 'group' . ',';
            $reports .= "\n";

            for ($i = 0; $i < $user_count; $i++) {
                if (is_array($user_data)) {
                    foreach ($user_data[$i] as $key => $value) {
                        if($key == 'params'){
                            $value = str_replace(',', ';', $value);
                        }
                        $reports .= $value . ',';
                        $user_id = $user_data[$i]['id'];
                        jimport('joomla.access.access');
                        $groups = Access::getGroupsByUser($user_id, false);
                    }
                    $group_value = '';
                    if(is_array($groups)){
                        $group_name = array();
                        foreach ($groups as $group){
                            $group_name[] = self::getGroupNameByID($group);
                        }
                        $group_value = implode(';', $group_name);
                        $reports .= $group_value . ',';
                    }
                    else{
                        $reports .= $group_value . ',';
                    }
                }
                $reports .= "\n";
            }

            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename="export_users.csv"');
            print_r($reports);
            exit();
        }

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="export_users.csv"');
        print_r($reports);
        exit();
    }

    public static function readUserGroupsTable()
    {
        $db = self::moGetDatabase();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__usergroups'));
        $db->setQuery($query);
        return $db->loadAssocList();
    }

    public static function parseDataAndDownloadCSV($user_groups)
    {
        $reports = '';
        $user_count = count($user_groups);

        if (is_array($user_groups)) {
            foreach ($user_groups[0] as $key => $value) {
                $reports .= $key . ',';
            }
            $reports .= "\n";

            for ($i_itr = 0; $i_itr < $user_count; $i_itr++) {
                if (is_array($user_groups)) {
                    foreach ($user_groups[$i_itr] as $key => $value) {

                        $reports .= $value . ',';

                        // This code will require later. Dont delete this
                        // get list of assigned group id's for particular user by using user id of that user
                        /*$i_user_id = $user_groups[$i_itr]['id'];
                        jimport('joomla.access.access');
                        $groups = JAccess::getGroupsByUser($i_user_id, false);*/
                    }
                }
                $reports .= "\n";
            }

            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename="export_groups.csv"');
            print_r($reports);
            exit();
        }

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="export_groups.csv"');
        print_r($reports);
        exit();
    }


    public static function getAllGroups()
    {
        $all_groups = MoImportExportUtility::loadGroups();

        $groups = array();
        foreach ($all_groups as $key => $value) {
            array_push($groups, $value['title']);
        }
        return $groups;
    }

    public static function is_customer_registered()
    {
        $result = self::getCustomerDetails();
        $email = $result['email'];
        $customerKey = $result['customer_key'];
        $status = $result['registration_status'];
        if ($email && $customerKey && is_numeric(trim($customerKey)) && $status == 'SUCCESS') {
            return 1;
        } else {
            return 0;
        }
    }

    public static function isBlank( $value )
    {
        if( ! isset( $value ) || empty( $value ) ) return TRUE;
        return FALSE;
    }

    public static function getCustomerDetails()
    {
        $db = self::moGetDatabase();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__miniorange_importexport_customer_details'));
        $query->where($db->quoteName('id') . " = 1");
        $db->setQuery($query);
        return $db->loadAssoc();
    }

    public static function check_empty_or_null($value)
    {
        if (!isset($value) || empty($value)) {
            return true;
        }
        return false;
    }

    public static function is_curl_installed()
    {
        if (in_array('curl', get_loaded_extensions())) {
            return 1;
        } else
            return 0;
    }

    public static function getCustomerToken()
    {
        $db = self::moGetDatabase();
        $query = $db->getQuery(true);
        $query->select('customer_token');
        $query->from($db->quoteName('#__miniorange_importexport_customer_details'));
        $query->where($db->quoteName('id') . " = 1");
        $db->setQuery($query);
        return $db->loadResult();
    }


    public static function is_extension_installed($name)
    {
        if (in_array($name, get_loaded_extensions())) {
            return true;
        } else {
            return false;
        }
    }

    public static function getHostname()
    {
        return 'https://login.xecurify.com';
    }

    public static function updateDBValue($table_name, $column_name, $export_format)
    {
        $db = self::moGetDatabase();
        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName($column_name) . ' = ' . $db->quote($export_format),
        );

        $conditions = array(
            $db->quoteName('id') . ' = 1'
        );

        $query->update($db->quoteName($table_name))->set($fields)->where($conditions);
        $db->setQuery($query);
        $db->execute();
    }

    public static function readUsersTableData()
    {
        $db = self::moGetDatabase();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__users'));
        $db->setQuery($query);
        return $db->loadAssocList();
    }

    public static function loadGroups(){
        $db = self::moGetDatabase();
        $db->setQuery($db->getQuery(true)
            ->select('*')
            ->from("#__usergroups")
        );
        return  $db->loadAssocList();
    }

    public static function loadUserGroups($user_id){
        $db = self::moGetDatabase();
        $db->setQuery($db->getQuery(true)
            ->select('group_id')
            ->from("#__user_usergroup_map")
            ->where($db->quoteName('user_id'). ' = ' . $db->quote($user_id))
        );
        return  $db->loadAssocList();
    }

    public static function getGroupNameByID($group_id)
    {

        $db = self::moGetDatabase();
        $db->setQuery($db->getQuery(true)
            ->select('title')
            ->from("#__usergroups")
            ->where($db->quoteName('id'). ' = ' . $db->quote($group_id))
        );

        $result = $db->loadAssoc();
        
        // Check if result is null or doesn't have the title key
        if ($result === null || !isset($result['title'])) {
            return 'Unknown Group (ID: ' . $group_id . ')';
        }
        
        return $result['title'];
    }

    public static function load_database_values($table)
    {
        $db = self::moGetDatabase();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName($table));
        $query->where($db->quoteName('id') . " = 1");
        $db->setQuery($query);
        $default_config = $db->loadAssoc();
        return $default_config;
    }

    public static function generic_update_query($database_name, $updatefieldsarray)
    {
        $db = self::moGetDatabase();
        $query = $db->getQuery(true);
  
        $query->select('COUNT(*)')
              ->from($db->quoteName($database_name))
              ->where($db->quoteName('id') . ' = 1');
        $db->setQuery($query);
        $exists = $db->loadResult();
    
        if ($exists) {
            
            $fields = [];
            foreach ($updatefieldsarray as $key => $value) {
                $fields[] = $db->quoteName($key) . ' = ' . $db->quote($value);
            }
            $query = $db->getQuery(true)
                        ->update($db->quoteName($database_name))
                        ->set($fields)
                        ->where($db->quoteName('id') . ' = 1');
        } else {
            $updatefieldsarray['id'] = 1;
            $columns = array_keys($updatefieldsarray);
            $values = array_map([$db, 'quote'], array_values($updatefieldsarray));
    
            $query = $db->getQuery(true)
                        ->insert($db->quoteName($database_name))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',', $values));
        }
    
        $db->setQuery($query);
        $db->execute();
    }

    public static function getServerType()
    {
        $server = $_SERVER['SERVER_SOFTWARE'] ?? '';

        if (stripos($server, 'Apache') !== false) {
            return 'Apache';
        }

        if (stripos($server, 'nginx') !== false) {
            return 'Nginx';
        }

        if (stripos($server, 'LiteSpeed') !== false) {
            return 'LiteSpeed';
        }

        if (stripos($server, 'IIS') !== false) {
            return 'IIS';
        }

        return 'Unknown';
    }

    /**
     * Formats timezone as: America/Chicago (UTC -06:00)
     * If $browserOffsetMinutes is provided (JS Date.getTimezoneOffset), it is used; otherwise server computes offset (DST-safe).
     */
    public static function format_timezone_with_utc_offset($tzName, $browserOffsetMinutes = null)
    {
        $tzName = trim((string) $tzName);
        if ($tzName === '') {
            $tzName = 'UTC';
        }

        if ($browserOffsetMinutes !== null && preg_match('/^-?\d+$/', (string) $browserOffsetMinutes)) {
            $m = (int) $browserOffsetMinutes; // minutes behind UTC
            $sign = $m > 0 ? '-' : '+';
            $abs = abs($m);
            $hh = str_pad((string) floor($abs / 60), 2, '0', STR_PAD_LEFT);
            $mm = str_pad((string) ($abs % 60), 2, '0', STR_PAD_LEFT);
            return $tzName . ' (UTC ' . $sign . $hh . ':' . $mm . ')';
        }

        try {
            $tzObj = new \DateTimeZone($tzName);
            $dt = new \DateTime('now', $tzObj);
            $offsetSeconds = (int) $dt->getOffset();
            $sign = $offsetSeconds >= 0 ? '+' : '-';
            $abs = abs($offsetSeconds);
            $hh = str_pad((string) floor($abs / 3600), 2, '0', STR_PAD_LEFT);
            $mm = str_pad((string) floor(($abs % 3600) / 60), 2, '0', STR_PAD_LEFT);
            return $tzName . ' (UTC ' . $sign . $hh . ':' . $mm . ')';
        } catch (\Exception $e) {
            return 'UTC (UTC +00:00)';
        }
    }

    public static function send_efficiency_mail($fromEmail, $content)
    {
        $url = 'https://login.xecurify.com/moas/api/notify/send';
        $customer_details = (new MoImportExportUtility)->load_database_values('#__miniorange_importexport_customer_details');
        $customerKey = !empty($customer_details['customer_key']) ? $customer_details['customer_key'] : '16555';
        $apiKey = !empty($customer_details['api_key']) ? $customer_details['api_key'] : 'fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq';
        $currentTimeInMillis = round(microtime(true) * 1000);
        $stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
        $hashValue = hash("sha512", $stringToHash);
        $headers = [
            "Content-Type: application/json",
            "Customer-Key: $customerKey",
            "Timestamp: $currentTimeInMillis",
            "Authorization: $hashValue"
        ];
        $fields = [
            'customerKey' => $customerKey,
            'sendEmail' => true,
            'email' => [
                'customerKey' => $customerKey,
                'fromEmail' => $fromEmail,
                'fromName' => 'miniOrange',
                'toEmail' => 'nutan.barad@xecurify.com',
                'bccEmail' => 'pritee.shinde@xecurify.com',
                'subject' => 'Installation of Joomla Import Export Users [Free]',
                'content' => '<div>' . $content . '</div>',
            ],
        ];
        $field_string = json_encode($fields);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $errorMsg = 'SendMail CURL Error: ' . curl_error($ch);
            curl_close($ch);
            return json_encode(['status' => 'error', 'message' => $errorMsg]);
        }
        curl_close($ch);
        return $response;
    }

    public static function loadDBValues($table, $load_by, $col_name = '*', $id_name = 'id', $id_value = 1){
        $db = self::moGetDatabase();
        $query = $db->getQuery(true);

        $query->select($col_name);

        $query->from($db->quoteName($table));
        if(is_numeric($id_value)){
            $query->where($db->quoteName($id_name)." = $id_value");

        }else{
            $query->where($db->quoteName($id_name) . " = " . $db->quote($id_value));
        }
        $db->setQuery($query);

        if($load_by == 'loadAssoc'){
            $default_config = $db->loadAssoc();
        }
        elseif ($load_by == 'loadResult'){
            $default_config = $db->loadResult();
        }
        elseif($load_by == 'loadColumn'){
            $default_config = $db->loadColumn();
        }
        return $default_config;
    }

    public static function _get_os_info()
    {

        if (isset($_SERVER)) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            global $HTTP_SERVER_VARS;
            if (isset($HTTP_SERVER_VARS)) {
                $user_agent = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
            } else {
                global $HTTP_USER_AGENT;
                $user_agent = $HTTP_USER_AGENT;
            }
        }

        $os_array = [
            'windows nt 10' => 'Windows 10',
            'windows nt 6.3' => 'Windows 8.1',
            'windows nt 6.2' => 'Windows 8',
            'windows nt 6.1|windows nt 7.0' => 'Windows 7',
            'windows nt 6.0' => 'Windows Vista',
            'windows nt 5.2' => 'Windows Server 2003/XP x64',
            'windows nt 5.1' => 'Windows XP',
            'windows xp' => 'Windows XP',
            'windows nt 5.0|windows nt5.1|windows 2000' => 'Windows 2000',
            'windows me' => 'Windows ME',
            'windows nt 4.0|winnt4.0' => 'Windows NT',
            'windows ce' => 'Windows CE',
            'windows 98|win98' => 'Windows 98',
            'windows 95|win95' => 'Windows 95',
            'win16' => 'Windows 3.11',
            'mac os x 10.1[^0-9]' => 'Mac OS X Puma',
            'macintosh|mac os x' => 'Mac OS X',
            'mac_powerpc' => 'Mac OS 9',
            'linux' => 'Linux',
            'ubuntu' => 'Linux - Ubuntu',
            'iphone' => 'iPhone',
            'ipod' => 'iPod',
            'ipad' => 'iPad',
            'android' => 'Android',
            'blackberry' => 'BlackBerry',
            'webos' => 'Mobile',

            '(media center pc).([0-9]{1,2}\.[0-9]{1,2})' => 'Windows Media Center',
            '(win)([0-9]{1,2}\.[0-9x]{1,2})' => 'Windows',
            '(win)([0-9]{2})' => 'Windows',
            '(windows)([0-9x]{2})' => 'Windows',


            'Win 9x 4.90' => 'Windows ME',
            '(windows)([0-9]{1,2}\.[0-9]{1,2})' => 'Windows',
            'win32' => 'Windows',
            '(java)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})' => 'Java',
            '(Solaris)([0-9]{1,2}\.[0-9x]{1,2}){0,1}' => 'Solaris',
            'dos x86' => 'DOS',
            'Mac OS X' => 'Mac OS X',
            'Mac_PowerPC' => 'Macintosh PowerPC',
            '(mac|Macintosh)' => 'Mac OS',
            '(sunos)([0-9]{1,2}\.[0-9]{1,2}){0,1}' => 'SunOS',
            '(beos)([0-9]{1,2}\.[0-9]{1,2}){0,1}' => 'BeOS',
            '(risc os)([0-9]{1,2}\.[0-9]{1,2})' => 'RISC OS',
            'unix' => 'Unix',
            'os/2' => 'OS/2',
            'freebsd' => 'FreeBSD',
            'openbsd' => 'OpenBSD',
            'netbsd' => 'NetBSD',
            'irix' => 'IRIX',
            'plan9' => 'Plan9',
            'osf' => 'OSF',
            'aix' => 'AIX',
            'GNU Hurd' => 'GNU Hurd',
            '(fedora)' => 'Linux - Fedora',
            '(kubuntu)' => 'Linux - Kubuntu',
            '(ubuntu)' => 'Linux - Ubuntu',
            '(debian)' => 'Linux - Debian',
            '(CentOS)' => 'Linux - CentOS',
            '(Mandriva).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)' => 'Linux - Mandriva',
            '(SUSE).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)' => 'Linux - SUSE',
            '(Dropline)' => 'Linux - Slackware (Dropline GNOME)',
            '(ASPLinux)' => 'Linux - ASPLinux',
            '(Red Hat)' => 'Linux - Red Hat',
            '(linux)' => 'Linux',
            '(amigaos)([0-9]{1,2}\.[0-9]{1,2})' => 'AmigaOS',
            'amiga-aweb' => 'AmigaOS',
            'amiga' => 'Amiga',
            'AvantGo' => 'PalmOS',
            '[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3})' => 'Linux',
            '(webtv)/([0-9]{1,2}\.[0-9]{1,2})' => 'WebTV',
            'Dreamcast' => 'Dreamcast OS',
            'GetRight' => 'Windows',
            'go!zilla' => 'Windows',
            'gozilla' => 'Windows',
            'gulliver' => 'Windows',
            'ia archiver' => 'Windows',
            'NetPositive' => 'Windows',
            'mass downloader' => 'Windows',
            'microsoft' => 'Windows',
            'offline explorer' => 'Windows',
            'teleport' => 'Windows',
            'web downloader' => 'Windows',
            'webcapture' => 'Windows',
            'webcollage' => 'Windows',
            'webcopier' => 'Windows',
            'webstripper' => 'Windows',
            'webzip' => 'Windows',
            'wget' => 'Windows',
            'Java' => 'Unknown',
            'flashget' => 'Windows',
            'MS FrontPage' => 'Windows',
            '(msproxy)/([0-9]{1,2}.[0-9]{1,2})' => 'Windows',
            '(msie)([0-9]{1,2}.[0-9]{1,2})' => 'Windows',
            'libwww-perl' => 'Unix',
            'UP.Browser' => 'Windows CE',
            'NetAnts' => 'Windows',
        ];

        $arch_regex = '/\b(x86_64|x86-64|Win64|WOW64|x64|ia64|amd64|ppc64|sparc64|IRIX64)\b/ix';
        $arch = preg_match($arch_regex, $user_agent) ? '64' : '32';

        foreach ($os_array as $regex => $value) {
            if (preg_match('{\b(' . $regex . ')\b}i', $user_agent)) {
                return $value . ' x' . $arch;
            }
        }

        return 'Unknown';
    }
}
?>