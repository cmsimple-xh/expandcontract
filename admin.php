<?php

/**
 * Back-end Expandcontract_XH.
 *
 * @category  CMSimple_XH Plugin
 * @author    svasti <svasti@svasti.de>
 * @copyright 2014-16 by svasti < http://svasti.de >
 * @copyright 2022 The CMSimple_XH Community < https://www.cmsimple-xh.org/ >
 * @version   1.0 - 2022.03.31
 */

if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Registers the plugin menu items
 */
if (function_exists('XH_registerStandardPluginMenuItems')) {
    XH_registerStandardPluginMenuItems(false);
}

if (function_exists('XH_wantsPluginAdministration')
        && XH_wantsPluginAdministration('expandcontract')
        || isset($expandcontract) && $expandcontract == 'true')
{
    $o .= print_plugin_admin('on');
//**************************************************************************
//**************************************************************************
    $ec_pluginName = 'Expandcontract_XH';
    $ec_pluginVersion = '1.1';
    $ec_copyright = '2022';
    $ec_cmsVersionArray = array('1.7.3', 'and higher');
    $ec_phpVersion = '7.4';
//**************************************************************************
//**************************************************************************
    // display only on start page of plugin and under info
    if (!$admin || $admin == 'plugin_main') {
        $o .= '<img class="ec_admin_logo" alt="Logo ' . $ec_pluginName . '" src="'
            . $pth['folder']['plugins'] . 'expandcontract/img/expandcontract-logo.svg">'
            . "\n";
        $o .= '<h1>' . $ec_pluginName . '</h1>'
            . '<p>Version ' . $ec_pluginVersion
            . '<br>&copy; 2014 - 2016 <a href="http://svasti.de" target="_blank">svasti</a>'
            . '<br>&copy; ' . $ec_copyright . ' <a href="https://www.cmsimple-xh.org/" target="_blank">The CMSimple_XH Community</a>
            . <br>Licence: <a target="_blank" href="https://www.gnu.org/licenses/gpl-3.0.en.html">GPLv3</a></p>'
            . '<p>'. $plugin_tx['expandcontract']['plugin_explanation']. '</p>';

//**************************************************************************
// System check
//**************************************************************************
        $o .= '<h2>System Check</h2>' . "\n";
// CMSimple_XH Version
        $ec_cmsVersionTmp = CMSIMPLE_XH_VERSION;
        $ec_cmsVersionTmp = str_replace(array('CMSimple_XH '), '', $ec_cmsVersionTmp);
        if (version_compare($ec_cmsVersionTmp, $ec_cmsVersionArray[0], '<'))
        {
            $o .= '<p class="xh_warning">'
                . CMSIMPLE_XH_VERSION
                . ' &#x2192 I hope it is still supported. It was designed for '
                . $ec_cmsVersionArray[0]
                . ' - '
                . end($ec_cmsVersionArray)
                . '.</p>'
                . "\n";
        }
        else
        {
            $o .= '<p class="xh_success">'
                . CMSIMPLE_XH_VERSION
                . ' &#x2192 supported</p>'
                . "\n";
        }
// PHP Version
        if (version_compare(phpversion(), $ec_phpVersion, '<')) {
            $o .= '<p class="xh_fail">PHP: '
                . phpversion()
                . ' &#x2192 not supported</p>'
                . "\n";
        } else {
            $o .= '<p class="xh_success">PHP: '
                . phpversion()
                . ' &#x2192 supported</p>'
                . "\n";
        }
// write permissions
        $ec_fileNameArray = array($pth['file']['plugin_stylesheet'],
                                  $pth['file']['plugin_config'],
                                  $pth['folder']['plugin_languages'],
                                  $pth['file']['plugin_language']);
        foreach ($ec_fileNameArray as $ec_fileName) {
            if (is_writable($ec_fileName)) {
                $o .= '<p class="xh_success">'
                    . $ec_fileName
                    . ' &#x2192 writable</p>'
                    . "\n";
            } else {
                $o .= '<p class="xh_fail">'
                    . $ec_fileName
                    . ' &#x2192 not writable</p>'
                    . "\n";
            }
        }
// checks access protection
        if (XH_isAccessProtected($pth['file']['plugin_config']) === true) {
            $o .= '<p class="xh_success"><a target="_blank" href="'
                . $pth['file']['plugin_config']
                . '">'
                . $pth['file']['plugin_config']
                . '</a> &#x2192 protected</p>'
                . "\n";
        } else {
            $o .= '<p class="xh_warning"><a target="_blank" href="'
                . $pth['file']['plugin_config']
                . '">'
                . $pth['file']['plugin_config']
                . '</a> &#x2192 protected ?</p>'
                . "\n";
        }
        $o .= '</div>' . "\n";
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
