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

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Version;
use Joomla\CMS\Router\Route;

$document = Factory::getDocument();
$document->addScript(Uri::base() . 'components/com_miniorange_importexportusers/assets/JS/bootstrap.js');
$document->addScript(Uri::base() . 'components/com_miniorange_importexportusers/assets/JS/utility.js');
$document->addScript('https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_importexportusers/assets/css/bootstrap-select-min.css');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_importexportusers/assets/css/miniorange_boot.css');
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_importexportusers/assets/css/miniorange_importexportusers.css');

HTMLHelper::_('jquery.framework');

$jsonFile = Uri::base() . 'components/com_miniorange_importexportusers/assets/json/tabs.json';

function getJsonData($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    // Disable SSL verification (ONLY for local/dev environments)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        return null;
    }

    curl_close($ch);
    return $response;
}

$tabsJson = getJsonData($jsonFile);
$tabs = json_decode($tabsJson, true);

if (MoImportExportUtility::is_curl_installed() == 0){ ?>
    <p class="mo_impexp_red_color">(<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_WARNING');?><a href="http://php.net/manual/en/curl.installation.php" target="_blank"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PHP_CURL_EXTENSION');?></a><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_INSTALL');?> )</p>
    <?php
}

$app = Factory::getApplication();
$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
$get = ($input && $input->get) ? $input->get->getArray() : [];
$impexp_settings_tab = 'import_export_settings';
$active_tab = ($input && $input->get) ? $input->get->getArray() : [];
if (isset($active_tab['tab-panel']) && !empty($active_tab['tab-panel'])) {
    $impexp_settings_tab = $active_tab['tab-panel'];
}

$j_version = new Version;
$jcms_version = $j_version->getShortVersion();
?> 
<div class="mo_boot_row mo_boot_p-3">
    <div class="mo_boot_col-sm-12">
        <a class=" mo_boot_btn mo_boot_px-4 mo_boot_py-1 btn-users_sync mo_boot_float-right" href="index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=contact_us"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_SUPPORT');?></a>
    </div>
</div>
<div class="form-horizontal">
    <div class="mo_import_export_nav-tab-wrapper mo_boot_row mo_import_export_tab">
        <?php foreach ($tabs as $Key => $tab): ?>
            <a id="<?php echo $tab['id']; ?>" 
                class="mo_boot_col mo_boot_py-3 mo_import_export_nav-tab <?php echo $impexp_settings_tab == $Key ? 'mo_nav_tab_active' : ''; ?>"
                href="<?php echo $tab['href']; ?>">
                <?php echo Text::_($tab['text']); ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<div class="tab-content" id="myTabContent">
    <div id="overview" class="tab-pane <?php if ($impexp_settings_tab == 'overview') echo 'active'; ?>">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12 mo_import_export_tab">
                <?php plugin_overview(); ?>
            </div> 
        </div>
    </div>

    <div id="export_configuration" class="tab-pane <?php if ($impexp_settings_tab == 'exportsettings') echo 'active'; ?>">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12 mo_import_export_tab">
                <?php export_configuration(); ?>
            </div>
        </div>
    </div>

    <div id="import_configuration" class="tab-pane <?php if ($impexp_settings_tab == 'importsettings') echo 'active'; ?>">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12 mo_import_export_tab">
                <?php import_configuration(); ?>
            </div>
        </div>
    </div>

    <div id="cron_import" class="tab-pane <?php if ($impexp_settings_tab == 'cronimport') echo 'active'; ?>">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12 mo_import_export_tab">
                <?php cron_import(); ?>
            </div>
        </div>
    </div>

    <div id="licensing_page" class="tab-pane <?php if ($impexp_settings_tab == 'license_page') echo 'active'; ?>">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12 mo_import_export_tab"> 
                <?php license_page(); ?>
            </div>
        </div>
    </div>

    <div id="contact_us" class="tab-pane <?php echo $impexp_settings_tab == 'contact_us' ? 'active' : ''; ?>">
			<div class="mo_boot_row">
				<div class="mo_boot_col-sm-12 mo_import_export_tab" >
					<?php support_form();?>
				</div>
			</div>
    </div>
</div>

