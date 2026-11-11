'use strict';


const codeEditor = function () {
    const editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};

    // Every editor builds its options from this, never from the previous
    // editor's result — extending editorSettings.codemirror in place made each
    // one inherit the leftovers of the one before it.
    const codemirror_base = _.extend({}, editorSettings.codemirror);
    const codemirror_gen =
        {
            "indentUnit": 2,
            "indentWithTabs": true,
            "inputStyle": "contenteditable",
            "lineNumbers": true,
            "lineWrapping": true,
            "matchBrackets": true,
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
            // Code folding and the rest below ship inside the CodeMirror bundle
            // WordPress already enqueues, so none of this costs a request.
            // Line numbers are listed first: CodeMirror appends that gutter to
            // the end when it is missing, putting numbers right of the arrows.
            "gutters": ["CodeMirror-linenumbers", "CodeMirror-lint-markers", "CodeMirror-foldgutter"],
            "foldGutter": true,
            "lint": true,
            "autoCloseBrackets": true,
            "autoCloseTags": true,
            "matchTags": {
                "bothTags": true
            },
            // Selecting a word marks its other occurrences, scrollbar included.
            "highlightSelectionMatches": {
                "annotateScrollbar": true,
                "showToken": /\w/
            },
            "tabSize": 2,

        };

    // Shortcuts shared by every editor on the page.
    codemirror_gen.extraKeys = _.extend({}, codemirror_gen.extraKeys, {
        // Ctrl/Cmd+S saves the snippet. Without it the only way to save is to
        // scroll past a 700px editor to the button at the top of the page.
        "Ctrl-S": submitFromEditor,
        "Cmd-S": submitFromEditor,
        "Ctrl-H": "replace",
        "Cmd-Alt-F": "replace",
        "Shift-Ctrl-R": "replaceAll",
        "Shift-Cmd-Alt-F": "replaceAll",
        // Sublime-style multi-cursor: rename a class in several places at once.
        "Ctrl-D": "selectNextOccurrence",
        "Cmd-D": "selectNextOccurrence",
        "Alt-G": "jumpToLine",
        "F11": toggleFullScreen
        // Esc is deliberately absent: WordPress listens for it on keydown to let
        // the next Tab move focus out of the editor. Leaving full screen on Esc
        // is handled per instance in addFullScreenButton(), which only consumes
        // the key when there is actually a full screen to leave.
    });

    /** Save the snippet from inside the editor. */
    function submitFromEditor(cm) {
        // CodeMirror hands us the event already prevented, so the browser's own
        // "save page" dialog does not appear.
        const form = cm.getTextArea && cm.getTextArea()
            ? cm.getTextArea().closest('form')
            : document.querySelector('.wowp-settings form, #wowp-settings-form');

        if (!form) {
            return;
        }

        cm.save(); // Flush the editor back into its textarea first.

        const submit = form.querySelector('#submit_settings, [type="submit"]');

        if (submit) {
            submit.click();
        } else {
            form.submit();
        }
    }

    function toggleFullScreen(cm) {
        cm.setOption('fullScreen', !cm.getOption('fullScreen'));
        cm.focus();
    }

    // ── Unsaved work ──────────────────────────────────────────────────────────

    const dirtyEditors = [];
    let savingNow = false;

    /**
     * Warn before leaving with unsaved code.
     *
     * Revision history snapshots a snippet *before* it is overwritten, so it does
     * nothing for work that was never saved — closing the tab or clicking "Back to
     * Codes" simply lost it. CodeMirror tracks this itself through
     * changeGeneration()/isClean().
     */
    function trackUnsaved(instance) {
        if (!instance || !instance.codemirror) {
            return;
        }

        const cm = instance.codemirror;
        const entry = {
            cm: cm,
            generation: cm.changeGeneration(true)
        };

        dirtyEditors.push(entry);
        cm.on('changes', function () {
            refreshSaveStatus();
        });

        // Submitting is not "leaving with unsaved work". Listen on the editor's
        // own form: Global PHP has its own form, not #wowp-settings, and saving
        // it used to raise the browser's "Leave site?" prompt.
        const form = cm.getTextArea && cm.getTextArea() ? cm.getTextArea().form : null;

        if (form && !form.hasAttribute('data-wowp-unsaved')) {
            form.setAttribute('data-wowp-unsaved', '1');
            form.addEventListener('submit', function () {
                savingNow = true;
                markAllSaved();
            });
        }
    }

    // The rest of the snippet form — title, tags, note, attributes, conditions —
    // compared as one snapshot taken once the page has settled. Only the editors
    // used to count, so a changed title or a new display rule was lost without
    // a word. The code columns are left to CodeMirror above: their textareas
    // only catch up on save. The remembered tab is not a change.
    const settingsForm = document.getElementById('wowp-settings');
    const UNTRACKED_FIELDS = ['param[tab]', 'html_code', 'css_code', 'js_code', 'php_code'];
    let formSnapshot = null;

    function formState(form) {
        const parts = [];
        new FormData(form).forEach(function (value, name) {
            if (typeof value === 'string' && UNTRACKED_FIELDS.indexOf(name) === -1) {
                parts.push(name + '=' + value);
            }
        });
        return parts.join('&');
    }

    if (settingsForm) {
        // After every ready handler has filled and toggled its fields.
        window.addEventListener('load', function () {
            setTimeout(function () {
                formSnapshot = formState(settingsForm);
            }, 0);
        });
    }

    function hasUnsavedWork() {
        const codeChanged = dirtyEditors.some(function (entry) {
            return !entry.cm.isClean(entry.generation);
        });

        return codeChanged || (formSnapshot !== null && formState(settingsForm) !== formSnapshot);
    }

    // The save bar (Settings::save_bar()) says whether there is anything to save,
    // from the same check as the prompt. Refreshed after typing, picking, adding
    // or removing rows, and editor changes — batched to one check per frame.
    const saveStatus = document.getElementById('wowp-savebar-status');
    let statusQueued = false;

    function refreshSaveStatus() {
        if (!saveStatus || statusQueued) {
            return;
        }
        statusQueued = true;
        window.requestAnimationFrame(function () {
            statusQueued = false;
            const dirty = !savingNow && hasUnsavedWork();
            saveStatus.classList.toggle('is-dirty', dirty);
            saveStatus.textContent = dirty ? saveStatus.getAttribute('data-dirty') : '';
        });
    }

    if (settingsForm && saveStatus) {
        ['input', 'change'].forEach(function (type) {
            settingsForm.addEventListener(type, refreshSaveStatus);
        });
        // Adding or removing a row is a click that changes the form after it.
        settingsForm.addEventListener('click', function () {
            setTimeout(refreshSaveStatus, 0);
        });
        window.addEventListener('load', function () {
            setTimeout(refreshSaveStatus, 0);
        });
    }

    const saveKey = document.getElementById('wowp-savebar-key');
    if (saveKey && /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent)) {
        saveKey.textContent = '⌘ S';
    }

    // A code tab with code in it carries a dot (Settings::tab_marks() sets it on
    // load); kept in step while typing, so an emptied tab loses it.
    function markTab(instance, file) {
        const label = document.querySelector('label[for="wowp-tab-' + file + '"]');
        if (!instance || !instance.codemirror || !label) {
            return;
        }
        instance.codemirror.on('change', function (cm) {
            label.classList.toggle('has-content', cm.getValue().trim() !== '');
        });
    }

    function markAllSaved() {
        dirtyEditors.forEach(function (entry) {
            entry.generation = entry.cm.changeGeneration(true);
        });
    }

    window.addEventListener('beforeunload', function (event) {
        if (savingNow || !hasUnsavedWork()) {
            return;
        }

        // Browsers show their own wording; a non-empty returnValue is what asks.
        event.preventDefault();
        event.returnValue = '';
        return '';
    });


    // ── Shortcut reference ────────────────────────────────────────────────────

    const SHORTCUTS = [
        ['Ctrl/Cmd + S', 'Save the snippet'],
        ['Ctrl/Cmd + F', 'Find'],
        ['Ctrl + H  /  Cmd + Alt + F', 'Replace'],
        ['Shift + Ctrl + R', 'Replace all'],
        ['Ctrl/Cmd + D', 'Select next occurrence'],
        ['Ctrl + Space', 'Autocomplete'],
        ['Ctrl/Cmd + /', 'Toggle comment'],
        ['Alt + G', 'Go to line'],
        ['F11', 'Full screen'],
        ['Esc', 'Leave full screen']
    ];

    /**
     * One dialog for every editor, built here rather than in each tab's template:
     * the shortcuts are set on the shared options object, so a copy per tab would
     * be four places to forget.
     */
    function shortcutDialog() {
        let dialog = document.getElementById('wowp-shortcuts-dialog');

        if (dialog) {
            return dialog;
        }

        dialog = document.createElement('dialog');
        dialog.id = 'wowp-shortcuts-dialog';
        dialog.className = 'wowp-repeater-dialog wowp-php-help-dialog';

        const rows = SHORTCUTS.map(function (pair) {
            return '<tr><td><code>' + pair[0] + '</code></td><td>' + pair[1] + '</td></tr>';
        }).join('');

        dialog.innerHTML =
            '<div class="wowp-dialog__header">' +
                '<h3 class="wowp-dialog__title">Editor shortcuts</h3>' +
                '<button type="button" class="wowp-dialog__close dashicons dashicons-no-alt" title="Close"></button>' +
            '</div>' +
            '<div class="wowp-dialog__body">' +
                '<table class="wowp-repeater-types-table"><tbody>' + rows + '</tbody></table>' +
            '</div>';

        dialog.querySelector('.wowp-dialog__close').addEventListener('click', function () {
            dialog.close();
        });

        document.body.appendChild(dialog);

        return dialog;
    }

    function addShortcutButton(wrapper) {
        if (!wrapper || wrapper.querySelector('.wowp-cm-shortcuts')) {
            return;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'wowp-cm-shortcuts wowp-tooltip is-pointer';
        button.setAttribute('data-tooltip', 'Editor shortcuts');
        button.setAttribute('aria-label', 'Editor shortcuts');
        button.innerHTML = '<span class="dashicons dashicons-editor-help"></span>';

        button.addEventListener('click', function () {
            shortcutDialog().showModal();
        });

        wrapper.appendChild(button);
    }

    /**
     * Grow an editor with its code, between a floor and most of the window. A
     * fixed 700px left a short snippet in a box of empty lines and made a long
     * one scroll inside the page. Full screen sizes the editor itself, so it is
     * left alone then. Tab panels are moved off screen, not display: none, so an
     * editor on a hidden tab can still be measured. Same as WP Coder Pro.
     */
    const EDITOR_MIN_HEIGHT = 320;

    function fitEditorHeight(cm) {
        if (!cm || cm.getOption('fullScreen')) {
            return;
        }
        const max = Math.max(EDITOR_MIN_HEIGHT, Math.round(window.innerHeight * 0.8));
        // Bottom of the last line, plus room for two more so the end of the code
        // never sits on the editor's edge.
        const content = cm.heightAtLine(cm.lineCount(), 'local') + 2 * cm.defaultTextHeight();
        const height = Math.min(max, Math.max(EDITOR_MIN_HEIGHT, Math.ceil(content)));
        if (Math.abs(cm.getWrapperElement().offsetHeight - height) > 1) {
            cm.setSize(null, height);
        }
    }

    function autoHeight(instance) {
        if (!instance || !instance.codemirror) {
            return;
        }
        const cm = instance.codemirror;
        let queued = false;
        const fit = function () {
            if (queued) {
                return;
            }
            queued = true;
            window.requestAnimationFrame(function () {
                queued = false;
                fitEditorHeight(cm);
            });
        };
        cm.on('changes', fit);
        window.addEventListener('resize', fit);
        fitEditorHeight(cm);
    }

    /**
     * Put a full-screen toggle on an editor.
     *
     * Runs once per instance after initialisation rather than per call site, so
     * every editor on the page gets one without repeating this five times.
     */
    function addFullScreenButton(instance) {
        if (!instance || !instance.codemirror) {
            return;
        }

        const cm = instance.codemirror;
        const wrapper = cm.getWrapperElement();

        if (!wrapper || wrapper.querySelector('.wowp-cm-fullscreen')) {
            return;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'wowp-cm-fullscreen wowp-tooltip is-pointer';
        button.setAttribute('data-tooltip', 'Full screen (F11)');
        button.setAttribute('aria-label', 'Full screen');
        button.innerHTML = '<span class="dashicons dashicons-editor-expand"></span>';

        button.addEventListener('click', function () {
            toggleFullScreen(cm);
        });

        // Only swallow Escape when there is a full screen to leave, so WordPress
        // keeps using it to release focus for the next Tab.
        cm.on('keydown', function (editor, event) {
            if (event.key === 'Escape' && editor.getOption('fullScreen')) {
                event.preventDefault();
                editor.setOption('fullScreen', false);
            }
        });

        cm.on('optionChange', function (editor, option) {
            if (option !== 'fullScreen') {
                return;
            }

            const on = editor.getOption('fullScreen');
            button.querySelector('.dashicons').className =
                'dashicons ' + (on ? 'dashicons-editor-contract' : 'dashicons-editor-expand');
            button.setAttribute('data-tooltip', on ? 'Exit full screen (Esc)' : 'Full screen (F11)');
            // The wrapper leaves the normal flow when full screen, so its height
            // has to be recalculated or the last lines stay unreachable.
            editor.refresh();
            if (!on) {
                fitEditorHeight(editor);
            }
        });

        wrapper.appendChild(button);
        addShortcutButton(wrapper);
    }

    const html_code = document.getElementById('html_code');
    let htmleditor;
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

        editorSettings.codemirror = _.extend({}, codemirror_base, codemirror_gen, codemirror_el);

        htmleditor = wp.codeEditor.initialize(html_code, editorSettings);
        addFullScreenButton(htmleditor);
        trackUnsaved(htmleditor);
        autoHeight(htmleditor);
        markTab(htmleditor, 'html-code');


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
    let csseditor;
    if (css_code) {
        let codemirror_el = {"mode": 'css',};
        editorSettings.codemirror = _.extend({}, codemirror_base, codemirror_gen, codemirror_el);
        // const tab = document.querySelector('[data-content="css-code"]');
        // tab.classList.add('tab-content-active');
        csseditor = wp.codeEditor.initialize(css_code, editorSettings);
        addFullScreenButton(csseditor);
        trackUnsaved(csseditor);
        autoHeight(csseditor);
        markTab(csseditor, 'css-code');
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
    let jseditor;
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
        editorSettings.codemirror = _.extend({}, codemirror_base, codemirror_gen, codemirror_el);
        // const tab = document.querySelector('[data-content="js-code"]');
        // tab.classList.add('tab-content-active');
        jseditor = wp.codeEditor.initialize(js_code, editorSettings);
        addFullScreenButton(jseditor);
        trackUnsaved(jseditor);
        autoHeight(jseditor);
        markTab(jseditor, 'js-code');
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
        editorSettings.codemirror = _.extend({}, codemirror_base, codemirror_gen, codemirror_el);
        // const tab = document.querySelector('[data-content="php-code"]');
        // tab.classList.add('tab-content-active');
        const phpeditor = wp.codeEditor.initialize(php_code, editorSettings);
        addFullScreenButton(phpeditor);
        trackUnsaved(phpeditor);
        autoHeight(phpeditor);
        markTab(phpeditor, 'php-code');
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
        editorSettings.codemirror = _.extend({}, codemirror_base, codemirror_gen, codemirror_el);
        const editor = wp.codeEditor.initialize(global_php, editorSettings);
        addFullScreenButton(editor);
        trackUnsaved(editor);
        autoHeight(editor);

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


    // Live Preview
    let timeout;
    const previewCheckbox = document.getElementById('checkbox_preview');

    function initPreviewListener() {
        document.querySelectorAll('.wowp-settings__page-content').forEach(editor => {
            editor.addEventListener('keydown', () => {
                clearTimeout(timeout);
                // Global PHP has editors but no Live Preview checkbox.
                if (previewCheckbox && previewCheckbox.checked) {
                    timeout = setTimeout(updatePreview, 500);
                }
            });
        });
    }


    if (previewCheckbox) {
        updatePreview();

        previewCheckbox.addEventListener('change', () => {
            if (previewCheckbox.checked) {
                updatePreview();
            }
        });
    }

    initPreviewListener();

    function updatePreview() {
        const html = htmleditor.codemirror.getValue();
        const css = csseditor.codemirror.getValue();

        let styleLinks = [];

        document.querySelectorAll('input[id*="include_file_"]').forEach(input => {
            const url = input.value.trim();

          if (url.includes('.css')) {
                styleLinks.push(`<link rel="stylesheet" href="${url}">`);
            }
        });

        const iframe = document.getElementById('wowp-preview');
        iframe.setAttribute("srcdoc", `
        <!DOCTYPE html>
        <html>
          <head>
            <style>body{margin:0;padding:0;box-sizing:border-box;}</style>
             ${styleLinks.join('\n')}
            <style>${css}</style>
          </head>
          <body>${html}</body>
        </html>
    `);
    }

    jQuery('.wowp-preview__dot.is-toggle').on('click', function () {
        jQuery(this).toggleClass('is-active');
        jQuery(this).find('.dashicons').toggleClass('is-hidden');
        jQuery('.wowp-preview__iframe #wowp-preview').toggleClass('is-hidden');
        jQuery('.wowp-preview__iframe').toggleClass('is-toggled');
    });

    jQuery('.wowp-preview__dot.is-reset').on('click', function () {
        jQuery('.wowp-preview__iframe').removeAttr('style');
        updatePreview();
    });

    const iframe = jQuery('.wowp-preview__iframe');
    const $size = iframe.find('.wowp-preview__size');
    // let timeout;

    if (iframe.length && $size.length) {
        const observer = new ResizeObserver(entries => {
            for (const entry of entries) {
                const width = Math.round(entry.contentRect.width);
                const height = Math.round(entry.contentRect.height) - 50;

                $size.text(`${width}px × ${height}px`).fadeIn(150);

                clearTimeout(timeout);
                // timeout = setTimeout(() => $size.fadeOut(300), 3000);
            }
        });

        observer.observe(iframe[0]);
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
            case 'archive_author':
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
    new changeTemplate;
    new feature;

})