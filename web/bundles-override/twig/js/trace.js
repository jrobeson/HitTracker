function traceToggle(id, clazz) {
    var el = document.getElementById(id);
    var current = el.style.display;

    if (clazz) {
        var tags = document.getElementsByClassName(clazz);
        for (var i = tags.length - 1; i >= 0 ; i--) {
            tags[i].style.display = 'none';
        }
    }

    el.style.display = current === 'none' ? 'block' : 'none';
}

function traceSwitchIcons(id1, id2) {
    var icon1, icon2, display1, display2;

    icon1 = document.getElementById(id1);
    icon2 = document.getElementById(id2);

    display1 = icon1.style.display;
    display2 = icon2.style.display;

    icon1.style.display = display2;
    icon2.style.display = display1;
}

window.addEventListener('load', function () {
    var els = document.querySelectorAll('a.traces-toggle');
    for (var i = 0;; i++) {
        if (els[i] == undefined) {
            break;
        }
        els[i].onclick = function (e) {
            var traceId = this.classList[1]; // icon-trace-$position-{open|close}
            traceToggle(traceId);
            traceSwitchIcons('icon-' + traceId + '-open', 'icon-' + traceId + '-close');

            return false;
        };
    }
});
