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

    if ($s < 0) return;

    $params = func_get_args();

    $tmp_params = array();
    foreach($params as $param) {
        list ($cKey, $cValue) = explode('=', $param, 2);
        $tmp_params[strtolower(trim($cKey))] = trim($cValue);
        /*$tmp_params = array_map(function($value) {
            return str_ireplace(array('on', 'off'), array('1', '0'), $value);
        }, $tmp_params);*/
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
    if (array_key_exists('show-close', $tmp_params)) {
        $closebutton = str_ireplace(array('on', 'off'), array('1', false), $tmp_params['show-close']);
    } else {
        $closebutton = $plugin_cf['expandcontract']['expand-content_show_close_button'];
    }
    if (array_key_exists('auto-close', $tmp_params)) {
        $autoclose = str_ireplace(array('on', 'off'), array('1', false), $tmp_params['auto-close']);
    } else {
        $autoclose = $plugin_cf['expandcontract']['expand-content_auto_close'];
    }
    if (array_key_exists('max-height', $tmp_params)) {
        $tmp_params['max-height'] = str_ireplace(array('on', 'off'), array('on', 'off'), $tmp_params['max-height']);
        if ($tmp_params['max-height'] == 'on'
        && $plugin_cf['expandcontract']['expand-content_max-height'] != '') {
            $limitheight = $plugin_cf['expandcontract']['expand-content_max-height'];
        } elseif ($tmp_params['max-height'] == 'off') {
            $limitheight = false;
        } else {
            $limitheight = $tmp_params['max-height'];
        }
    } else {
        $limitheight = $plugin_cf['expandcontract']['expand-content_max-height'];
    }
    if (array_key_exists('show-inline', $tmp_params)) {
        $usebuttons = str_ireplace(array('on', 'off'), array('1', false), $tmp_params['show-inline']);
    } else {
        $usebuttons = $plugin_cf['expandcontract']['use_inline_buttons'];
    }
    if (array_key_exists('firstopen', $tmp_params)) {
        $firstopen = str_ireplace(array('on', 'off'), array('1', false), $tmp_params['firstopen']);
    } else {
        $firstopen = $plugin_cf['expandcontract']['expand-content_first_open'];
    }

    $o = $t = '';
    $pageNrArray = array();

    // $unikId only to demonstrate different settings on the same page
    $unikId = $closebutton . $limitheight . $usebuttons;

    if ($link) {
        if (strpos($link, ',')) {
            $linklist = explode(',', $link);
            foreach ($linklist as $singlelink) {
                $singlelink = trim($singlelink);
                $pageNr = array_search($singlelink, $h);
                if ($pageNr === false) {
                    return XH_message('fail', 'Page "%s" not found!', $singlelink); //i18n
                }
                $pageNrArray[] = $pageNr;
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


    if (!$link) $o .= '
<div class="expand_area">';
    if ($usebuttons) {
        $o .= '
<div class="expand_linkArea">';
}
    foreach ($pageNrArray as $value) {

        $js = '" class="linkBtn" id="deeplink'.$value.$unikId.'" onclick="expandcontract(\'popup'.$value.$unikId.'\'); return false;';

        $expContent = str_replace('#CMSimple hide#', '', $c[$value]);

        if ($usebuttons) {
            $o .= '
<form method="post" class="expand_button" action="?' . $u[$value] . $js . '">
<input type="submit" value="';
            $o .= $linktext? $linktext : $h[$value];
            $o .=  '">
</form>';
        } else {
            if (!$link) $t .= '
<p class="expand_link" id="' . $value . '">';
            $t .= a($value,$js);
            $t .= $linktext? $linktext : $h[$value];
            $t .= '</a>';
            if (!$link) $t .= '</p>';
        }
        $t .= '
<div id="popup'.$value.$unikId.'" class="expand_content" style="max-height: 0px;">';
        $linkU =  $_SERVER['REQUEST_URI'];
        $t .= '
<div class="deepLink"><a href="' . $linkU . '#popup' . $value.$unikId . '" onclick="return false;">&#x1f517;</a></div>';
        if ($limitheight) $t .= '
<div style="height:'.$limitheight.';overflow-y:scroll;">';
        $t .= $expContent;
        $t .= '<div style="clear:both"></div>';
        if ($limitheight) $t .= '
</div>';
        if ($closebutton) {
            $t .= '
<div class="ecClose">
<button type="button" onclick="expandcontract(\'popup' . $value.$unikId . '\'); return false;">' . $plugin_tx['expandcontract']['close'] . '</button>
</div>';
        }
        $t .= '
</div>';
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

    $temp = $plugin_cf['expandcontract']['expand-content_padding'];
    if ($temp != '') {
        $expandcontractPadding = $temp;
    } else {
        $expandcontractPadding = 0;
    }
    $bjs .= '
<script>
function expandcontract(expPage) {
    contentPadding = "' . $expandcontractPadding . '";
    let el = document.getElementById(expPage);
    let elMaxHeight = el.scrollHeight;
    elMaxHeight = parseInt(elMaxHeight) + (parseInt(contentPadding) * 12);
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

    return $o;
}

$expandcontractStyles = $plugin_cf['expandcontract']['use_stylesheet'];
if ($expandcontractStyles != '') {
    $hjs .= '<link rel="stylesheet" href="' . $pth['folder']['plugins'] . 'expandcontract/css/' . $expandcontractStyles . '" type="text/css">
';
}