<?php
function plugin_overview()
{
    ?>
    <div id="sp_support_usync">
        <div class="mo_boot_row mo_boot_p-4">
            <div class="mo_boot_col-sm-12">
                <form name="f" method="post" action="index.php?option=com_miniorange_importexportusers&view=accountsetup&task=accountsetup.pluginoverview"><br>
                    <div class="mo_boot_row">
                        <div class="mo_boot_col-12 mo_boot_text-justify">
                            <p><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_OVERVIEW_TEXT');?></p>
                            <p><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_OVERVIEW_TEXT2');?></p>
                            <p><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_OVERVIEW_TEXT1');?></p>
                            <a class="mo_boot_btn btn-users_sync mo_boot_px-3 mo_boot_mx-1" target="_blank" href="https://plugins.miniorange.com/import-export-users-for-joomla"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_VISIT_SITE');?></a>
                            <a class="mo_boot_btn btn-users_sync mo_boot_px-3 mo_boot_mx-1" href="<?php echo Uri::root().'administrator/index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=license_page';?>"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_LICENSE_PLAN');?></a>
                            <a class="mo_boot_btn btn-users_sync mo_boot_px-3 mo_boot_mx-1" target="_blank" href="https://plugins.miniorange.com/joomla-import-export-users"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_GUIDES');?></a>
                            <a class="mo_boot_btn btn-users_sync mo_boot_px-3 mo_boot_mx-1" target="_blank" href="https://faq.miniorange.com/kb/joomla/">FAQ</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php

}

