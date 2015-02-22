window.addEventListener('load', function() {
    var sfwdt = document.getElementsByClassName('sf-toolbar')[0];
    var toolbarConfig = JSON.parse(sfwdt.getAttribute('data-sftoolbar-config'));
    var token = toolbarConfig['token'];
    var toolBarPosition = toolbarConfig['position'];
    var wdtUrl = toolbarConfig['wdtUrl'];
    var profilerUrl = toolbarConfig['profilerUrl'];

    if (toolBarPosition == 'top') {
        document.body.insertBefore(
            document.body.removeChild(sfwdt),
            document.body.firstChild
        );
    }

    Sfjs.load(
        'sfwdt' + token,
        wdtUrl,
        function (xhr, el) {
            el.style.display = -1 !== xhr.responseText.indexOf('sf-toolbarreset') ? 'block' : 'none';

            if (el.style.display == 'none') {
                return;
            }

            if (Sfjs.getPreference('toolbar/displayState') == 'none') {
                document.getElementsByClassName('sf-toolbarreset')[0].style.display = 'none';
                document.getElementsByClassName('sf-toolbarclearer')[0].style.display = 'none';
                document.getElementsByClassName('sf-minitoolbar')[0].style.display = 'block';
            } else {
                document.getElementsByClassName('sf-toolbarreset')[0].style.display = 'block';
                document.getElementsByClassName('sf-toolbarclearer')[0].style.display = 'block';
                document.getElementsByClassName('sf-minitoolbar')[0].style.display = 'none';
            }

            Sfjs.renderAjaxRequests();

            /* Handle toolbar-info position */
            var toolbarBlocks = document.getElementsByClassName('sf-toolbar-block');
            for (var i = 0; i < toolbarBlocks.length; i += 1) {
                toolbarBlocks[i].onmouseover = function () {
                    var toolbarInfo = this.getElementsByClassName('sf-toolbar-info')[0];
                    var pageWidth = document.body.clientWidth;
                    var elementWidth = toolbarInfo.offsetWidth;
                    var leftValue = (elementWidth + this.offsetLeft) - pageWidth;
                    var rightValue = (elementWidth + (pageWidth - this.offsetLeft)) - pageWidth;

                    /* Reset right and left value, useful on window resize */
                    toolbarInfo.style.right = '';
                    toolbarInfo.style.left = '';

                    if (leftValue > 0 && rightValue > 0) {
                        toolbarInfo.style.right = (rightValue * -1) + 'px';
                    } else if (leftValue < 0) {
                        toolbarInfo.style.left = 0;
                    } else {
                        toolbarInfo.style.right = '-1px';
                    }
                };
            }
            showHideToolbar();
        },
        function (xhr) {
            if (xhr.status !== 0) {
                confirm('An error occurred while loading the web debug toolbar (' + xhr.status + ': ' + xhr.statusText + ').\n\nDo you want to open the profiler?') && (window.location = profilerUrl);
            }
        },
        {'maxTries': 5}
    );

});
function showHideToolbar() {
    var toolbarToggle = document.getElementsByClassName('sf-toggletoolbar')[0];
    toolbarToggle.addEventListener('click', function (e) {
        //var elem = e.parentNode;
        var elem = e.originalTarget.parentNode;
        //var elem = document.getElementsByClassName('sf-toggletoolbar')[0];
        if (elem.style.display == 'none') {
            document.getElementsByClassName('sf-toolbarreset')[0].style.display = 'none';
            document.getElementsByClassName('sf-toolbarclearer')[0].style.display = 'none';
            elem.style.display = 'block';
        } else {
            document.getElementsByClassName('sf-toolbarreset')[0].style.display = 'block';
            document.getElementsByClassName('sf-toolbarclearer')[0].style.display = 'block';
            elem.style.display = 'none'
        }
        Sfjs.setPreference('toolbar/displayState', 'block');
        return false;
    }, false);

    var toolbarHide = document.getElementsByClassName('hide-button')[0];
    //toolbarHide.onclick = function (e) {
    toolbarHide.addEventListener('click', function (e) {
        var p = e.originalTarget.parentNode;
        p.style.display = 'none';
        (p.previousElementSibling || p.previousSibling).style.display = 'none';
        document.getElementsByClassName('sf-minitoolbar')[0].style.display = 'block';
        document.getElementsByClassName('sf-toggletoolbar')[0].style.display = 'block';
        Sfjs.setPreference('toolbar/displayState', 'none');
        return false;
    });
}

