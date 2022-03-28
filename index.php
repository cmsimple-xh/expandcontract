<?php

/**
 * Front-end of Expandcontract_XH.
 *
 * @category  CMSimple_XH Plugin
 * @author    svasti <svasti@svasti.de>
 * @copyright 2014-16 by svasti < http://svasti.de >
 * @copyright 2022 The CMSimple_XH Community < https://www.cmsimple-xh.org/ >
 */

/**
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


//function expand($link='',$linktext='',$closebutton='',$limitheight='',$usebuttons='',$firstopen='')
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
            //$tmp_params[strtolower(trim($cKey))] = trim($cValue);
            $pKey = str_replace('-', '', $pKey);
            $pKey = ec_cleanTinySpaces($pKey);
            $pValue = ec_cleanTinySpaces($pValue);
            if ($pKey != '' && $pValue != '') {
                //$pValue = ec_lowercase('on|off',$pValue);
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
        $tmp_params['maxheight'] = ec_lowercase('px|em|rem|vh|off', $tmp_params['maxheight']);
        if (ec_validateCSS('px|em|rem|\%|vh', $tmp_params['maxheight'], 'withPoint')) {
            $limitheight = $tmp_params['maxheight'];
        }
    }
    if ($ec_pcf['expand-content_max-height'] != ''
    && $limitheight === false) {
        $ec_pcf['expand-content_max-height'] = ec_lowercase('px|em|rem|vh|off', $ec_pcf['expand-content_max-height']);
        if (ec_validateCSS('px|em|rem|\%|vh', $ec_pcf['expand-content_max-height'], 'withPoint')) {
            $limitheight = $ec_pcf['expand-content_max-height'];
        }
    }
    if ($limitheight == '0'
    || $limitheight == 'off') {
        $limitheight = false;
    }
/*
    $contentpadding = 0;
    if (array_key_exists('contentpadding', $tmp_params)) {
        $tmp_params['contentpadding'] = ec_lowercase('px|off', $tmp_params['contentpadding']);
        if (ec_validateCSS('px', $tmp_params['contentpadding'])) {
            $contentpadding = $tmp_params['contentpadding'];
        }
    }
    if ($ec_pcf['expand-content_padding'] != ''
    && $contentpadding === 0) {
        var_dump($ec_pcf['expand-content_padding']);
        $ec_pcf['expand-content_padding'] = ec_lowercase('px|off', $ec_pcf['expand-content_padding']);
        var_dump($ec_pcf['expand-content_padding']);
        if (ec_validateCSS('px', $ec_pcf['expand-content_padding'])) {
            $contentpadding = $ec_pcf['expand-content_padding'];
        }
    }
    if ($contentpadding == '0'
    || strtolower($contentpadding) == 'off') {
        $contentpadding = 0;
    }
 */
    $contentpadding = 0;
    $temp = '';
    if (array_key_exists('contentpadding', $tmp_params)) {
        $temp = ec_cleanTinySpaces($tmp_params['contentpadding']);
    } elseif ($ec_pcf['expand-content_padding'] != '') {
        //$temp = ec_cleanTinySpaces($ec_pcf['expand-content_padding']);    //in der config dürften nur normale Leerzeichen auftauchen, trim ist sicher performanter
        $temp = trim($ec_pcf['expand-content_padding']);
        //$temp = preg_replace('#\s+#u', ' ', $temp);                         // '10px    20px     30px    40px' aus der config korrigieren
    }

    if ($temp !== '') {
        $temp = ec_lowercase('px|off', $temp);
        if ($temp == '0'
        || $temp == 'off') {
            $paddings[] = '0';                                              // 0 und off möglich
        } else {
            //$t = explode(' ', $temp);
            $t = preg_split('#\s+#u', $temp, -1, PREG_SPLIT_NO_EMPTY);
            $paddings = array();

            $fe_count = 0;                                                  // Angaben wie '10px 20px 30px 40px 50px 60px' abfangen
            foreach ($t as $padding) {
                $fe_count++;
                if ($fe_count === 5) {
                    break;
                }
                //$padding = ec_cleanTinySpaces($padding);
                if ($padding == '0') {
                    $paddings[] = $padding;
                } else {
                    //$padding = ec_lowercase('px', $padding);              // ein Durchlauf im ganzen ist vielleicht performanter
                    //if (!preg_match('/^[0-9]+(px)$/', $padding)) {
                    if (!ec_validateCSS('px', $padding)) {
                        return XH_message('fail',
                                'There is an error in the definition of "Content-Padding"'); //i18n
                    }
                    //Es sind jetzt nur noch Zahlenwerte 
                    //mit nachgestelltem "px", sowie "0" enthalten
                    $paddings[] = $padding;
                }
            }
            //Kann das überhaupt vorkommen?                                 // jetzt ja -> $fe_count
            if (count($t) !== count($paddings)) {
                return XH_message('fail',
                        'There is an error in the definition of "Content-Padding"'); //i18n;
            }
            //assert(count($t) === count($paddings));
            $contentpadding = implode(' ', $paddings);
        }
    }
    
    // Berechnung Höhen-Offset
    $heightoffset = 0;
    if (isset($paddings)) {
        $elements = count($paddings);
        switch ($elements) {
            case 1:
                $heightoffset = intval($paddings[0]) * 2;
                break;
            case 2:
                $heightoffset = intval($paddings[0]) * 2;
                break;
            case 3:
                $heightoffset = intval($paddings[0]) + intval($paddings[2]);
                break;
            case 4:
                $heightoffset = intval($paddings[0]) + intval($paddings[2]);
                break;
        }
    }

    /*
    if (array_key_exists('contentpadding', $tmp_params)) {
        $tmp_params['contentpadding'] = ec_lowercase('px|off', $tmp_params['contentpadding']);
        if (ec_validateCSS('px', $tmp_params['contentpadding'])) {
            $contentpadding = $tmp_params['contentpadding'];
        }
    }
    if ($ec_pcf['expand-content_padding'] != ''
    && $contentpadding === 0) {
        $ec_pcf['expand-content_padding'] = ec_lowercase('px|off', $ec_pcf['expand-content_padding']);
        if (ec_validateCSS('px', $ec_pcf['expand-content_padding'])) {
            $contentpadding = $ec_pcf['expand-content_padding'];
        }
    }
    if ($contentpadding == '0'
    || strtolower($contentpadding) == 'off') {
        $contentpadding = 0;
    }
    */
    
    $closebutton = ec_validateOnOff($tmp_params, 'showclose', 'expand-content_show_close_button');
    $autoclose = ec_validateOnOff($tmp_params, 'autoclose', 'expand-content_auto_close');
    $usebuttons = ec_validateOnOff($tmp_params, 'showinline', 'use_inline_buttons');
    $firstopen = ec_validateOnOff($tmp_params, 'firstopen', 'expand-content_first_open');
    $targetid = 'ecId' . $count;
    
    // Fuer CMS-Suche alle Container geschlossen lassen
    if (isset($_GET['search'])) {
        $firstopen = $autoclose = false;
    }

    /*
    $options = array(
        'containerId' => $targetid,
        'contentPadding' => $contentpadding,
        'autoClose' => (bool) $autoclose,
        'firstOpen' => (bool) $firstopen
        );
    $options = json_encode($options);
    */
    
    $options = 
            'data-contentpadding="' . $contentpadding . '" ' .
            'data-heightoffset="' . $heightoffset . '" ' .
            'data-autoclose="' . $autoclose . '" ' .
            'data-firstopen="' . $firstopen . '"'
            ;

    $o = $t = '';
    $pageNrArray = array();

    // $unikId only to demonstrate different settings on the same page
    //$unikId = $closebutton . $limitheight . $usebuttons;

    if ($link) {
        if (strpos($link, ',')) {
            $link = str_replace('\,', '&#44;', $link);
            $linklist = explode(',', $link);
            foreach ($linklist as $singlelink) {
                //$singlelink = trim($singlelink);
                $singlelink = str_replace('&#44;', ',', $singlelink);
                $singlelink = ec_cleanTinySpaces($singlelink);
                if ($singlelink != '') {
                    $pageNr = array_search($singlelink, $h);
                    if ($pageNr === false) {
                        return XH_message('fail', 'Page "%s" not found!', $singlelink); //i18n
                    }
                    $pageNrArray[] = $pageNr;
                }
            }
            //$link = false; // Fix Variante  #17
        } else {
            $link = ec_cleanTinySpaces($link);
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
    //Fix "Variante 3" #17
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
                $singlelinktext = ec_cleanTinySpaces($singlelinktext);
                if ($singlelinktext != '') {
                    $headlineArray[] = $singlelinktext;
                }
            }
        } else {
            $headlineArray[] = $linktext;
        }
    }

    if (!$link) $o .= '