function export_configuration()
{
    $result = MoImportExportUtility::getCustomerDetails();
    $disabled = 'disabled';
    $enable_export_users = isset($result['enable_export_users']) ? $result['enable_export_users'] : '';
    $groups = MoImportExportUtility::loadGroups();
    ?>

    <div id="sp_support_usync">
        <div class="mo_boot_row mo_boot_p-4">
            <div class="mo_boot_col-sm-12">
                <div class="mo_boot_row">
                    <h2 class="mo_import_export_heading mo_boot_px-3"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_EXPORT_USERS');?><sup><a href="https://plugins.miniorange.com/joomla-import-export-users#step2" target="_blank"><div class="fa fa-question-circle-o mo_tooltip"><span class="mo_tooltiptext" ><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_EXPORT_TEXT');?></span></div></a></sup></h2>
                </div>
            </div>
            <form name="f" method="post" class="mo_boot_col-12" action="index.php?option=com_miniorange_importexportusers&view=accountsetup&task=accountsetup.exportUsers" ><br>
                <div class="mo_boot_row mo_boot_mt-1">
                    <div class="mo_boot_col-6">
                        <?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_EXPORT_FORMAT');?>
                    </div>
                    <div class="mo_boot_col-3">
                        <button type="submit" name="export_groups" value="Export Groups" class="mo_boot_btn btn-users_sync mo_boot_col-sm-12"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_EXPORT_GROUPS'); ?></button>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-1">
                    <div class="mo_boot_col-6">
                        <?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_EXPORT_FORMAT_2');?>
                        <input type="hidden" name="export_user_profile" value="false">
                    </div>
                    <div class="mo_boot_col-3"> 
                        <button type="submit" name="export_users" class="mo_boot_btn btn-users_sync mo_boot_col-sm-12"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_EXPORT_USERS');?></button>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-1">
                    <div class="mo_boot_col-6">
                        <?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_EXPORT_FORMAT_3');?><sup><a href='<?php echo Route::_('index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=license_page'); ?>'><img class="crown_img_small ml-2" src="<?php echo Uri::base();?>/components/com_miniorange_importexportusers/assets/images/crown.webp"></a></sup>
                        <input type="hidden" name="export_user_profile" value="true">
                    </div>
                    <div class="mo_boot_col-3"> 
                        <button type="submit" name="export_users_profile" class="mo_boot_btn btn-users_sync mo_boot_col-sm-12" disabled><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_EXPORT_GROUPS_PROFILE');?></button>
                    </div>
                </div>
                <div class="mo_boot_row">
                    <div class="mo_boot_col-12">
                        <details open>
                            <summary class="mo_users_summary">&#x2795;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_ROLE_BASED');?>
                            <sup><?php if ($disabled == 'disabled') : ?><a href="<?php echo Route::_('index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=license_page'); ?>"><img class="crown_img_small ml-2" src="<?php echo Uri::base();?>/components/com_miniorange_importexportusers/assets/images/crown.webp" alt="Crown"></a><?php endif; ?></sup>
                            </summary>
                            <div class="mo_boot_mx-4 mo_users_no_cursor">
                            <?php
                            
                            echo '<table class="mo_boot_col-12">';
                            $counter = 0;  
                            
                            foreach ($groups as $key => $value) {
                                if ($value['title'] != 'Public' && $value['title'] != 'Guest') {
                                    if ($counter % 3 == 0) {
                                        if ($counter > 0) {
                                            echo '</tr>'; 
                                        }
                                        echo '<tr>';
                                    }
                            
                                    echo '<td><div class="mo_boot_form-check mo_boot_form-switch"><input type="checkbox" class="mo_boot_form-check-input" name="role_based_tfa_' . str_replace(' ', '_', $value['title']) . '" ' . $disabled . '/>&emsp;' . $value['title'] .'</div> </td>';
                            
                                    $counter++;
                                }
                            }
                            
                            while ($counter % 3 != 0) {
                                echo '<td></td>';
                                $counter++;
                            }
                            
                            if ($counter != 0) { 
                                echo '</tr>';
                            }
                            
                            echo '</table>'; 
                            

                            ?>

                            <button type="button" class="mo_boot_btn btn-users_sync mo_boot_my-4" disabled><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_EXPORT_USERS'); ?></button>
                        </div>
                        </details>

                        <details open>
                            <summary class="mo_users_summary">&#x2795;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_COLUMN_BASED');?>
                            <sup><?php if ($disabled == 'disabled') : ?><a href="<?php echo Route::_('index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=license_page'); ?>"><img class="crown_img_small ml-2" src="<?php echo Uri::base();?>/components/com_miniorange_importexportusers/assets/images/crown.webp" alt="Crown"></a><?php endif; ?></sup>
                            </summary><br>
                            <div class="mo_boot_mx-4 mo_users_no_cursor">
                                <div class="mo_boot_form-check mo_boot_form-switch">
                                <input type="checkbox" class="mo_boot_form-check-input" disabled/>&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_ENABLE');?></div><br>
                                <div class="text-center">
                                <textarea class="mo_boot_col-12" rows="5" placeholder="<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_PLACEHOLDER');?>" disabled></textarea>
                                </div>
                                <p class="alert alert-info mo_boot_col-12"><strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_NOTE');?></strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_ENABLE_INFO');?>
                                <strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_FOR_EX');?></strong></p>
                            </div>
                        </details>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
}

