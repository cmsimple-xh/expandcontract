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
    global $s, $cl, $l, $cf, $h, $c, $u, $plugin_cf, $plugin_tx, $bjs;
    static $count = 1;
    $uniqueId = '_ec' . $count;

    if ($s < 0) return;

    $params = func_get_args();

    $tmp_params = array();
    foreach($params as $param) {
        if (strpos($param, '=') !== false) {
            list ($pKey, $pValue) = explode('=', $param, 2);
            //$tmp_params[strtolower(trim($cKey))] = trim($cValue);
            $pKey = ec_cts($pKey);
            $pValue = ec_cts($pValue);
            if ($pKey != '' && $pValue != '') {
                $pValue = str_ireplace(array('on', 'off'),
                                       array('on', 'off'),
                                       $pValue);
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

    if (array_key_exists('max-height', $tmp_params)) {
        if (preg_match('#^(([0-9]{1,4}(\.[0-9]{1,4})?(px|em|rem|\%|vh))|off|0)$#i', $tmp_params['max-height'])) {
            if ($tmp_params['max-height'] == 'off'
            || $tmp_params['max-height'] == '0') {
                $limitheight = false;
            } else {
                $limitheight = $tmp_params['max-height'];
            }
        } else {
            $limitheight = $plugin_cf['expandcontract']['expand-content_max-height'];
        }
    } else {
        $limitheight = $plugin_cf['expandcontract']['expand-content_max-height'];
    }

    $contentpadding = 0;
    if (array_key_exists('content-padding', $tmp_params)) {
        $contentpadding = $tmp_params['content-padding'];
    } elseif ($plugin_cf['expandcontract']['expand-content_padding'] != '') {
        $contentpadding = $plugin_cf['expandcontract']['expand-content_padding'];
    }

    $closebutton = ec_validateOnOff($tmp_params, 'show-close', 'expand-content_show_close_button');
    $autoclose = ec_validateOnOff($tmp_params, 'auto-close', 'expand-content_auto_close');
    $usebuttons = ec_validateOnOff($tmp_params, 'show-inline', 'use_inline_buttons');
    $firstopen = ec_validateOnOff($tmp_params, 'firstopen', 'expand-content_first_open');

    $o = $t = '';
    $pageNrArray = array();

    // $unikId only to demonstrate different settings on the same page
    //$unikId = $closebutton . $limitheight . $usebuttons;

    if ($link) {
        if (strpos($link, ',')) {
            $linklist = explode(',', $link);
            foreach ($linklist as $singlelink) {
                //$singlelink = trim($singlelink);
                $singlelink = ec_cts($singlelink);
                if ($singlelink != '') {
                    $pageNr = array_search($singlelink, $h);
                    if ($pageNr === false) {
                        return XH_message('fail', 'Page "%s" not found!', $singlelink); //i18n
                    }
                    $pageNrArray[] = $pageNr;
                }
            }
            $link = false;
        } else {
            $pageNrArray[] = array_search($link, $h);
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

    $headlineArray = array('headlines');
    if ($linktext) {
        if (strpos($linktext, ',')) {
            $linktextlist = explode(',', $linktext);
            foreach ($linktextlist as $singlelinktext) {
                $singlelinktext = ec_cts($singlelinktext);
                if ($singlelinktext != '') {
                    $headlineArray[] = $singlelinktext;
                }
            }
        } else {
            $headlineArray[] = $linktext;
        }
    }

    if (!$link) $o .= '
<div class="expand_area">';
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
<p class="expand_link" id="ecId' . $i . '">';
            $t .= a($value,$js);
            $t .= !empty($headlineArray[$i]) ? $headlineArray[$i] : $h[$value];
            $t .= '</a>';
            if (!$link) $t .= '</p>';
        }
        $t .= '
<div id="popup'.$i.$uniqueId.'" class="expand_content" style="max-height: 0px;">';
        $linkU =  $_SERVER['REQUEST_URI'];
        $t .= '
<div class="deepLink"><a href="' . $linkU . '#popup' . $i.$uniqueId . '" onclick="return false;">&#x1f517;</a></div>';
        if ($limitheight) $t .= '
<div style="height:'.$limitheight.';overflow-y:auto;">';
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

    if ($s >= 0) $o .= evaluate_scripting($t);
    if (!$link) $o .= '
</div>';

    static $firstExpand = true;
    if ($firstExpand) {
        $firstExpand = false;

    /*
    $temp = $plugin_cf['expandcontract']['expand-content_padding'];
    if ($temp != '') {
        $expandcontractPadding = $temp;
    } else {
        $expandcontractPadding = 0;
    }
     */
    $bjs .= '
<script>
function expandcontract(expPage) {
    contentPadding = "' . $contentpadding . '";
    let el = document.getElementById(expPage);
    let elMaxHeight = el.scrollHeight;
    target = el.getElementsByClassName("ecCloseButton")[0];
    if (typeof target !== "undefined") {
        targetHeight = target.offsetHeight;
    } else {
        targetHeight = 0;
    }
    depp = el.getElementsByClassName("deepLink")[0];
    if (typeof depp !== "undefined") {
        deppHeight = depp.offsetHeight;
    } else {
        deppHeight = 0;
    }
    elMaxHeight = parseInt(elMaxHeight) + (parseInt(contentPadding) * 2) + targetHeight + deppHeight;
    if (document.getElementById(expPage).style.getPropertyValue("max-height") != "0px") {
        document.getElementById(expPage).style.setProperty("max-height", "0px");
        document.getElementById(expPage).style.setProperty("padding", "0px");
        document.getElementById(expPage).classList.remove("open");
        deepL = expPage.replace("popup","deeplink");
        document.getElementById(deepL).classList.remove("current");
    } else {';
    if ($autoclose) {
        $bjs .= '
        var expandlist = document.getElementsByClassName("expand_content");
        for (index = 0; index < expandlist.length; ++index) {
            expandlist[index].style.setProperty("max-height", "0px");
            expandlist[index].style.setProperty("padding", "0px");
            expandlist[index].classList.remove("open");
        }
        var btnlist = document.getElementsByClassName("current");
        for (index = 0; index < btnlist.length; ++index) {
            btnlist[index].classList.remove("current");
        }';
    }
    $bjs .= '
        document.getElementById(expPage).style.setProperty("max-height", elMaxHeight +"px");
        document.getElementById(expPage).style.setProperty("padding", contentPadding);
        document.getElementById(expPage).classList.add("open");
        deepL = expPage.replace("popup","deeplink");
        document.getElementById(deepL).classList.add("current");
        //document.getElementById(expPage).scrollIntoView({block: "center", behavior: "smooth"});
    }
}';

    if ($firstopen) {
        $bjs .= '
// öffnet den ersten Expand-Content
area = document.getElementsByClassName("expand_area");
if (area.length) {
    list = document.getElementsByClassName("expand_area")[0];
    first = list.getElementsByClassName("expand_content")[0].id;
    expandcontract(first);
}';
    }

    $bjs .= '
// Deeplink öffnet den Expand-Content
var hash = window.location.hash;
hash = hash.replace("#","");
if (hash.length && hash.substring(0, 5) == "popup" && document.getElementById(hash) !== null) {
    expandcontract(hash);
    //document.getElementById(hash).scrollIntoView({ block: "start",  behavior: "smooth" });
}
</script>';
    }

    $count++;
    return $o;
}

$expandcontractStyles = $plugin_cf['expandcontract']['use_stylesheet'];
if ($expandcontractStyles != '') {
    $hjs .= '<link rel="stylesheet" href="' . $pth['folder']['plugins'] 
        . 'expandcontract/css/' . $expandcontractStyles . '" type="text/css">';
}

function ec_validateOnOff($args = array(), $param = '', $default = '') {

    global $plugin_cf;

    if (!array_key_exists($param, $args)) {
        return $plugin_cf['expandcontract'][$default];
    }
    
    switch ($args[$param]) {
        case 'on': 
            return true;

        case 'off': 
            return false;

        default:
            return $plugin_cf['expandcontract'][$default];
    }
}

// clean TinyMCE multible spaces
// at the beginning and at the end from $data
// from WYSIWYG-Mode
function ec_cts($data = '') {

    return $data = preg_replace('/^\s+|\s+$/u', '', $data);
}
