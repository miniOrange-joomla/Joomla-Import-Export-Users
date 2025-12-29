<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
/**
 * @package     Joomla.Package
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

class pkg_ImportexportusersInstallerScript
{
    /**
     * This method is called after a component is installed.
     *
     * @param \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function install($parent)
    {
        require_once JPATH_ADMINISTRATOR . '/components/com_miniorange_importexportusers/helpers/mo_importexport_utility.php';
        $siteName = $_SERVER['SERVER_NAME'];
        $email = Factory::getConfig()->get('mailfrom');
        $moPluginVersion = MoImportExportUtility::GetPluginVersion();
        $jCmsVersion = MoImportExportUtility::getJoomlaCmsVersion();
        $phpVersion = phpversion();
        $moServerType = MoImportExportUtility::getServerType();
        $query1 = '[Plugin ' . $moPluginVersion . ' | PHP ' . $phpVersion .' | Joomla Version '. $jCmsVersion .' | Server Type '. $moServerType .']';
        $content = '<div>
            Hello,<br><br>
            Import Export Users Plugin has been successfully installed on the following site.<br><br>
            <strong>Company:</strong> <a href="http://' . $siteName . '" target="_blank">' . $siteName . '</a><br>
            <strong>Admin Email:</strong> <a href="mailto:' . $email . '">' . $email . '</a><br>
            <strong>System Information:</strong> ' . $query1 . '<br><br>
        </div>';
        MoImportExportUtility::send_efficiency_mail($email, $content);

    }

    /**
     * This method is called after a component is uninstalled.
     *
     * @param \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function uninstall($parent)
    {
        
    }

    /**
     * This method is called after a component is updated.
     *
     * @param \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function update($parent)
    {
        
    }

    /**
     * Runs just before any installation action is performed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param string $type - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $parent)
    {
        
    }

    /**
     * Runs right after any installation action is performed on the component.
     *
     * @param string $type - Type of PostFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    function postflight($type, $parent)
    {
        if ($type == 'uninstall') {
            return true;
        }
        $this->showInstallMessage('');
    }

    protected function showInstallMessage($messages = array())
    {
        ?>
        <style>
            .mo-row {
                width: 100%;
                display: block;
                margin-bottom: 2%;
            }
        
            .mo-row:after {
                clear: both;
                display: block;
                content: "";
            }
        
            .mo-column-2 {
                width: 19%;
                margin-right: 1%;
                float: left;
            }
        
            .mo-column-10 {
                width: 80%;
                float: left;
            }
            .btn {
                display: inline-block;
                font-weight: 300;
                text-align: center;
                vertical-align: middle;
                user-select: none;
                background-color: transparent;
                border: 1px solid transparent;
                padding: 4px 12px;
                font-size: 0.85rem;
                line-height: 1.5;
                border-radius: 0.25rem;
                transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            } 
        
            .btn-cstm, .btn-cstm:hover {
                background: #001b4c;
                border: none;
                font-size: 1.1rem;
                padding: 0.3rem 1.5rem;
                color: #fff !important;
                cursor: pointer;
            }

            :root[data-color-scheme=dark] {
                .btn-cstm {
                    color: white;
                    background-color: #000000;
                    border-color:1px solid #ffffff; 
                }

                .btn-cstm:hover {
                    background-color: #000000;
                    border-color: #ffffff; 
                }
            }
		
        </style>

        <h4><strong>Steps to use the Import Export Users Free plugin:</strong></h4>
        <ul>
            <li>Click on <strong>Components</strong>.</li>
            <li>Click on <strong>Component - Import Export Users</strong> and select the <strong>Overview</strong> tab.</li>
            <li>You can now start using the Import Export Users plugin.</li>
        </ul>
        <div class="mo-row">
            <a class="btn btn-cstm" onClick="window.location.reload();"
               href="index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=overview">Start Using miniOrange Import Export Users plugin</a>
            <a class="btn btn-cstm" href="https://plugins.miniorange.com/import-export-users-for-joomla"
               target="_blank">Read the miniOrange documents</a>
            <a class="btn btn-cstm" href="https://www.miniorange.com/contact" target="_blank">Get Support!</a>
        </div>
        <?php
    }

}