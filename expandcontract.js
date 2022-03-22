
function expandcontract(expPage, fromHash = false) {

    const el = document.getElementById(expPage);
    const container = el.closest("div.expand_area");
    const containerId = container.id;

    let contentPadding = container.dataset.contentpadding;
    let autoClose = container.dataset.autoclose;
    let firstOpen = container.dataset.firstopen;

    if (fromHash === true && el.classList.contains("open")) {
        //Mach nichts, wenn der Container schon offen ist
        return;
    }
    
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
    if (el.style.getPropertyValue("max-height") !== "0px") {
        el.style.setProperty("max-height", "0px");
        el.style.setProperty("padding", "0px");
        el.classList.remove("open");
        deepL = expPage.replace("popup", "deeplink");
        document.getElementById(deepL).classList.remove("current");
    } else {
        if (autoClose) {
            var expandlist = document.getElementById(containerId).getElementsByClassName("expand_content");
            //var expandlist = document.getElementsByClassName("expand_content");
            for (index = 0; index < expandlist.length; ++index) {
                expandlist[index].style.setProperty("max-height", "0px");
                expandlist[index].style.setProperty("padding", "0px");
                expandlist[index].classList.remove("open");
            }
            var btnlist = document.getElementById(containerId).getElementsByClassName("current");
            //var btnlist = document.getElementsByClassName("current");
            for (index = 0; index < btnlist.length; ++index) {
                btnlist[index].classList.remove("current");
            }
        }

        el.style.setProperty("max-height", elMaxHeight + "px");
        el.style.setProperty("padding", contentPadding);
        el.classList.add("open");
        deepL = expPage.replace("popup", "deeplink");
        document.getElementById(deepL).classList.add("current");
        //el.scrollIntoView({block: "center", behavior: "smooth"});
    }
}

// Firstopen 
function ec_openFirst() {
    let containers = document.getElementsByClassName("expand_area");
    for (index = 0; index < containers.length; ++index) {
        itemId = containers[index].id;
        if (document.getElementById(itemId).dataset.firstopen) {
            //console.log(itemId);
            //console.log(document.getElementById(itemId).dataset.firstopen);
            first = document.getElementById(itemId).getElementsByClassName("expand_content")[0];
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
    if (hash.length && hash.substring(0, 5) === "popup" && document.getElementById(hash) !== null) {
        expandcontract(hash, true);
        //document.getElementById(hash).scrollIntoView({ block: "start",  behavior: "smooth" });
    }
}

ec_openFirst();
ec_openFromHash();