function import_configuration()
{
    $result = MoImportExportUtility::getCustomerDetails();
    $disabled = 'disabled';
    $groups = MoImportExportUtility::loadGroups();
    ?>
    <div id="sp_support_usync">
        <div class="mo_boot_row mo_boot_p-4">
            <div class="mo_boot_col-sm-12">
                <div class="mo_boot_row">
                    <h2 class="mo_import_export_heading"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_IMP_ROLE');?><sup><a href="https://plugins.miniorange.com/joomla-import-export-users#step3" target="_blank"><div class="fa fa-question-circle-o mo_tooltip"><span class="mo_tooltiptext" ><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_EXPORT_TEXT');?></span></div></a></sup><a href='<?php echo Route::_('index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=license_page'); ?>'>&nbsp;<sup><img class="crown_img_small ml-2" src="<?php echo Uri::base();?>/components/com_miniorange_importexportusers/assets/images/crown.webp"></a></sup></h2>
                </div>
            </div>
            <div>
                <p class="alert alert-info mo_boot_col-sm-12"><b><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_NOTE');?></b> <?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_NOTE_INFO');?></p>
            </div>
            <div class="mo_boot_col-12 mo_boot_mt-1">
                <div class="mo_boot_row mo_boot_mt-2">
                    <div class="mo_boot_col-3">
                        <input type="file" <?php echo $disabled; ?> class="mo_boot_col-12 mo_sync_upload_file_btn mo_sync_btn mo_sync_btn_primary" name="csv_import_file_groups" />
                    </div>
                    <div class="mo_boot_col-3">
                        <div class="mo_tooltip" >
                            <button type="button" class="mo_boot_btn btn-users_sync mo_boot_col-12 mo_boot_mx-4" disabled><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_IMP_ROLE');?></button>
                            <span class="mo_tooltiptext" ><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_FEATURES');?></span>
                        </div> 
                    </div>
                </div>
            </div>
            <div class="mo_boot_mt-5">
                <h3 class="mo_import_export_heading"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_IMP_USERS');?></h3>
                <p class="alert alert-info mo_boot_col-sm-12"><b><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_NOTE');?></b><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_USER_INFO');?></p>
            </div>
            <div class="mo_boot_col-12 mo_boot_mt-1">
                <div class="mo_boot_row mo_boot_mt-1">
                    <b><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_USER_A');?> </b>
                    <div class="mo_boot_col-12 mo_boot_mt-2">
                        <div class="mo_boot_row mo_boot_mt-2">
                            <div class="mo_boot_col-3">
                                <input type="file" <?php echo $disabled; ?> class="mo_boot_col-12 mo_sync_upload_file_btn mo_sync_btn mo_sync_btn_primary" name="csv_import_file_groups" />
                            </div>
                            <div class="mo_boot_col-3">
                                <div class="mo_tooltip" >
                                    <button type="button" class="mo_boot_btn btn-users_sync mo_boot_col-12 mo_boot_mx-4" disabled><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_IMPORT_USERS');?></button>
                                    <span class="mo_tooltiptext" ><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_FEATURES');?></span>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-2">
                    <b><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_USER_B');?></b>
                    <div class="mo_boot_col-12 mo_boot_mt-2">
                        <div class="mo_boot_row mo_boot_mt-2">
                            <div class="mo_boot_col-3">
                                <input type="file" <?php echo $disabled; ?> class="mo_boot_col-12 mo_sync_upload_file_btn mo_sync_btn mo_sync_btn_primary" name="csv_import_file_groups" />
                            </div>
                            <div class="mo_boot_col-3">
                                <div class="mo_tooltip" >
                                    <button type="button" class="mo_boot_btn btn-users_sync mo_boot_col-12 mo_boot_mx-4" disabled><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_IMPORT_USERS_PROFILE');?></button>
                                    <span class="mo_tooltiptext" ><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_FEATURES');?></span>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mo_boot_row mo_boot_mt-3">
                <div class="mo_boot_col-12 mo_boot_mt-1">
                    <details open>
                        <summary class="mo_users_summary">&#x2795;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_ROLE_BASED_IMPORT');?></summary>
                        <div class="mo_boot_mx-4 mo_users_no_cursor">
                        <?php 
                        echo '<table class="mo_boot_col-12">';
                        $counter = 0;  
                        
                        foreach ($groups as $key => $value) {
                            if ($value['title'] != 'Public' && $value['title'] != 'Guest') {
                                if ($counter % 3 == 0) {
                                    if ($counter > 0) {
                                        echo '</tr>'; 
                                    }
                                    echo '<tr>';
                                }
                        
                                echo '<td><div class="mo_boot_form-check mo_boot_form-switch"><input type="checkbox" class="mo_boot_form-check-input" name="role_based_tfa_' . str_replace(' ', '_', $value['title']) . '" ' . $disabled . '/>&emsp;' . $value['title'] . '</div></td>';
                        
                                $counter++;
                            }
                        }
                        
                        while ($counter % 3 != 0) {
                            echo '<td></td>';
                            $counter++;
                        }
                        
                        if ($counter != 0) { 
                            echo '</tr>';
                        }
                        
                        echo '</table>'; 
                        ?>
                        </div>
                    </details>

                    <details open>
                        <summary class="mo_users_summary">&#x2795;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_COLUMN_BASED_IMPORT');?>
                        </summary><br>
                        <div class="mo_users_no_cursor">
                            <div class="mo_boot_form-check mo_boot_form-switch">
                                <input type="checkbox" class="mo_boot_form-check-input" disabled/>&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_ENABLE_COLUMN_BASED_IMPORT');?>
                            </div><br>
                            <textarea name="" class="mo_boot_col-sm-12" rows="5" placeholder="<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_COMMA_SEPERATED_PLACEHOLDER');?>" disabled></textarea>
                            <p class="alert alert-info mo_boot_col-sm-12"><strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_NOTE');?></strong> <?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_COLUMN_INFO');?> <strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_FOR_EX');?></strong></p>
                        </div>
                    </details>

                    <details open>
                        <summary class="mo_users_summary">&#x2795;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPDATE_USER');?></summary>
                        <div class="mo_users_no_cursor">
                            <div class="mo_boot_form-check mo_boot_form-switch">
                                <input type="checkbox" class="mo_boot_form-check-input" disabled/>&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_ENABLE_UPDATE_USERS');?></div><br>
                            <div class="mo_boot_form-check mo_boot_form-switch">
                                <input type="checkbox" class="mo_boot_form-check-input" disabled/>&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_DELETE_USER');?></div>
                            <div class="mo_boot_row mo_boot_mt-3">
                                <div class="mo_boot_col-sm-4">
                                    <strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPDATE_ROLE');?></strong>
                                </div>
                                <div class="mo_boot_col-sm-8">
                                    <select class="mo-form-control mo-form-control-select">
                                        <option value="dont_update_email"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_NO');?></option>
                                        <option disabled value="update_and_override_roles"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_UPDATE_OVERRIDE');?></option>
                                        <option disabled value="add_new_keep_old_roles"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_NEW_GROUP');?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </details>
                </div>
            </div><br>
        </div>
    </div>
    <?php
}

