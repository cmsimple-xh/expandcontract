<?php

/**
 * Back-end Expandcontract_XH.
 *
 * @category  CMSimple_XH Plugin
 * @author    svasti <svasti@svasti.de>
 * @copyright 2014-16 by svasti < http://svasti.de >
 * @copyright 2022 The CMSimple_XH Community < https://www.cmsimple-xh.org/ >
 *
 */

define('EXPANDCONTRACT_VERSION','0.7');

if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Registers the plugin menu items
 */
if (function_exists('XH_registerStandardPluginMenuItems')) {
    XH_registerStandardPluginMenuItems(true);
}

if (function_exists('XH_wantsPluginAdministration')
        && XH_wantsPluginAdministration('expandcontract')
        || isset($expandcontract) && $expandcontract == 'true')
{
//    if(!isset($plugin_cf['expandcontract']['version'])
//        || $plugin_cf['expandcontract']['version'] != EXPANDCONTRACT_VERSION) {
//        if($o .= expandcontract_createConfig()) include $pth['folder']['plugins'] . 'expandcontract/config/config.php';
//        }
    $o .= print_plugin_admin('on');
    if (!$admin || $admin == 'plugin_main') {
        $o .= '<h1>Expandcontract_XH</h1>'
            . '<p>Version '. $plugin_cf['expandcontract']['version']
            . '<br>&copy; 2014-16 <a href="http://svasti.de" target="_blank">svasti</a>'
            . '<br>&copy; 2022 <a href="https://www.cmsimple-xh.org/" target="_blank">The CMSimple_XH Community</a>
            . <br>Licence: <a target="_blank" href="https://www.gnu.org/licenses/gpl-3.0.en.html">GPLv3</a></p>'
            . '<p>'. $plugin_tx['expandcontract']['plugin_explanation']. '</p>'
            . '<h2>'.$plugin_tx['expandcontract']['plugin_call'].'</h2>'
            . '<p>'. $plugin_tx['expandcontract']['link_hidden_subpages']. '</p>'
            . '<p>'. $plugin_tx['expandcontract']['link_multiple_pages']. '</p>'
            . '<p>'. $plugin_tx['expandcontract']['link_single_page']. '</p>';
    }
	$o .= plugin_admin_common($action, $admin, $plugin);
}

/**
 * Helper to fill stylesheet selectlist in config.php
 */
function expandGetCssFiles() {
        global $pth;
        $temp = glob($pth['folder']['plugins'] . 'expandcontract/css/*.css');
        $files = array('');
        foreach ($temp as $file) {
            if (basename($file) !== 'stylesheet.css') {
                $files[] = basename($file);
            }
        }
        return $files;
}
