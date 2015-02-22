    Sfjs = (function() {
        "use strict";

        var noop = function() {},

            profilerStorageKey = 'sf2/profiler/',

            request = function(url, onSuccess, onError, payload, options) {
                var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
                options = options || {};
                options.maxTries = options.maxTries || 0;
                xhr.open(options.method || 'GET', url, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.onreadystatechange = function(state) {
                    if (4 !== xhr.readyState) {
                        return null;
                    }

                    if (xhr.status == 404 && options.maxTries > 1) {
                        setTimeout(function(){
                            options.maxTries--;
                            request(url, onSuccess, onError, payload, options);
                        }, 500);

                        return null;
                    }

                    if (200 === xhr.status) {
                        (onSuccess || noop)(xhr);
                    } else {
                        (onError || noop)(xhr);
                    }
                };
                xhr.send(payload || '');
            },

            hasClass = function(el, klass) {
                return el.className && el.className.match(new RegExp('\\b' + klass + '\\b'));
            },

            removeClass = function(el, klass) {
                if (el.className) {
                    el.className = el.className.replace(new RegExp('\\b' + klass + '\\b'), ' ');
                }
            },

            addClass = function(el, klass) {
                if (!hasClass(el, klass)) {
                    el.className += " " + klass;
                }
            },

            getPreference = function(name) {
                if (!window.localStorage) {
                    return null;
                }

                return localStorage.getItem(profilerStorageKey + name);
            },

            setPreference = function(name, value) {
                if (!window.localStorage) {
                    return null;
                }

                localStorage.setItem(profilerStorageKey + name, value);
            },

            requestStack = [],

            renderAjaxRequests = function() {
                var requestCounter = document.querySelectorAll('.sf-toolbar-ajax-requests');
                if (!requestCounter.length) {
                    return;
                }

                var tbodies = document.querySelectorAll('.sf-toolbar-ajax-request-list');
                var state = 'ok';
                if (tbodies.length) {
                    var tbody = tbodies[0];

                    var rows = document.createDocumentFragment();

                    if (requestStack.length) {
                        for (var i = 0; i < requestStack.length; i++) {
                            var request = requestStack[i];

                            var row = document.createElement('tr');
                            rows.appendChild(row);

                            var methodCell = document.createElement('td');
                            methodCell.textContent = request.method;
                            row.appendChild(methodCell);

                            var pathCell = document.createElement('td');
                            pathCell.className = 'sf-ajax-request-url';
                            pathCell.textContent = request.url;
                            pathCell.setAttribute('title', request.url);
                            row.appendChild(pathCell);

                            var durationCell = document.createElement('td');
                            durationCell.className = 'sf-ajax-request-duration';

                            if (request.duration) {
                                durationCell.textContent = request.duration + "ms";
                            } else {
                                durationCell.textContent = '-';
                            }
                            row.appendChild(durationCell);

                            row.appendChild(document.createTextNode(' '));
                            var profilerCell = document.createElement('td');

                            if (request.profilerUrl) {
                                var profilerLink = document.createElement('a');
                                profilerLink.setAttribute('href', request.profilerUrl);
                                profilerLink.textContent = request.profile;
                                profilerCell.appendChild(profilerLink);
                            } else {
                                profilerCell.textContent = 'n/a';
                            }

                            row.appendChild(profilerCell);

                            var requestState = 'ok';
                            if (request.error) {
                                requestState = 'error';
                                if (state != "loading" && i > requestStack.length - 4) {
                                    state = 'error';
                                }
                            } else if (request.loading) {
                                requestState = 'loading';
                                state = 'loading'
                            }
                            row.className = 'sf-ajax-request sf-ajax-request-' + requestState;
                        }

                        var infoSpan = document.querySelectorAll(".sf-toolbar-ajax-info")[0];
                        var children = Array.prototype.slice.call(tbody.children);
                        for (var i = 0; i < children.length; i++) {
                            tbody.removeChild(children[i]);
                        }
                        tbody.appendChild(rows);

                        if (infoSpan) {
                            var text = requestStack.length + ' call' + (requestStack.length > 1 ? 's' : '');
                            infoSpan.textContent = text;
                        }
                    } else {
                        var cell = document.createElement('td');
                        cell.setAttribute('colspan', '4');
                        cell.textContent = "No AJAX requests yet.";
                        var row = document.createElement('tr');
                        row.appendChild(cell);
                        tbody.appendChild(row);
                    }
                }

                requestCounter[0].textContent = requestStack.length;

                var className = 'sf-toolbar-ajax-requests sf-toolbar-status';
                if (state == 'ok') {
                    className += ' sf-toolbar-status-green';
                } else if (state == 'error') {
                    className += ' sf-toolbar-status-red';
                } else {
                    className += ' sf-ajax-request-loading';
                }

                requestCounter[0].className = className;
            };


        var sfwdt = document.getElementsByClassName('sf-toolbar')[0];
        var toolbarConfig = JSON.parse(sfwdt.getAttribute('data-sftoolbar-config'));
        if ('' != toolbarConfig['excluded_ajax_paths']) {
            var proxied = XMLHttpRequest.prototype.open;

            XMLHttpRequest.prototype.open = function(method, url, async, user, pass) {
                var self = this;
                var sfwdt = document.getElementsByClassName('sf-toolbar')[0];
                var toolbarConfig = JSON.parse(sfwdt.getAttribute('data-sftoolbar-config'));
                /* prevent logging AJAX calls to static and inline files, like templates */
                if (url.substr(0, 1) === '/' && !url.match(new RegExp(toolbarConfig['excluded_ajax_paths']))) {
                    var stackElement = {
                        loading: true,
                        error: false,
                        url: url,
                        method: method,
                        start: new Date()
                    };

                    requestStack.push(stackElement);

                    this.addEventListener("readystatechange", function() {
                        if (self.readyState == 4) {
                            stackElement.duration = new Date() - stackElement.start;
                            stackElement.loading = false;
                            stackElement.error = self.status < 200 || self.status >= 400;
                            stackElement.profile = self.getResponseHeader("X-Debug-Token");
                            stackElement.profilerUrl = self.getResponseHeader("X-Debug-Token-Link");

                            Sfjs.renderAjaxRequests();
                        }
                    }, false);

                    Sfjs.renderAjaxRequests();
                }

                proxied.apply(this, Array.prototype.slice.call(arguments));
            };
        }

        return {
            hasClass: hasClass,

            removeClass: removeClass,

            addClass: addClass,

            getPreference: getPreference,

            setPreference: setPreference,

            request: request,

            renderAjaxRequests: renderAjaxRequests,

            load: function(selector, url, onSuccess, onError, options) {
                var el = document.getElementById(selector);

                if (el && el.getAttribute('data-sfurl') !== url) {
                    request(
                        url,
                        function(xhr) {
                            el.innerHTML = xhr.responseText;
                            el.setAttribute('data-sfurl', url);
                            removeClass(el, 'loading');
                            (onSuccess || noop)(xhr, el);
                        },
                        function(xhr) { (onError || noop)(xhr, el); },
                        '',
                        options
                    );
                }

                return this;
            },

            toggle: function(selector, elOn, elOff) {
                var i,
                    style,
                    tmp = elOn.style.display,
                    el = document.getElementById(selector);

                elOn.style.display = elOff.style.display;
                elOff.style.display = tmp;

                if (el) {
                    el.style.display = 'none' === tmp ? 'none' : 'block';
                }

                return this;
            }
        }
    })();

    function toggleMenuPanels(state, doSave) {
        var leftIconPath = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAhCAYAAADOHBvaAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA4tJREFUeNrkl19IU1Ecx7fZJN2arhq2JUWJlEZL1MAm1B582GBErMJqtabzRRAqFAYa9OfFFx+KQEG3MkYRZkkEJUUwctVSKCJWJojL1ZiydJvLtNXW98AW1znv7t0CHzrw5Xe3nXM/O/f8zvf8LjcajXJWo/E4q9T+P/CaNMZw29rahBMTE3LyoaKi4m1zc/M865swSS6bzZbT09NzfG5urjYUCikikYiQ+ntWVlZQKBQ+F4lE/Var9Ta+CmcK5hsMhpNer/fiwsLCFiYzycnJGZfJZOctFss9uj+wInhwcDDfbDb3+Hy+I+msoVQqNZtMpnNlZWUhxuChoaENHR0djwOBwN5MEkgsFtsGBgY0uPzOJKtzu7q6rmcKJW12dlaJpbqGy7WpwPyWlpZGj8dzkMX9B+l+dLlcda2trbrEHbQEPDY2Jh0dHTWxgF5AxqsRz9J1cjqdlxDWrwTO7u3tNWK7SFhAL8e221WE7pU6Ytk2t7e311JnTQWLJycntWyhpCmVShXCKboBMJyjCKJlzgVzKJiamtrBAKoH1JoAvU+2MN2g6enp3QgboRnqjHl2u70kHA7zU0APpQMlze/357vd7gKyhang7GAwKEsx1gLoAwp0E8JdJtB4w1LK4sw4OAtWl51inBGwAxT/9iLUQb+ZgsHgJ4I5hYWFAQZjbYBrKPB+hGNM4DweL1pUVBRMzOpweXm5WyAQ/GIAfwj4GbZwiUTyIy8vby7eLw4mQH9paamX4VO7kgTeTTeguLjYg0AOjAgVTD58UygUb1i41l84ogGhka4z+rwi9p3MQHwajeYRjrNFlvBbiDfoOmFtQzU1NU/J5JKBA3w+/71er3/J8hA6kSKpOPX19c9w+YH4VDIwqRbG1Wr1HZVK5eX8o6bVaj9XV1cTk3HFcinpsUjs7AWKNwvWO5gpFI93pqmpyYxLB0neVBUIcaI90OHOzs7TfX19ErZA8niNRuNXnU53M2apTmiBSc0lgEoh9cjIiBYV5i6c1YxKYblc/rOhoeEdIin2nkCfoHk2VSYpV7ZCVdB+h8OxDyocHh4WoEJZskRwvUhVVVUIcldWVtrx1XPoNfQFWkynvCWzzIe2QSXQTmg7Od5QT+WinuagliaFnI8kJvQxJpJIATo34zJ8WyR/YF2sfBFDpKCPHyqLMUeajSVniJq9mYKXjUtwPdY34a7W+/EfAQYAZM5kh2zoNWsAAAAASUVORK5CYII=",
            rightIconPath = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAhCAYAAADOHBvaAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA4tJREFUeNrsl11IU2EYx91ytrnpmmy4TYNkqGhEJGiiabuQ2EAiZ1kZW9NtF4pgoBB40weJeCHVjReygVmuIhP6gLoJJNQGadhiJcLa2sb8KN2nrprbel7YYh7WzjnOsIsO/Hnes533/Z33PB/nOZRIJJK2GwflP/ifBg8MDGTOzs4eQeOCggJjb2+vH4aRvwGmyeXyZq/Xe9rv99eGQqHs+D+pVKqfxWJNZ2VlPdRoNPfFYnEgVTBNpVI1Op3OG4FAQETkDul0uo3P518dHh6+B6dB0uC5uTlWf3//zcXFRfV2fMjlcsfUarVGIpG4yYCZDQ0Nz10ulziVAGKz2W+7u7ulNTU1q0TAdKVSOWi1Wlt2InqFQuFTvV5/HoYbW+ICc116T0/PBQLQl0TBEB8nYddtKF6SgXNMJtM1nLUuTUxMSMFeIQqfn5+/vLCwIPgTOL2vr++sx+PJS7LGEEBvowHY60ThkII8iHIVDDMSgbMtFssZnDXkkKOS2AkZuM1mk4HhJAJzV1ZWDuHMZ4DGE8AVeODl5eVin8+XiwVT7HZ7rtvt3kfg5hPB74I5lWxSMBikTU5OlsSYMTAVHoWQRJYg+COA8+PgT8Dokk2CkiuM+fk3mMFg0EiAQ6AWgC3FfoCbOA5GlfRuGQwE3bPFxyKRyAvFPkIQeg6gY3HQerRpvIn5+fkerI9DUN58PB4vsA1oJ5hneFAmk7lZVlZmj704YuAwSrfCwkInzvyhBNBbRHxTWlqK3IJeGJvYdHLBQm9w5rfBNUqyUHRUVVW9A7Ma3eSWlwRK7np4/w6azWYWzjp6UDNRqEAg+DEyMtJJo9Eew+k37I59oI+tra2vIMjw1momkQFpCoViGqAfYOhJVLnQs7dWV1ePy2SyLzvV1EEjsCSVSh/A0BzfkWC3hpxv6Ojo0NbV1a2lCgW/eru6ulBRmQKt4TYCoIMg2ejo6EWdTpcXDodJQ5uamr62t7ffgSHy63tQgEjrkwkqBp0wGo2NWq32MNgMIsCioqJN6DRN5eXl43D6AsUNaJ1Ml7kXFRvQUVDtzMzMMYPBsB/EcjgcVEx7E66oqFivrKx0gFBKvkYuA6FY+b6d9hbVVTboAKgkKtTmcqHgM6G/TuNwOBvRFPmMmg3QJ5Alvlik0tCng1Bu50TznRV9Iuj4iaoeKkDRAPIlA6b67UTBlFvSi+zaR9svAQYA6+V18DvfH/8AAAAASUVORK5CYII=",
            menu = $('#navigation'),
            savedState = Sfjs.getPreference('menu/displayState'),
            displayState, elem, className;

        if (null === savedState) {
            savedState = 'block';
        }

        displayState = state || ('block' === savedState ? 'none' : 'block');

        if ('undefined' === typeof doSave) {
            doSave = true;
        }

        $('#searchBar').css('display', displayState);
        $('#adminBar').css('display', displayState);

        if ('block' === displayState) {
            menu.removeClass('collapsed-menu');
            Sfjs.removeClass(menu.parentNode.parentNode, 'collapsed-menu-parents');

           $('minimizePanelIcon').attr('src', leftIconPath);
        } else {
            menu.addClass('collapsed-menu');
            Sfjs.addClass(menu.parentNode.parentNode, 'collapsed-menu-parents');

            $('minimizePanelIcon').attr('src', rightIconPath);
        }

        if (doSave) {
            Sfjs.setPreference('menu/displayState', displayState);
        }

        try {
            canvasAutoUpdateOnThresholdChange(null);
        } catch (err) {

        }

        return false;
    }