function cron_import()
{
    
    $disabled = 'disabled';
    ?>
    <div class="mo_boot_row mr-1 mo_boot_p-3 mo_boot_px-2" id="tabhead">
        <div class="mo_boot_col-sm-12">
            <form action="" method="post" name="" enctype="multipart/form-data">
                <div class="mo_boot_col-sm-12">
                    <div class="mo_boot_row">
                        <h2 class="mo_import_export_heading"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PERIODIC_USER_IMPORT');?> <sup><a href="https://plugins.miniorange.com/joomla-import-export-users#step4" target="_blank"><div class="fa fa-question-circle-o mo_tooltip"><span class="mo_tooltiptext" ><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_EXPORT_TEXT');?></span>
                        <?php if ($disabled == 'disabled') : ?><a href="<?php echo Route::_('index.php?option=com_miniorange_importexportusers&view=accountsetup&tab-panel=license_page'); ?>"><img class="crown_img_small ml-2" src="<?php echo Uri::base();?>/components/com_miniorange_importexportusers/assets/images/crown.webp" alt="Crown"></a><?php endif; ?></div></a></sup></h2>
                    </div>
                </div>

                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-sm-5">
                        <strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PERIODIC_IMPORT');?></strong>
                    </div>
                    <div class="mo_boot_col-sm-6">
                        <div class="mo_boot_form-check mo_boot_form-switch">
                            <input type="checkbox" class="mo_boot_form-check-input" disabled>
                        </div>
                    </div>
                </div>

                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-sm-5">
                        <strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_FILE_PATH');?> </strong>
                    </div>
                    <div class="mo_boot_col-sm-6">
                        <input class="mo-form-control" type="url" disabled placeholder="<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PATH_PLACEHOLDER');?>	" name=""/>
                    </div>
                </div>

                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-sm-5">
                        <strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PERIOD');?></strong>
                    </div>
                    <div class="mo_boot_col-sm-6">
                        <select class="mo-form-control mo-form-control-select">
                            <option value="hourly"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_HOURLY');?></option>
                            <option disabled value="daily"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_DAILY');?></option>
                            <option disabled value="weekly"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_WEEKLY');?></option>
                            <option disabled value="monthly"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_MONTHLY');?></option>
                        </select>
                        <strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PLUGIN_REOCCUR');?></strong>
                    </div>
                </div>

                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-lg-12 mo_boot_mt-1">
                        <h2 class="mo_import_export_heading"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_ADDITIONAL_FEATURE');?></h2>
                    </div>
                </div>

                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-sm-5">
                        <strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_DELETE_CSV');?></strong>
                    </div>
                    <div class="mo_boot_col-sm-6">
                        <div class="mo_boot_form-check mo_boot_form-switch">
                            <input type="checkbox" class="mo_boot_form-check-input" disabled>
                        </div>
                    </div>
                </div>

                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-sm-5">
                        <strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_ROLE_USER');?></strong>
                    </div>
                    <div class="mo_boot_col-sm-6">
                        <div class="mo_boot_form-check mo_boot_form-switch">
                            <input type="checkbox" class="mo_boot_form-check-input" disabled>&emsp;
                        </div>
                    </div>
                </div>

                <div class="mo_boot_row mo_boot_mt-5">
                    <div class="mo_boot_col-sm-10 text-center">
                        <input type="submit" class=" mo_boot_btn btn-users_sync" value="<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_CRON_OPTIONS');?>" disabled/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
}

