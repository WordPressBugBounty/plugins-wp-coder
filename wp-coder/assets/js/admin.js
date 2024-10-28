'use strict';

const settingTabs = function () {
    const tabs = document.getElementById('settings-tab');
    if (!tabs) {
        return false;
    }
    const tabsContent = document.querySelectorAll('#settings-content .tab-content');

    const links = tabs.querySelectorAll('a, label');
    links.forEach((link) => {
        link.addEventListener('click', function () {
            const attr = link.getAttribute('data-tab');
            links.forEach(el => el.classList.remove('nav-tab-active'));
            tabsContent.forEach(el => el.classList.remove('tab-content-active'));
            link.classList.add('nav-tab-active');
            const tabContent = document.querySelector('[data-content=' + attr + ']');
            tabContent.classList.add('tab-content-active');
            setLocalStorage(attr);
        });
    });

    hideTabs();

    function hideTabs() {
        const tabs = document.querySelectorAll('.wowp-hide-tabs input[type="checkbox"]');
        if (!tabs) {
            return false;
        }
        const tabs_arr = {
            hide_html: 'html-code',
            hide_css: 'css-code',
            hide_js: 'js-code',
            hide_php: 'php-code',
            hide_include: 'include',
            hide_settings: 'settings',
            hide_attributes: 'attributes',
        };

        tabs.forEach(el => {
            el.addEventListener('click', function () {
                const id = el.id;
                const key = id.replace('checkbox_', '');
                const tab = document.querySelector('[data-tab="' + tabs_arr[key] + '"]');
                const tabcontent = document.querySelector('[data-content="' + tabs_arr[key] + '"]');
                if (el.checked) {
                    tab.style.display = 'none';
                    tabcontent.style.display = 'none';
                } else {
                    tab.style.display = 'block';
                    tabcontent.style.display = 'block';
                }
            })
        })


    }

    setTabs();

    function setTabs() {
        if (getLocalStorage() === '') {
            return false;
        }
        const tab = getLocalStorage();
        if(tab === false) {
            return false;
        }
        links.forEach(el => el.classList.remove('nav-tab-active'));
        tabsContent.forEach(el => el.classList.remove('tab-content-active'));
        const link = tabs.querySelector('a[data-tab="' + tab + '"]');
        link.classList.add('nav-tab-active');
        const cont = document.querySelector('[data-content=' + tab + ']');
        cont.classList.add('tab-content-active');
    }

    function setLocalStorage(attr) {
        const tool_id = document.getElementById('tool_id');
        if (!tool_id) {
            return false;
        }
        sessionStorage.setItem("wowpTab_" + tool_id.value, attr);
    }

    function getLocalStorage() {
        const tool_id = document.getElementById('tool_id');

        if (!tool_id) {
            return false;
        }
        const tab = sessionStorage.getItem("wowpTab_" + tool_id.value);
        if (!tab) {
            return false;
        } else {
            return tab;
        }
    }
}

