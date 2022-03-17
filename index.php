<?php

/**
 * Front-end of Expandcontract_XH.
 *
 * @category  CMSimple_XH
 * @author    svasti <svasti@svasti.de>
 * @copyright 2014-16 by svasti <http://svasti.de>
 */

/**
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


function expand($link='',$linktext='',$withheading='',$closebutton='',$limitheight='',$usebuttons='')
{
    global $s, $cl, $l, $cf, $h, $c, $u, $plugin_cf, $plugin_tx, $bjs;

    if ($s < 0) return;

    $o = $t = '';
    $pageNrArray = array();

    $withheading = $withheading!==''? $withheading : $plugin_cf['expandcontract']['show_headings'];
    $closebutton = $closebutton!==''? $closebutton : $plugin_cf['expandcontract']['show_close_button'];
    $limitheight = $limitheight!==''? $limitheight : $plugin_cf['expandcontract']['max_height'];
    $usebuttons  = $usebuttons!==''?  $usebuttons  : $plugin_cf['expandcontract']['use_inline_buttons'];

    // $unikId only to demonstrate different settings on the same page
    $unikId = $withheading . $closebutton . $limitheight . $usebuttons;

    if ($link) {
        if (strpos($link, ',')) { 
            $linklist = explode(',', $link);
            foreach ($linklist as $singlelink) {
                $pageNrArray[] = array_search($singlelink, $h);
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


    if (!$link) $o .= "\n\n<!-- E X P A N D - C O N T R A C T    S T A R T -->\n"
                    . '<div class="expand_area">' . "\n";

    foreach ($pageNrArray as $value) {

        $js = '" onclick="expandcontract(\'popup'.$value.$unikId.'\'); return false;';

        $content = str_replace('#CMSimple hide#', '', $c[$value]);
        if (!$withheading) {
            $content = preg_replace("/.*<\/h[1-".$cf['menu']['levels']."]>/isU", "", $content);
        }

        if ($usebuttons) {
            $o .= '<form method="post" class="expand_button" action="?'
               .  $u[$value] . $js . '"><input type="submit" value="';
            $o .= $linktext? $linktext : $h[$value];
            $o .=  '"></form>';
        } else {
            if (!$link) $t .= '<p class="expand_link">';
            $t .= a($value,$js);
            $t .= $linktext? $linktext : $h[$value];
            $t .= '</a>';
            if (!$link) $t .= '</p>' . "\n\n";
        }

        $t .= '<div style="display:none;" id="popup'.$value.$unikId.'" class="expand_content">';
        if ($limitheight) $t .= '<div style="height:'.$limitheight.';overflow-y:scroll;">';
        $t .= $content . '<div style="clear:both"></div>';
        if ($limitheight) $t .= '</div>';
        if ($closebutton) {
            $t .= '<button type="submit" onclick="expandcontract(\'popup'.$value.$unikId.'\');">'
               .  $plugin_tx['expandcontract']['close'] .'</button>';
        }
        $t .= '</div>';
    }
    if ($s >= 0) $o .= evaluate_scripting($t);
    if (!$link) $o .= '</div>' . "\n\n<!-- E X P A N D - C O N T R A C T    E N D -->\n\n\n";

    static $firstExpand = true;
    if ($firstExpand) {
        $firstExpand = false;

        $bjs .= '<script type="text/javascript">
              // <![CDATA[
              function expandcontract(page)
              {
                  if (document.getElementById(page).style.display == \'block\') {
                      document.getElementById(page).style.display = \'none\';
                   } else {';
        if ($plugin_cf['expandcontract']['auto-close']) {
            $bjs .= 'var expandlist = document.getElementsByClassName(\'expand_content\');
                     for (index = 0; index < expandlist.length; ++index) {
                           expandlist[index].style.display = \'none\';
                     }';
        }
        $bjs .= '    document.getElementById(page).style.display = \'block\';
                  }
              }
              // ]]>
              </script>';
    }

    return $o;
}
?>