function license_page()
{
?>
    <div class="mo_boot_col-sm-12">
        <div class="mo_import_export-pricing-container cd-has-margins"><br>
            <ul class="cd-pricing-list cd-bounce-invert" >
                <li class="cd-black" >
                    <ul class="cd-pricing-wrapper">
                        <li id="singlesite_tab" data-type="singlesite" class="mosslp is-visible">
                            <header class="cd-pricing-header">
                                <h2 class="mo_boot_mb-3"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_FREE');?><br/></h2><span class="mo_usync_plan_description"><strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_EXPORT_USERS');?></strong></span>
                            </header>
                            <div class="mo_impexp_text_center">
                                <span id="plus_total_price" class="mo_impexp_pricing">$0</span><br><span class="mo_usync_note"></span> <br/>
                            </div>
                            <footer class="cd-pricing-footer"><br> 
                                <?php
                                    echo '<a target="" class="cd-select mo_impexp_upgrade_active"><h4>' .Text::_('COM_MINIORANGE_IMPORTEXPORT_ACTIVE_PLAN').'</h4></a>';
                                ?>
                            </footer><br>
                            <div class="cd-pricing-body">
                                <ul class="cd-pricing-features">
                                    <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F1');?></li>
                                    <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F2');?></li>
                                    <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F3');?></li>
                                    <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F4');?></li>
                                    <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F5');?></li>
                                    <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F6');?></li>
                                    <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F7');?></li>
                                    <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F8');?></li>
                                    <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F9');?></li>
                                    <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F10');?></li>
                                    <li>&#10060;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F11');?></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </li>               
                <li class="cd-black">
                    <ul class="cd-pricing-wrapper">
                        <li id="singlesite_tab" data-type="singlesite" class="mosslp is-visible">
                            <header class="cd-pricing-header">
                            <h2 class="mo_boot_mb-3"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PREMIUM');?><br/></h2><span class="mo_usync_plan_description"><strong>Import / Export Users</strong></span>
                            </header>
                            <div class="mo_impexp_text_center">
                                <span id="plus_total_price" class="mo_impexp_pricing">$149</span><br><span class="mo_usync_note"></span> <br/>
                            </div>
                            <footer class="cd-pricing-footer"><br>
                                <?php
                                    $user_email="";
                                    $redirect1= "https://portal.miniorange.com/initializePayment?requestOrigin=joomla_import_export_premium_plan";
                                    echo '<a target="_blank" class="cd-select mo_impexp_upgrade_now" href="'.$redirect1.'" ><h4>'.Text::_('COM_MINIORANGE_IMPORTEXPORT_UPGRADE_NOW').'</h4></a>';
                                ?>
                            </footer><br>
                            <div class="cd-pricing-body">
                                <ul class="cd-pricing-features">
                                    <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F1');?></li>
                                    <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F2');?></li>
                                    <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F3');?></li>
                                    <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F4');?></li>
                                    <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F5');?></li>
                                    <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F6');?></li>
                                    <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F7');?></li>
                                    <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F8');?></li>
                                    <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F9');?></li>
                                    <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F10');?></li>
                                    <li>&#9989;&emsp;<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_F11');?></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul> 
        </div> 
   
        <div class="mo_impexp_mo_works_step" id="upgrade-steps">
            <div  class="pt-2">
                <h2 class="mo_impexp_text_center"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_PREMIUM');?></h2>
            </div> <hr>
            <section   id="section-steps" >
                <div class="mo_boot_col-sm-12 mo_boot_row ">
                    <div class=" mo_boot_col-sm-6 mo_import_export_works-step">
                        <div><strong>1</strong></div>
                        <p><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_ONE');?></p>
                    </div>
                    <div class="mo_boot_col-sm-6 mo_import_export_works-step">
                        <div><strong>4</strong></div>
                        <p><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_FOUR');?></p>
                    </div>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_row ">
                    <div class=" mo_boot_col-sm-6 mo_import_export_works-step">
                        <div><strong>2</strong></div>
                        <p><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_TWO');?></p>
                    </div>
                    <div class="mo_boot_col-sm-6 mo_import_export_works-step">
                        <div><strong>5</strong></div>
                        <p><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_FIVE');?></p>
                    </div>
                </div>

                <div class="mo_boot_col-sm-12 mo_boot_row ">
                    <div class="mo_boot_col-sm-6 mo_import_export_works-step">
                        <div><strong>3</strong></div>
                        <p><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_THREE');?></p>
                    </div>
                    <div class=" mo_boot_col-sm-6 mo_import_export_works-step">
                        <div><strong>6</strong></div>
                        <p><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_UPGRADE_SIX');?></p>
                    </div>
                </div>
            </section>
        </div>

        <div class="mo_impexp_mo_works_step" id="upgrade-steps">
            <div  class="pt-1">
                <h2 class="mo_impexp_text_center"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_FAQ');?></h2>
            </div> <hr>
		    
            <div class="mx-4">
		        <div class="mo_boot_row">
			        <div class="mo_boot_col-sm-6">
				        <h3 class="mo_import_export_faq_page"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_INSTANCE');?></h3>
				        <div class="mo_import_export_faq_body">
					        <p><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_INSTANCE_TEXT');?>
                            </p>
				        </div>
				        <hr class="mo_import_export_hr_line">
			        </div>
		
                    <div class="mo_boot_col-sm-6">
                        <h3 class="mo_import_export_faq_page"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_TECHNICAL_SUPPORT');?></h3>
                        <div class="mo_import_export_faq_body">
                            <p><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_TECH_SUPPORT_TEXT');?>
                            </p>
                        </div>
                        <hr class="mo_import_export_hr_line">
                    </div>
                </div>
		        
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-6">
                        <h3 class="mo_import_export_faq_page"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_USER_DATA');?></h3>
                        <div class="mo_import_export_faq_body">
                            <p><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_USER_DATA_TEXT');?>
                            </p>
                        </div>
                        <hr class="mo_import_export_hr_line">
                    </div>

                    <div class="mo_boot_col-sm-6">
                        <h3 class="mo_import_export_faq_page"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_CUSTOMIZATION');?></h3>
                        <div class="mo_import_export_faq_body">
                            <p><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_CUSTOMIZATION_TEXT');?>
                            </p>
                        </div>
                        <hr class="mo_import_export_hr_line">
                    </div>
                </div>

                <div class="mo_boot_row">	
                    <div class="mo_boot_col-sm-6">
                        <h3 class="mo_import_export_faq_page"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_LICENSE_PLUGIN');?></h3>
                        <div class="mo_import_export_faq_body">
                            <p><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_LICENSE_PLUGIN_TEXT');?>
                            </p>
                        </div>
                        <hr class="mo_import_export_hr_line">
                    </div>
                    <div class="mo_boot_col-sm-6">
                        <h3 class="mo_import_export_faq_page"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_REFUND_POLICY');?></h3>
                        <div class="mo_import_export_faq_body">
                            <p><?php echo Text::_('COM_MINIORANGE_REFUND_POLICY');?></p>
                        </div>
                        <hr class="mo_import_export_hr_line">
                    </div>
                </div>
			</div>
		    <script>
			    var test = document.querySelectorAll('.mo_import_export_faq_page');
			    test.forEach(function(header) {
				    header.addEventListener('click', function() {
					    var body = this.nextElementSibling;
					    body.style.display = body.style.display === 'none' || body.style.display =="" ? 'block' : 'none';
  			    	});
			    });
		    </script>
        </div>

    </div>       
    <?php
}

