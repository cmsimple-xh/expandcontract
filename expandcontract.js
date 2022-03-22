
function expandcontract(expPage, options) {
    
    /*
    console.log(expPage);
    console.log(options.containerId);
    console.log(options.contentPadding);
    console.log(options.autoClose);
    console.log(options.firstOpen);
    */

    let id = options.containerId;
    let contentPadding = options.contentPadding;
    let autoClose = options.autoClose;
    //let firstOpen = options.firstOpen;
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
    if (document.getElementById(expPage).style.getPropertyValue("max-height") !== "0px") {
        document.getElementById(expPage).style.setProperty("max-height", "0px");
        document.getElementById(expPage).style.setProperty("padding", "0px");
        document.getElementById(expPage).classList.remove("open");
        deepL = expPage.replace("popup", "deeplink");
        document.getElementById(deepL).classList.remove("current");
    } else {
        if (autoClose) {
            var expandlist = document.getElementById(id).getElementsByClassName("expand_content");
            //var expandlist = document.getElementsByClassName("expand_content");
            for (index = 0; index < expandlist.length; ++index) {
                expandlist[index].style.setProperty("max-height", "0px");
                expandlist[index].style.setProperty("padding", "0px");
                expandlist[index].classList.remove("open");
            }
            var btnlist = document.getElementById(id).getElementsByClassName("current");
            //var btnlist = document.getElementsByClassName("current");
            for (index = 0; index < btnlist.length; ++index) {
                btnlist[index].classList.remove("current");
            }
        }

        document.getElementById(expPage).style.setProperty("max-height", elMaxHeight + "px");
        document.getElementById(expPage).style.setProperty("padding", contentPadding);
        document.getElementById(expPage).classList.add("open");
        deepL = expPage.replace("popup", "deeplink");
        document.getElementById(deepL).classList.add("current");
        //document.getElementById(expPage).scrollIntoView({block: "center", behavior: "smooth"});
    }
}

/*
 if ($firstopen) {
 // öffnet den ersten Expand-Content
 area = document.getElementsByClassName("expand_area");
 if (area.length) {
 list = document.getElementsByClassName("expand_area")[0];
 first = list.getElementsByClassName("expand_content")[0].id;
 expandcontract(first);
 }
 }
 
 // Deeplink öffnet den Expand-Content
 var hash = window.location.hash;
 hash = hash.replace("#", "");
 if (hash.length && hash.substring(0, 5) == "popup" && document.getElementById(hash) !== null) {
 expandcontract(hash);
 //document.getElementById(hash).scrollIntoView({ block: "start",  behavior: "smooth" });
 }
 */
