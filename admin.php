<?php

/**
 * Back-end Expandcontract_XH.
 * Copyright (c) 2014-16 svasti@svasti.de
 *
 * Last Change: 02.06.2016 18:20:56
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
    if(!isset($plugin_cf['expandcontract']['version'])
        || $plugin_cf['expandcontract']['version'] != EXPANDCONTRACT_VERSION) {
        if($o .= expandcontract_createConfig()) include $pth['folder']['plugins'] . 'expandcontract/config/config.php';
        }
    $o .= print_plugin_admin('on');
    if (!$admin || $admin == 'plugin_main') {
        $o .= '<h4>Expandcontract_XH</h4>'
            . '<p>Version '. $plugin_cf['expandcontract']['version']
            . ' &copy; 2014-16 by <a href="http://svasti.de" target="_blank">svasti</a></p>'
            . '<h5>'.$plugin_tx['expandcontract']['plugin_call'].'</h5>'
            . '<p><b>{{{expand}}}</b> '
            . '<br><i> '. $plugin_tx['expandcontract']['link_hidden_subpages']. '</i>'
            .  '<br>'
            . '<p><b>{{{expand \'page1,page2,page3,page4\'}}}</b> '
            . '<br><i> '. $plugin_tx['expandcontract']['link_multiple_pages']. '</i>'
            .  '<br>'
            . '<p><b>{{{expand \'page X\', \'linktext\'}}}</b> '
            . '<br><i> '. $plugin_tx['expandcontract']['link_single_page']. '</i></p>';
    }
	$o .= plugin_admin_common($action, $admin, $plugin);
}



/**
 * Prepares the creation of config items with default values or pre-existing ones
 */
function expandcontract_createConfig()
{
	global $pth ,$plugin_tx;

    // make sure that the plugin css really gets put into the generated plugincss
    touch($pth['folder']['plugins'] . 'expandcontract/css/stylesheet.css');

    $text = '<?php' . "\n\n"
          . expandcontract_findConfigValue(array(
              'use_inline_buttons;true',
              'auto-close;true',
              'show_headings;true',
              'show_close_button;true',
              'max_height'))
          . '$plugin_cf[\'expandcontract\'][\'version\']="'
          . EXPANDCONTRACT_VERSION . '";' . "\n"
          . "\n" . '?>' . "\n";

    $config = $pth['folder']['plugins'] . 'expandcontract/config/config.php';

    if (!file_put_contents($config, $text)) {
        e('cntwriteto', 'file', $config);
        return false;
    } else {
      // give out notice that updating was successful
      return '<div style="display:block; width:100%; border:1px solid red;'
             . 'margin:2em 0;">'
             . '<h4 style="text-align:center; margin:0; padding:.5em;"> '
             . sprintf($plugin_tx['expandcontract']['text_update_successful'], EXPANDCONTRACT_VERSION)
             . '</h4></div>';
    }
}



/**
 * Checks if old config values exist and creates new config values
 */
function expandcontract_findConfigValue($itemArray)
{
	global $plugin_cf;
    $o = '';

    foreach ($itemArray as $value) {
        list($item, $default, $oldname) = array_pad(explode(';',$value), 3, '');
        $name = $oldname ? $oldname : $item;
        $value = isset($plugin_cf['expandcontract'][$name])
            ? $plugin_cf['expandcontract'][$name]
            : (isset($plugin_cf['expandcontract'][$item])
              ? $plugin_cf['expandcontract'][$item]
              : $default);

        $o .= '$plugin_cf[\'expandcontract\'][\'' . $item . '\']="'
                . $value . '";' . "\n";
    }
    return $o;
}

?>
