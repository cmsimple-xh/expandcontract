<?php

/**
 * Front-end of Expandcontract_XH.
 *
 * @category  CMSimple_XH Plugin
 * @author    svasti <svasti@svasti.de>
 * @copyright 2014-16 by svasti < http://svasti.de >
 * @copyright 2022 The CMSimple_XH Community < https://www.cmsimple-xh.org/ >
 * @version   1.0 - 2022.03.31
 */

/**
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

function expand()
{
    global $s, $cl, $l, $cf, $h, $hjs, $c, $u, $plugin_cf, $plugin_tx, $pth, $bjs;
    $ec_pcf = $plugin_cf['expandcontract'];
    static $count = 1;
    static $nested = false;
    $uniqueId = '_ec' . $count;

    if ($nested) return XH_message('warning', 'Nested calls of ExpandContract are not possible!'); //i18n
    if ($s < 0) return;

    $params = func_get_args();

    $tmp_params = array();
    foreach($params as $param) {
        if (strpos($param, '=') !== false) {
            list ($pKey, $pValue) = explode('=', $param, 2);
            $pKey = str_replace('-', '', $pKey);
            $pKey = ec_removeSpaces($pKey);
            $pValue = ec_removeSpaces($pValue);
            if ($pKey != '' && $pValue != '') {
                $tmp_params[strtolower($pKey)] = $pValue;
            }
        }
    }

    if (array_key_exists('pages', $tmp_params)) {
        $link = $tmp_params['pages'];
    } else {
        $link = false;
    }
    if (array_key_exists('headlines', $tmp_params)) {
        $linktext = $tmp_params['headlines'];
    } else {
        $linktext = false;
    }

    $limitheight = false;
    if (array_key_exists('maxheight', $tmp_params)) {
        $tmp_params['maxheight'] = ec_lowercase('px|em|rem|vh|off', 
                $tmp_params['maxheight']);
        if (ec_validateCSS($tmp_params['maxheight'])) {
            $limitheight = $tmp_params['maxheight'];
        }
    }
    if ($ec_pcf['expand-content_max-height'] != '' 
            && $limitheight === false) {
        $ec_pcf['expand-content_max-height'] = ec_lowercase('px|em|rem|vh|off', 
                $ec_pcf['expand-content_max-height']);
        if (ec_validateCSS($ec_pcf['expand-content_max-height'])) {
            $limitheight = $ec_pcf['expand-content_max-height'];
        }
    }
    if ($limitheight == '0'
    || $limitheight == 'off') {
        $limitheight = false;
    }

    $contentpadding = 0;
    $temp = '';
    if (array_key_exists('contentpadding', $tmp_params)) {
        $temp = ec_removeSpaces($tmp_params['contentpadding']);
    } elseif ($ec_pcf['expand-content_padding'] != '') {
        $temp = trim($ec_pcf['expand-content_padding']);
    }

    if ($temp !== '') {
        $temp = ec_lowercase('px|off', $temp);
        if ($temp == '0'
        || $temp == 'off') {
            $paddings[] = '0';
        } else {
            $t = preg_split('#\s+#u', $temp, -1, PREG_SPLIT_NO_EMPTY);
            $paddings = array();

            $fe_count = 0;
            foreach ($t as $padding) {
                $fe_count++;
                if ($fe_count === 5) {
                    break;
                }
                if ($padding == '0') {
                    $paddings[] = $padding;
                } else {
                    if (!ec_validateCSS($padding)) {
                        return XH_message('fail',
                                'There is an error in the definition of "Content-Padding"'); //i18n
                    }
                    $paddings[] = $padding;
                }
            }
            if (count($t) !== count($paddings)) {
                return XH_message('fail',
                        'There is an error in the definition of "Content-Padding"'); //i18n;
            }
            $contentpadding = implode(' ', $paddings);
        }
    }

    $closebutton = ec_validateOnOff($tmp_params, 'showclose', 'expand-content_show_close_button');
    $autoclose = ec_validateOnOff($tmp_params, 'autoclose', 'expand-content_auto_close');
    $usebuttons = ec_validateOnOff($tmp_params, 'showinline', 'use_inline_buttons');
    $firstopen = ec_validateOnOff($tmp_params, 'firstopen', 'expand-content_first_open');
    $targetid = 'ecId' . $count;
    
    // Fuer CMS-Suche alle Container geschlossen lassen
    if (isset($_GET['search'])) {
        $firstopen = $autoclose = false;
    }
    
    $options = 
            'data-autoclose="' . $autoclose . '" ' .
            'data-firstopen="' . $firstopen . '"'
            ;

    $o = $t = '';
    $pageNrArray = array();

    if ($link) {
        if (strpos($link, ',')) {
            $link = str_replace('\,', '&#44;', $link);
            $linklist = explode(',', $link);
            foreach ($linklist as $singlelink) {
                $singlelink = str_replace('&#44;', ',', $singlelink);
                $singlelink = ec_removeSpaces($singlelink);
                if ($singlelink != '') {
                    $pageNr = array_search($singlelink, $h);
                    if ($pageNr === false) {
                        return XH_message('fail', 'Page "%s" not found!', $singlelink); //i18n
                    }
                    $pageNrArray[] = $pageNr;
                }
            }
        } else {
            $link = ec_removeSpaces($link);
            $pageNr = array_search($link, $h);
            if ($pageNr === false || $link == '' ) {
                return XH_message('fail', 'Page "%s" not found!', $link); //i18n
            }
            $pageNrArray[] = $pageNr;
        }
    } else {
        $tl = $l[$s] + 1 + $cf['menu']['levelcatch'];
        for ($i = $s + 1; $i < $cl; $i++) {
            if ($l[$i] <= $l[$s]) {
                break;
            }
            if ($l[$i] <= $tl) {
                if (hide($i)) {
                    $pageNrArray[] = $i;
                }
            }
            if ($l[$i] < $tl) {
                $tl = $l[$i];
            }
        }
    }
    if (count($pageNrArray) > 0) {
        $link = false;
    } else {
        return XH_message('fail', 'No hidden pages found!'); //i18n
    }
    
    $headlineArray = array('headlines');
    if ($linktext) {
        if (strpos($linktext, ',')) {
            $linktext = str_replace('\,', '&#44;', $linktext);
            $linktextlist = explode(',', $linktext);
            foreach ($linktextlist as $singlelinktext) {
                $singlelinktext = str_replace('&#44;', ',', $singlelinktext);
                $singlelinktext = ec_removeSpaces($singlelinktext);
                if ($singlelinktext != '') {
                    $headlineArray[] = $singlelinktext;
                }
            }
        } else {
            $headlineArray[] = $linktext;
        }
    }

    if (!$link) {
        $o .= '<div class="expand_area" id="' . $targetid . '" '. $options .'>';
    }
    if ($usebuttons) {
        $o .= '<div class="expand_linkArea">';
    }
    $nonce = '';
    if (function_exists('sh_cspHeaderNonce')) {
        $nonce = ' nonce="' . sh_cspHeaderNonce() . '"';
    }
    $headStyleContent = '.expand_clear {clear: both;}'
                      . "\n";
    $i = 1;
    foreach ($pageNrArray as $value) {
        $js = '" class="linkBtn" id="deeplink' . $i . $uniqueId . '" ';
        $expContent = str_replace('#CMSimple hide#', '', $c[$value]);
        if ($usebuttons) { 
            $o .= '<form method="post" class="expand_button" action="?'
                    . $u[$value] . $js . '"><input type="submit" value="';
            $o .= !empty($headlineArray[$i]) ? $headlineArray[$i] : $h[$value];
            $o .=  '"></form>';
        } else {
            if (!$link) {
                $t .= '<p class="expand_link" data-popup-id="popup' . $i . $uniqueId . '">';
            }
            $t .= a($value, $js);
            $t .= !empty($headlineArray[$i]) ? $headlineArray[$i] : $h[$value];
            $t .= '</a>';
            if (!$link) {
                $t .= '</p>';
            }
        }
        $t .= '<div id="popup' . $i . $uniqueId . '" class="expand_content">'
            . '<div id="popup' . $i . $uniqueId . '_div_1" class="expand_contentwrap">';
        $headStyleContent .= '#popup' . $i . $uniqueId
                           . ' {max-height: 0px;}' . "\n";
        $headStyleContent .= '#popup' . $i . $uniqueId . '_div_1'
                           . ' {padding: ' . $contentpadding . '}' . "\n";
        $t .= '<div class="deepLink"><a href="#popup' 
            . $i . $uniqueId . '">&#x1f517;</a></div>';
        if ($limitheight) {
            $t .= '<div id="popup' . $i . $uniqueId . '_div_3">';
            $headStyleContent .= '#popup' . $i . $uniqueId . '_div_3'
                           . ' {height:' . $limitheight
                           . '; overflow-y: auto; padding-right: 1em;}'
                           . "\n";
        }
        $t .= $expContent;
        $t .= '<div class="expand_clear"></div>';
        if ($limitheight) {
            $t .= '</div>';
        }
        if ($closebutton) {
            $t .= '<div class="ecClose">'
                    . '<button class="ecCloseButton" type="button">'
                    . $plugin_tx['expandcontract']['close'] 
                    . '</button>'
                    . '</div>';
        }
    $t .= '</div></div>';
    $i++;
    }
    if ($usebuttons) {
        $o .= '</div>';
    }

    if ($s >= 0) {
        $nested = true;
        $o .= evaluate_scripting($t);
        $nested = false;
    }
    if (!$link) $o .= '</div>';

    // Style in den head
    $hjs .= '<style' . $nonce . '>' . "\n"
          . $headStyleContent
          . '</style>' . "\n";
    // JS & CSS nur einmal laden
    if ($count === 1) {
        $expandcontractStyles = $ec_pcf['use_stylesheet'];
        if ($expandcontractStyles != '') {
            $hjs .= '<link rel="stylesheet" href="' . $pth['folder']['plugins'] 
                    . 'expandcontract/css/' 
                    . $expandcontractStyles . '" type="text/css">';
        }
        $jsFile = $pth['folder']['plugins'] . 'expandcontract/expandcontract.js';
        $bjs .= '<script src="' . $jsFile . '"></script>';
        
    }
    $count++;
    return $o;
}

function ec_validateOnOff($args = array(), $param = '', $default = '') {

    global $plugin_cf;
    $ec_pcf = $plugin_cf['expandcontract'];

    if (!array_key_exists($param, $args)) {
        return $ec_pcf[$default];
    } else {
        $args[$param] = ec_lowercase('on|off',$args[$param]);
    }
    
    switch ($args[$param]) {
        case 'on': 
            return true;

        case 'off': 
            return false;

        default:
            return $ec_pcf[$default];
    }
}

// clean spaces at the beginning 
// and at the end from $data
// from WYSIWYG-Mode
function ec_removeSpaces($data = '') {

    return $data = preg_replace('/^\s+|\s+$/u', '', $data);
}

// Checks for valid CSS specifications, off or 0
// $data => string
function ec_validateCSS($data = '') {

    $filter = '([\d]{1,4})(\.[\d]{1,4})?';
    $units = 'px|em|rem|\%|vh';

    if (preg_match('#(^' . $filter . '(' . $units . ')$|^(off)$|^(0)$)#uim', $data)) {
        return true;
    } else {
        return false;
    }
}

// change to lowercase
// $words => i.e. 'px|em|rem|vh|off'
// $data => string
function ec_lowercase($words = '', $data = '') {

    $wordsArray = explode('|', $words);
    foreach($wordsArray as $tmp) {
        $data = str_ireplace($tmp, $tmp, $data);
    }
    return $data;
}