const codeEditor = function () {
    const editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
    const codemirror_gen =
        {
            "indentUnit": 2,
            "indentWithTabs": true,
            "inputStyle": "contenteditable",
            "lineNumbers": true,
            "lineWrapping": true,
            "styleActiveLine": true,
            "continueComments": true,
            "extraKeys": {
                "Ctrl-Space": "autocomplete",
                "Ctrl-\/": "toggleComment",
                "Cmd-\/": "toggleComment",
                "Alt-F": "findPersistent",
                "Ctrl-F": "findPersistent",
                "Cmd-F": "findPersistent"
            },
            "direction": "ltr",
            "gutters": ["CodeMirror-lint-markers"],
            "lint": true,
            "autoCloseBrackets": true,
            "autoCloseTags": true,
            "matchTags": {
                "bothTags": true
            },
            "tabSize": 2,

        };

    const html_code = document.getElementById('html_code');
    if (html_code) {
        let codemirror_el =
            {
                "tagname-lowercase": true,
                "attr-lowercase": true,
                "attr-value-double-quotes": false,
                "doctype-first": false,
                "tag-pair": true,
                "spec-char-escape": true,
                "id-unique": true,
                "src-not-empty": true,
                "attr-no-duplication": true,
                "alt-require": true,
                "space-tab-mixed-disabled": "tab",
                "attr-unsafe-chars": true,
                "mode": 'htmlmixed',

            };

        editorSettings.codemirror = _.extend({}, editorSettings.codemirror, codemirror_gen, codemirror_el,);

        const htmleditor = wp.codeEditor.initialize(html_code, editorSettings);

        const htmlNavigationMenu = document.getElementById("htmlNavigationMenu");

        htmleditor.codemirror.eachLine(function (line) {
            const text = line.text;
            if (text.startsWith("<!-- NAV:")) {
                let navItem = document.createElement("li");
                let navLink = document.createElement("a");
                navLink.href = "#";
                // navLink.innerText = text.replace("<!-- NAV:", "").trim();
                navLink.innerText = text.replace(/<!-- NAV:|-->|<!-- NAV: -->/g, '').trim();
                navLink.addEventListener("click", function (e) {
                    e.preventDefault();

                    // Remove existing highlights from the gutter
                    htmleditor.codemirror.eachLine(function (l) {
                        htmleditor.codemirror.removeLineClass(l, 'gutter', 'highlighted-gutter');
                    });

                    // Set cursor and scroll to line
                    htmleditor.codemirror.setCursor(line.lineNo());
                    let heightOfLine = htmleditor.codemirror.defaultTextHeight();
                    let verticalPos = line.lineNo() * heightOfLine;
                    htmleditor.codemirror.scrollTo(null, verticalPos);

                    // Highlight the line number in the gutter
                    htmleditor.codemirror.addLineClass(line.lineNo(), 'gutter', 'highlighted-gutter');

                    htmleditor.codemirror.focus();
                });
                navItem.appendChild(navLink);
                htmlNavigationMenu.appendChild(navItem);
            }
        });
    }

    const css_code = document.getElementById('css_code');
    if (css_code) {
        let codemirror_el = {"mode": 'css',};
        editorSettings.codemirror = _.extend({}, editorSettings.codemirror, codemirror_gen, codemirror_el,);
        // const tab = document.querySelector('[data-content="css-code"]');
        // tab.classList.add('tab-content-active');
        const csseditor = wp.codeEditor.initialize(css_code, editorSettings);
        // tab.classList.remove('tab-content-active');

        const cssNavigationMenu = document.getElementById("cssNavigationMenu");

        csseditor.codemirror.eachLine(function (line) {
            const text = line.text;
            if (text.startsWith("/* NAV:")) {
                let navItem = document.createElement("li");
                let navLink = document.createElement("a");
                navLink.href = "#";
                // navLink.innerText = text.replace("// NAV:", "").trim();
                navLink.innerText = text.replace(/\/\* NAV:|\*\/|\/\* NAV: \*\//g, '').trim();
                navLink.addEventListener("click", function (e) {
                    e.preventDefault();

                    // Remove existing highlights from the gutter
                    csseditor.codemirror.eachLine(function (l) {
                        csseditor.codemirror.removeLineClass(l, 'gutter', 'highlighted-gutter');
                    });

                    // Set cursor and scroll to line
                    csseditor.codemirror.setCursor(line.lineNo());
                    let heightOfLine = csseditor.codemirror.defaultTextHeight();
                    let verticalPos = line.lineNo() * heightOfLine;
                    csseditor.codemirror.scrollTo(null, verticalPos);

                    // Highlight the line number in the gutter
                    csseditor.codemirror.addLineClass(line.lineNo(), 'gutter', 'highlighted-gutter');

                    csseditor.codemirror.focus();
                });
                navItem.appendChild(navLink);
                cssNavigationMenu.appendChild(navItem);
            }
        });
    }

    const js_code = document.getElementById('js_code');
    if (js_code) {
        let codemirror_el = {
            "boss": true,
            "curly": true,
            "eqeqeq": true,
            "eqnull": true,
            "es3": true,
            "expr": true,
            "immed": true,
            "noarg": true,
            "nonbsp": true,
            "onevar": true,
            "quotmark": "single",
            "trailing": true,
            "undef": true,
            "unused": true,
            "browser": true,
            "globals": {
                "_": false,
                "Backbone": false,
                "jQuery": true,
                "JSON": false,
                "wp": false
            },
            "mode": 'javascript',
        };
        editorSettings.codemirror = _.extend({}, editorSettings.codemirror, codemirror_gen, codemirror_el,);
        // const tab = document.querySelector('[data-content="js-code"]');
        // tab.classList.add('tab-content-active');
        const jseditor = wp.codeEditor.initialize(js_code, editorSettings);
        // tab.classList.remove('tab-content-active');

        const jsNavigationMenu = document.getElementById("jsNavigationMenu");

        jseditor.codemirror.eachLine(function (line) {
            const text = line.text;
            if (text.startsWith("// NAV:")) {
                let navItem = document.createElement("li");
                let navLink = document.createElement("a");
                navLink.href = "#";
                navLink.innerText = text.replace("// NAV:", "").trim();
                navLink.addEventListener("click", function (e) {
                    e.preventDefault();

                    // Remove existing highlights from the gutter
                    jseditor.codemirror.eachLine(function (l) {
                        jseditor.codemirror.removeLineClass(l, 'gutter', 'highlighted-gutter');
                    });

                    // Set cursor and scroll to line
                    jseditor.codemirror.setCursor(line.lineNo());
                    let heightOfLine = jseditor.codemirror.defaultTextHeight();
                    let verticalPos = line.lineNo() * heightOfLine;
                    jseditor.codemirror.scrollTo(null, verticalPos);

                    // Highlight the line number in the gutter
                    jseditor.codemirror.addLineClass(line.lineNo(), 'gutter', 'highlighted-gutter');

                    jseditor.codemirror.focus();
                });
                navItem.appendChild(navLink);
                jsNavigationMenu.appendChild(navItem);
            }
        });
    }

    const php_code = document.getElementById('php_code');
    if (php_code) {
        let codemirror_el = {
            firstLineNumber: 4,
            mode: {
                name: 'php',
                startOpen: true
            }
        };
        editorSettings.codemirror = _.extend({}, editorSettings.codemirror, codemirror_gen, codemirror_el,);
        // const tab = document.querySelector('[data-content="php-code"]');
        // tab.classList.add('tab-content-active');
        const phpeditor = wp.codeEditor.initialize(php_code, editorSettings);
        // tab.classList.remove('tab-content-active');

        const phpNavigationMenu = document.getElementById("phpNavigationMenu");

        phpeditor.codemirror.eachLine(function (line) {
            const text = line.text;
            if (text.startsWith("// NAV:")) {
                let navItem = document.createElement("li");
                let navLink = document.createElement("a");
                navLink.href = "#";
                navLink.innerText = text.replace("// NAV:", "").trim();
                navLink.addEventListener("click", function (e) {
                    e.preventDefault();

                    // Remove existing highlights from the gutter
                    phpeditor.codemirror.eachLine(function (l) {
                        phpeditor.codemirror.removeLineClass(l, 'gutter', 'highlighted-gutter');
                    });

                    // Set cursor and scroll to line
                    phpeditor.codemirror.setCursor(line.lineNo());
                    let heightOfLine = phpeditor.codemirror.defaultTextHeight();
                    let verticalPos = line.lineNo() * heightOfLine;
                    phpeditor.codemirror.scrollTo(null, verticalPos);

                    // Highlight the line number in the gutter
                    phpeditor.codemirror.addLineClass(line.lineNo(), 'gutter', 'highlighted-gutter');

                    phpeditor.codemirror.focus();
                });
                navItem.appendChild(navLink);
                phpNavigationMenu.appendChild(navItem);
            }
        });
    }

    const global_php = document.getElementById('wpcoder-global-php');
    if (global_php) {
        let codemirror_el = {
            mode: {
                name: 'php',
                startOpen: true,

            },
            firstLineNumber: 4,
        };
        editorSettings.codemirror = _.extend({}, editorSettings.codemirror, codemirror_gen, codemirror_el,);
        const editor = wp.codeEditor.initialize(global_php, editorSettings);

        const navigationMenu = document.getElementById("phpNavigationMenu");

        editor.codemirror.eachLine(function (line) {
            const text = line.text;
            if (text.startsWith("// NAV:")) {
                let navItem = document.createElement("li");
                let navLink = document.createElement("a");
                navLink.href = "#";
                navLink.innerText = text.replace("// NAV:", "").trim();
                navLink.addEventListener("click", function (e) {
                    e.preventDefault();

                    // Remove existing highlights from the gutter
                    editor.codemirror.eachLine(function (l) {
                        editor.codemirror.removeLineClass(l, 'gutter', 'highlighted-gutter');
                    });

                    // Set cursor and scroll to line
                    editor.codemirror.setCursor(line.lineNo());
                    let heightOfLine = editor.codemirror.defaultTextHeight();
                    let verticalPos = line.lineNo() * heightOfLine;
                    editor.codemirror.scrollTo(null, verticalPos);

                    // Highlight the line number in the gutter
                    editor.codemirror.addLineClass(line.lineNo(), 'gutter', 'highlighted-gutter');

                    editor.codemirror.focus();
                });
                navItem.appendChild(navLink);
                navigationMenu.appendChild(navItem);
            }
        });


    }

}


const changeTemplate = function () {

    const template = document.getElementById('template');
    if (!template) {
        return false;
    }


    template.addEventListener('change', function (event) {

        const parent = document.getElementById('wpcoder-template');
        let type = template.value;
        type = default_custom_post(type);
        const elements = parent.querySelectorAll('.wowp-field');

        switch (type) {
            case 'post_all':
            case 'page_all':
                elements[1].classList.add('is-hidden');
                elements[2].classList.add('is-hidden');
                elements[3].classList.add('is-hidden');
                break;
            case 'post_selected':
            case 'post_in_category':
            case 'page_selected':
            case 'archive_category':
            case 'archive_tag':
                elements[1].classList.remove('is-hidden');
                elements[2].classList.remove('is-hidden');
                elements[3].classList.add('is-hidden');
                break;
            case 'page_type':
                elements[1].classList.remove('is-hidden');
                elements[2].classList.add('is-hidden');
                elements[3].classList.remove('is-hidden');
                break;
            default:
                elements[1].classList.add('is-hidden');
                elements[2].classList.add('is-hidden');
                elements[3].classList.add('is-hidden');
                break;

        }

    });

    function default_custom_post(el) {
        if (el.includes('custom_post_selected')) {
            return 'post_selected';
        }
        if (el.includes('custom_post_tax')) {
            return 'post_category';
        }
        if (el.includes('custom_post_all')) {
            return 'post_all';
        }
        return el;
    }

}


const feature = function () {
    const texts = document.querySelectorAll('.w_card-description');

    if (!texts) {
        return false;
    }
    texts.forEach((el) => {
        el.addEventListener('click', () => {
            el.classList.toggle('is-open');
        })
    })

}

document.addEventListener('DOMContentLoaded', function () {

    new codeEditor;
    new settingTabs;
    new changeTemplate;
    new feature;
})