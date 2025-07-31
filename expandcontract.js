
/**
 * Expandcontract_XH Browser-Scripting
 *
 * @category  CMSimple_XH Plugin
 * @author    svasti <svasti@svasti.de>
 * @copyright 2014-16 by svasti < http://svasti.de >
 * @copyright 2022 The CMSimple_XH Community < https://www.cmsimple-xh.org/ >
 * @version   1.0 - 2022.03.31
 */

function expandcontract(expPage, fromHash = false) {

    const el = document.getElementById(expPage);
    const container = el.closest("div.expand_area");
    const containerId = container.id;
    let autoClose = container.dataset.autoclose;

    if (fromHash === true && el.classList.contains("open")) {
        //Mach nichts, wenn der Container schon offen ist
        return;
    }
    
    let elMaxHeight = el.scrollHeight;
    let targetHeight;
    const target = el.getElementsByClassName("ecCloseButton")[0];
    if (typeof target !== "undefined") {
        targetHeight = target.offsetHeight;
    } else {
        targetHeight = 0;
    }
    let deppHeight;
    const depp = el.getElementsByClassName("deepLink")[0];
    if (typeof depp !== "undefined") {
        deppHeight = depp.offsetHeight;
    } else {
        deppHeight = 0;
    }

    elMaxHeight = parseInt(elMaxHeight) + targetHeight + deppHeight;
    if (el.style.getPropertyValue("max-height") !== "0px") {
        el.style.setProperty("max-height", "0px");
        el.classList.remove("open");
        const deepL = expPage.replace("popup", "deeplink");
        document.getElementById(deepL).classList.remove("current");
    } else {
        if (autoClose) {
            var expandlist = document.getElementById(containerId)
                    .getElementsByClassName("expand_content");
            for (let index = 0; index < expandlist.length; ++index) {
                expandlist[index].style.setProperty("max-height", "0px");
                expandlist[index].classList.remove("open");
            }
            var btnlist = document.getElementById(containerId)
                    .getElementsByClassName("current");
            for (let index = 0; index < btnlist.length; ++index) {
                btnlist[index].classList.remove("current");
            }
        }

        el.style.setProperty("max-height", elMaxHeight + "px");
        el.classList.add("open");
        const deepL = expPage.replace("popup", "deeplink");
        document.getElementById(deepL).classList.add("current");
        //el.scrollIntoView({block: "center", behavior: "smooth"});
    }
}

// CMS-Suche
function ec_showSearchResults() {
    let containers = document.getElementsByClassName("expand_content");
    for (let index = 0; index < containers.length; ++index) {
        if (containers[index].getElementsByClassName("xh_find").length) {
            if (!containers[index].classList.contains("open")) {
                expandcontract(containers[index].id);
            }
        }
    }
}

// Firstopen 
function ec_openFirst() {
    let containers = document.getElementsByClassName("expand_area");
    for (let index = 0; index < containers.length; ++index) {
        const itemId = containers[index].id;
        if (document.getElementById(itemId).dataset.firstopen) {
            const first = document.getElementById(itemId).
                    getElementsByClassName("expand_content")[0];
            if (!first.classList.contains("open")) {
                expandcontract(first.id);
            }
        }
    }
}

// Deeplink öffnet den Expand-Content
function ec_openFromHash() {
    var hash = window.location.hash;
    hash = hash.replace("#", "");
    if (hash.length && hash.substring(0, 5) === "popup" 
            && document.getElementById(hash) !== null) {
        window.onload = function () {
            expandcontract(hash, true);
        }
    }
}
ec_openFirst();
ec_openFromHash();
ec_showSearchResults();