<div class="expand_area" id="' . $targetid . '" '. $options .'>';
    if ($usebuttons) {
        $o .= '
<div class="expand_linkArea">';
}
    $i = 1;
    foreach ($pageNrArray as $value) {
        
        $js = '" class="linkBtn" id="deeplink'.$i.$uniqueId.'" onclick="expandcontract(\'popup'.$i.$uniqueId.'\'); return false;';
        $expContent = str_replace('#CMSimple hide#', '', $c[$value]);

        if ($usebuttons) { 
            $o .= '
<form method="post" class="expand_button" action="?' . $u[$value] . $js . '">
<input type="submit" value="';
            $o .= !empty($headlineArray[$i]) ? $headlineArray[$i] : $h[$value];
            $o .=  '">
</form>';
        } else {
            if (!$link) $t .= '
<p class="expand_link">';
            $t .= a($value,$js);
            $t .= !empty($headlineArray[$i]) ? $headlineArray[$i] : $h[$value];
            $t .= '</a>';
            if (!$link) $t .= '</p>';
        }
        $t .= '
<div id="popup'.$i.$uniqueId.'" class="expand_content" style="max-height: 0px;">';
        $linkU =  $_SERVER['REQUEST_URI'];
        $t .= '
<div class="deepLink"><a href="' . $linkU . '#popup' . $i.$uniqueId . '">&#x1f517;</a></div>';
        if ($limitheight) $t .= '
<div style="height:'.$limitheight.';overflow-y:auto;padding-right:1em;">';
        $t .= $expContent;
        $t .= '<div style="clear:both"></div>';
        if ($limitheight) $t .= '
</div>';
        if ($closebutton) {
            $t .= '
<div class="ecClose">
<button class="ecCloseButton" type="button" onclick="expandcontract(\'popup' . $i.$uniqueId . '\'); return false;">' . $plugin_tx['expandcontract']['close'] . '</button>
</div>';
        }
        $t .= '
</div>';
    $i++;
    }
    if ($usebuttons) {
        $o .= '
</div>';
    }

    if ($s >= 0) {
        $nested = true;
        $o .= evaluate_scripting($t);
        $nested = false;
    }
    if (!$link) $o .= '
</div>';

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

// clean TinyMCE multible spaces
// at the beginning and at the end from $data
// from WYSIWYG-Mode
function ec_cleanTinySpaces($data = '') {

    return $data = preg_replace('/^\s+|\s+$/u', '', $data);
}

// Checks for valid CSS specifications, off or 0
// $units => i.e. 'px|em|rem|\%|vh'
// $data => string
// $dec => if set, then numbers with dot separation are allowed
function ec_validateCSS($units = '', $data = '', $dec = '') {

    if ($dec == '') {
        //$filter = '([1-9]{1})([\d]{1,3})?';
        $filter = '[0-9]+';
    } else {
        $filter = '([\d]{1,4})(\.[\d]{1,4})?';
    }

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

    //$words = trim($words);
    $wordsArray = explode('|', $words);
    foreach($wordsArray as $tmp) {
        //$tmp = trim($tmp);
        $data = str_ireplace($tmp, $tmp, $data);
    }
    return $data;
}