function support_form()
{
    $current_user = Factory::getUser();
    $result       = MoImportExportUtility::getCustomerDetails();
    $admin_email  = isset($result['email']) ? $result['email'] : '';
    $admin_phone  = isset($result['admin_phone']) ? $result['admin_phone'] : '';
    if($admin_email == '')
        $admin_email = $current_user->email;
    ?>
    <div class="mo_boot_col-12">
        <form  name="f" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_importexportusers&view=accountsetup&task=accountsetup.contactUs');?>">
            <div id="sp_support_usync">
                <h3 class="mo_import_export_heading mo_boot_mt-3"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_SUPPORT');?></h3>
                <div class="mo_boot_col-12 mo_boot_mt-2">
                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-6 mo_boot_px-2">
                            <input type="radio" id="support_general" name="support_type" value="general_query" checked onclick="toggleCallTimeField()" style="display: none;">
                            <label for="support_general" class="support-type-btn" id="general_query_btn">
                                <strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_GENERAL_QUERY');?></strong>
                            </label>
                        </div>
                        <div class="mo_boot_col-6 mo_boot_px-2">
                            <input type="radio" id="support_call" name="support_type" value="setup_call" onclick="toggleCallTimeField()" style="display: none;">
                            <label for="support_call" class="support-type-btn" id="setup_call_btn">
                                <strong><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_SETUP_CALL');?></strong>
                            </label>
                        </div>
                    </div>

                    <div class="mo_boot_row mo_boot_mt-3">
                        <div class="mo_boot_col-3 mo_boot_offset-1">
                            <?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_EMAIL');?><span class="mo_impexp_red_color">*</span>
                        </div>
                        <div class="mo_boot_col-6">
                            <input type="email" class="mo-form-control" id="query_email" name="query_email" value="<?php echo $admin_email; ?>" placeholder="<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_EMAIL_PLACEHOLDER');?>" required />
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-3 mo_boot_offset-1"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PHONE_NO');?> </div>
                        <div class="mo_boot_col-6">
                            <input type="tel" pattern="[\+]\d{11,14}|[\+]\d{1,4}([\s]{0,1})(\d{0}|\d{9,10})" class="mo-form-control" name="query_phone" id="query_phone" value="<?php echo $admin_phone; ?>" placeholder="<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_PHONE_PLACEHOLDER');?>"/>
                        </div>
                    </div>

                    <div class="mo_boot_row mo_boot_mt-2" id="call_date_field" style="display: none;">
                        <div class="mo_boot_col-3 mo_boot_offset-1">
                            <?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_DATE');?><span class="mo_impexp_red_color">*</span>
                        </div>
                        <div class="mo_boot_col-6">
                            <input type="date" class="mo-form-control" id="call_date" name="call_date" placeholder="<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_DATE_PLACEHOLDER');?>"/>
                        </div>
                    </div>

                    <div class="mo_boot_row mo_boot_mt-2" id="call_time_field" style="display: none;">
                        <div class="mo_boot_col-3 mo_boot_offset-1">
                            <?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_TIME');?><span class="mo_impexp_red_color">*</span>
                        </div>
                        <div class="mo_boot_col-6">
                            <input type="time" class="mo-form-control" id="call_time" name="call_time" placeholder="<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_TIME_PLACEHOLDER');?>"/>
                        </div>
                    </div>

                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-sm-3 mo_boot_offset-1"><?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_QUERY');?><span class="mo_impexp_red_color">*</span></div>
                        <div class="mo_boot_col-sm-6">
                            <textarea id="query_support" class = "mo_boot_px-3 mo-form-control" name="query_support" style="height:150px !important" placeholder="<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_QUERY_PLACEHOLDER');?>" required></textarea>
                        </div>
                    </div>

                    <div class="mo_boot_row mo_boot_my-4">
                        <div class="mo_boot_col-sm-11 mo_boot_text-center">
                            <input type="submit" name="send_query" value="<?php echo Text::_('COM_MINIORANGE_IMPORTEXPORTUSERS_SUBMIT_QUERY');?>" class=" mo_boot_btn btn-users_sync" class="mt-5"/>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}
?>