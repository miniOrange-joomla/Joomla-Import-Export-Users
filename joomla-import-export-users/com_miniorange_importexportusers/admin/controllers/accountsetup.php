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
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class MiniorangeImportExportUsersControllerAccountsetup extends FormController
{
	public function __construct()
	{
		$this->view_list = 'accountsetup'; // phpcs:ignore Joomla.NamingConventions.ValidVariableName.MemberNotCamelCaps -- Joomla FormController property.
		parent::__construct();
	}

	public function exportUsers()
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = ($input && $input->post) ? $input->post->getArray() : [];

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=exportsettings');

			return;
		}

		$enableExportUsers = isset($post['enable_export_users']) ? $post['enable_export_users'] : 0;

		MoImportExportUtility::updateDBValue('#__miniorange_exportusers', 'enable_export_users', $enableExportUsers);

		$message = Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_MSG_EXPORT_USERS');
		$this->setRedirect('index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=exportsettings', $message);

		$userData = MoImportExportUtility::readUsersTableData();

		if (isset($post['export_groups']) && $post['export_groups'] == 'Export Groups')
		{
			MoImportExportUtility::exportUserGroups();
		}

		MoImportExportUtility::downloadCSVFile($userData);
	}

	public function contactUs()
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$post = ($input && $input->post) ? $input->post->getArray() : [];

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=contact_us');

			return;
		}

		$queryEmail = isset($post['query_email']) ? $post['query_email'] : '';
		$query = isset($post['query_support']) ? $post['query_support'] : '';
		$phone = isset($post['query_phone']) ? $post['query_phone'] : '';
		$countryCode = isset($post['country_code']) ? $post['country_code'] : '';
		$clientTimezone = isset($post['client_timezone']) ? $post['client_timezone'] : '';
		$clientTimezoneOffset = isset($post['client_timezone_offset']) ? $post['client_timezone_offset'] : '';
		$supportType = isset($post['support_type']) ? $post['support_type'] : 'general_query';
		$callDate = isset($post['call_date']) ? $post['call_date'] : '';
		$callTime = isset($post['call_time']) ? $post['call_time'] : '';

		if (MoImportExportUtility::checkEmptyOrNull($queryEmail) || MoImportExportUtility::checkEmptyOrNull($query))
		{
			$this->setRedirect('index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=contact_us', Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_MSG_SUBMIT_QUERY'), 'error');

			return;
		}

		// Sanitize + format phone number with selected dial (country) code
		$dial = preg_replace('/\D+/', '', (string) $countryCode);
		$phoneRaw = trim((string) $phone);

		if (!empty($dial) && $phoneRaw !== '' && strpos($phoneRaw, '+') !== 0)
		{
			$local = preg_replace('/\D+/', '', $phoneRaw);
			$phone = '+' . $dial . ($local !== '' ? (' ' . $local) : '');
		}
		elseif (!empty($dial) && $phoneRaw === '')
		{
			$phone = '+' . $dial;
		}

		$queryWithDetails = $query;

		// Timezone details (priority: browser tz -> Joomla user tz -> global config offset)
		$user = Factory::getUser();
		$timezone = $user->getParam('timezone');

		if (empty($timezone))
		{
			$config = Factory::getConfig();
			$timezone = $config->get('offset');
		}

		// Prefer browser timezone name if provided
		$tzName = trim((string) $clientTimezone);

		if ($tzName === '')
		{
			$tzName = (string) $timezone;
		}

		if (trim($tzName) === '')
		{
			$tzName = 'UTC';
		}

		// Build UTC offset like "+05:30" or "-06:00"
		$utcOffset = '';
		$tzOffsetMinutes = trim((string) $clientTimezoneOffset);

		if ($tzOffsetMinutes !== '' && preg_match('/^-?\d+$/', $tzOffsetMinutes))
		{
			// JS Date.getTimezoneOffset(): minutes behind UTC (e.g. Chicago winter = 360)
			$m = (int) $tzOffsetMinutes;
			$sign = $m > 0 ? '-' : '+';
			$abs = abs($m);
			$hh = str_pad((string) floor($abs / 60), 2, '0', STR_PAD_LEFT);
			$mm = str_pad((string) ($abs % 60), 2, '0', STR_PAD_LEFT);
			$utcOffset = $sign . $hh . ':' . $mm;
		}
		else
		{
			// Fallback: compute offset from timezone name on server (handles DST)
			try
			{
				$tzObj = new \DateTimeZone($tzName);
				$dt = new \DateTime('now', $tzObj);
				$offsetSeconds = (int) $dt->getOffset();
				$sign = $offsetSeconds >= 0 ? '+' : '-';
				$abs = abs($offsetSeconds);
				$hh = str_pad((string) floor($abs / 3600), 2, '0', STR_PAD_LEFT);
				$mm = str_pad((string) floor(($abs % 3600) / 60), 2, '0', STR_PAD_LEFT);
				$utcOffset = $sign . $hh . ':' . $mm;
			}
			catch (\Exception $e)
			{
				$utcOffset = '+00:00';
				$tzName = 'UTC';
			}
		}

		if ($supportType == 'setup_call')
		{
			$queryWithDetails .= "\n\n--- Support Request Details ---";
			$queryWithDetails .= "\nSupport Type: Setup Call";

			// Handle missing or empty call date
			if (!empty($callDate))
			{
				$formattedDate = date('F j, Y', strtotime($callDate));
			}
			else
			{
				$formattedDate = 'Not specified';
			}

			if (!empty($callTime))
			{
				$formattedTime = date('g:i A', strtotime($callTime));
			}
			else
			{
				$formattedTime = 'Not specified';
			}

			$queryWithDetails .= "\nPreferred Call Date: " . $formattedDate;
			$queryWithDetails .= "\nPreferred Call Time: " . $formattedTime;
		}

		$timezoneInfo = $tzName . " (UTC " . $utcOffset . ")";

		$contactUs = new MoImportExportCustomer;
		$submited = json_decode($contactUs->submitContactUs($queryEmail, $phone, $queryWithDetails, $timezoneInfo), true);

		if (json_last_error() == JSON_ERROR_NONE)
		{
			if (is_array($submited) && array_key_exists('status', $submited) && $submited['status'] == 'ERROR')
			{
				$this->setRedirect('index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=contact_us', $submited['message'], 'error');
			}
			else
			{
				if ($submited == false)
				{
					$this->setRedirect('index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=contact_us', Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_MSG_QUERY_NOT_SUBMIT'), 'error');
				}
				else
				{
					if ($supportType == 'setup_call')
					{
						$this->setRedirect('index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=contact_us', Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_MSG_THANK_YOU'));
					}
					else
					{
						$this->setRedirect('index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=contact_us', Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_MSG_GENERAL_QUERY'));
					}
				}
			}
		}
	}
}
