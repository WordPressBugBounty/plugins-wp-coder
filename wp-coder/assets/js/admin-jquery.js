'use strict';

jQuery(document).ready(function ($) {

    // Snippets and Tools tabs (radios + labels). The radios used to be
    // display:none, so the keyboard could reach no category but the open one;
    // now they are only visually hidden — Tab enters the group, the arrow keys
    // change category — and the label shows the focus. Each label counts the
    // switches on in its panel, kept live as they change.
    $('.wowp-snippets__tabs').each(function () {
        const $tabs = $(this);

        // focusin/focusout: delegated focus events arrive under those names.
        $tabs.on('focusin', '.wowp-snippets__tabs-radio', function () {
            $tabs.find('label[for="' + this.id + '"]').toggleClass('is-focused', this.matches(':focus-visible'));
        });
        $tabs.on('focusout', '.wowp-snippets__tabs-radio', function () {
            $tabs.find('label[for="' + this.id + '"]').removeClass('is-focused');
        });

        const count = function () {
            $tabs.find('.wowp-snippets__tabs-content').each(function () {
                const id = String($(this).data('content'));
                const on = $(this).find('.wowp-snippet__item > .wowp-field .switch input[type="checkbox"]:checked').length;
                const $label = $tabs.find('label[for="' + id + '"]');
                let $badge = $label.find('.wowp-snippets__tabs-count');
                if (!$badge.length) {
                    $badge = $('<span class="wowp-snippets__tabs-count"></span>').appendTo($label);
                }
                $badge.text(on ? on : '').attr('aria-label', on ? on + ' on' : null);
            });
        };
        count();
        $tabs.on('change', '.switch input[type="checkbox"]', count);

        // On a phone the labels are a strip that scrolls sideways: bring the
        // open one into it, without moving the page.
        const reveal = function () {
            const strip = $tabs.find('.wowp-snippets__tabs-labels')[0];
            const radio = $tabs.find('.wowp-snippets__tabs-radio:checked')[0];
            const label = radio ? $tabs.find('label[for="' + radio.id + '"]')[0] : null;
            if (strip && label && strip.scrollWidth > strip.clientWidth) {
                strip.scrollLeft = label.offsetLeft - strip.offsetLeft - 16;
            }
        };
        reveal();
        $tabs.on('change', '.wowp-snippets__tabs-radio', reveal);
    });



    // All checkboxes
    $('#wowp-settings').on('click', 'input:checkbox', function () {
        checkboxchecked(this);
    });

    // data-inverse: the switch shows the opposite of what is stored — "Active"
    // over the status column, where "1" means deactivated (sidebar.php).
    function checkboxchecked(el) {
        const on = $(el).prop('checked');
        const stored = $(el).data('inverse') ? !on : on;
        $(el).next('input[type="hidden"]').val(stored ? '1' : '');
    }

    // Preview
    $('[data-option="preview"] input[type="checkbox"]').each(show_preview).on('click', show_preview);

    function show_preview() {
        const preview = $('.wowp-preview');
        if ($(this).is(':checked')) {
            $(preview).removeClass('is-hidden');
        } else {
            $(preview).addClass('is-hidden');
        }
    }

    // Browsers options
    $('[data-option="hide_browsers"] input[type="checkbox"]').each(hide_browsers);
    $('[data-option="hide_browsers"] input[type="checkbox"]').on('click', hide_browsers);

    function hide_browsers() {
        const browsers = $(this).parents('.wowp-field').siblings('.wowp-field');
        if ($(this).is(':checked')) {
            $(browsers).removeClass('is-hidden');
        } else {
            $(browsers).addClass('is-hidden');
        }
    }

    // Languages options
    $('[data-option="depending_language"] input[type="checkbox"]').each(languageOn);
    $('[data-option="depending_language"] input[type="checkbox"]').on('click', languageOn);

    function languageOn() {
        const languages = $(this).parents('.wowp-field').siblings('.wowp-field');
        if ($(this).is(':checked')) {
            $(languages).removeClass('is-hidden');
        } else {
            $(languages).addClass('is-hidden');
        }
    }

    // Users
    $('[name="param[item_user]"]').each(usersRule);
    $('[name="param[item_user]"]').on('change', usersRule);

    function usersRule() {
        const user = $(this).val();
        const parent = $(this).closest('fieldset');
        const boxRoles = $(parent).find('.wowp-users-roles');
        $(boxRoles).addClass('is-hidden');
        if (user === '2') {
            $(boxRoles).removeClass('is-hidden');
        }
    }

    $('.wowp-users-roles .wowp-field:first input:first').each(checkAllRoles);
    $('.wowp-users-roles .wowp-field:first input:first').on('change', checkAllRoles);

    function checkAllRoles() {
        const checkboxes = $('.wowp-users-roles input[type="checkbox"]');
        if ($(this).is(':checked')) {
            $(checkboxes).prop('checked', true);
        }
    }

    // Schedule options
    $('.wowp-dates input[type="checkbox"]').each(datesSchedule);
    $('#schedule').on('click', '.wowp-dates input', datesSchedule);

    function datesSchedule() {
        const parent = $(this).closest('.wowp-fields__group');
        if ($(this).is(':checked')) {
            $(parent).find('.wowp-date-input').removeClass('is-hidden');
        } else {
            $(parent).find('.wowp-date-input').addClass('is-hidden');
        }
    }

    $('#add-schedule').on('click', function (e) {
        e.preventDefault();

        const temlate = $('#clone-schedule').clone().html();

        $(temlate).insertBefore('#schedule .wowp-btn-actions');
        $('.wowp-dates input[type="checkbox"]').each(datesSchedule);
    });

    $('#schedule').on('click', '.dashicons-trash', function () {
        const parent = $(this).closest('.wowp-fields__group');
        $(parent).remove();
    });

    // Display rules
    $('#add_display').on('click', function (e) {
        e.preventDefault();

        const temlate = $('#template-display').clone().html();

        $(temlate).insertBefore('#display-rules .btn-add-display');
        $('#display-rules .display-option select').each(displayRules);
    });

    $('#display-rules').on('click', '.dashicons-trash', function () {
        const userConfirmed = confirm("Are you sure you want to remove?");
        if (userConfirmed) {
            const parent = $(this).closest('.wowp-fields__group');
            $(parent).remove();
        }

    });

    $('#display-rules .display-option select').each(displayRules);
    $('#display-rules').on('change', '.display-option select', displayRules);

    function displayRules() {
        let type = $(this).val();
        const parent = $(this).closest('.wowp-fields__group');
        $(parent).find('.display-operator, .display-ids, .display-pages').addClass('is-hidden');
        if (type.indexOf('custom_post_selected') !== -1) {
            type = 'post_selected';
        }
        if (type.indexOf('custom_post_tax') !== -1) {
            type = 'post_category';
        }
        switch (type) {
            case 'post_selected':
            case 'post_category':
            case 'page_selected':
                $(parent).find('.display-operator, .display-ids').removeClass('is-hidden');
                break;
            case 'page_type':
                $(parent).find('.display-operator, .display-pages').removeClass('is-hidden');
                break;
        }

    }

    // Includes Assets files
    $('#add-include').on('click', function (e) {
        e.preventDefault();

        const $row = $($('#clone-includes').html().trim());
        $row.insertBefore('#includes-files .btn-add-display');
        $row.find('.display-option select').each(includeFiles);
        $row.find('.include-file input').trigger('focus');
    });

    // The type follows the URL's extension (query string and hash aside), so a
    // pasted .css link does not go out as a script.
    $('#includes-files').on('input change', '.include-file input', function () {
        const match = String(this.value).split(/[?#]/)[0].match(/\.(css|js)$/i);
        if (!match) {
            return;
        }
        const $type = $(this).closest('.wowp-fields__group').find('.display-option select');
        if ($type.val() !== match[1].toLowerCase()) {
            $type.val(match[1].toLowerCase()).trigger('change');
        }
    });

    $('#includes-files .display-option select').each(includeFiles);
    $('#includes-files').on('change', '.display-option select', includeFiles);

    function includeFiles() {
        let type = $(this).val();
        const parent = $(this).closest('.wowp-fields__group');
        const js = $(parent).find('.js-attr');
        const css =  $(parent).find('.css-only-preview');
        $(js).add(css).addClass('is-hidden');
        switch (type) {
            case 'css':
                $(css).removeClass('is-hidden');
                break;
            case 'js':
                $(js).removeClass('is-hidden');
                break;
        }

    }

    // Scripts & Styles rows (7.include.php): × removes at once, with Undo in the
    // same list — as on Attributes. The confirm() it replaces was the button
    // that, cancelled, reloaded the page. Each list shows "No files yet." when
    // empty, and the tab counts files and handles together.
    $('.wowp-assets').each(function () {
        const $list = $(this);
        const $undo = $list.find('.wowp-attr-undo');
        let pending = null;

        const drop = function () {
            if (pending) {
                clearTimeout(pending.timer);
                pending = null;
            }
            $undo.prop('hidden', true);
        };

        $list.on('click', '.wowp-asset-remove', function () {
            const $row = $(this).closest('.wowp-fields__group');
            drop();
            // A file by its name, not its whole URL; a handle as typed.
            const url = $.trim($row.find('.include-file input').val() || '');
            const name = (url ? url.split(/[?#]/)[0].split('/').filter(Boolean).pop() : $.trim($row.find('input[type="text"]').first().val())) || '…';
            pending = {
                $row: $row,
                index: $list.children('.wowp-fields__group').index($row),
                timer: setTimeout(drop, 10000),
            };
            $row.detach();
            $undo.find('.wowp-attr-undo__text').text(String($undo.data('removed') || '').replace('%s', name));
            $undo.prop('hidden', false);
            $undo.find('.wowp-undo-button').trigger('focus');
        });

        $undo.on('click', '.wowp-undo-button', function () {
            if (!pending) {
                return;
            }
            const removal = pending;
            const $rows = $list.children('.wowp-fields__group');
            if (removal.index < $rows.length) {
                removal.$row.insertBefore($rows.eq(removal.index));
            } else {
                removal.$row.insertBefore($list.children('.btn-add-display'));
            }
            drop();
            removal.$row.find('.wowp-asset-remove').trigger('focus');
        });
    });

    const $assetLists = $('.wowp-assets');
    const $assetsTab = $('label[for="wowp-tab-include"]');
    if ($assetLists.length && window.MutationObserver) {
        const countAssets = function () {
            let total = 0;
            $assetLists.each(function () {
                const rows = $(this).children('.wowp-fields__group').length;
                $(this).children('.wowp-assets__empty').prop('hidden', rows > 0);
                total += rows;
            });
            $assetsTab.toggleClass('has-count', total > 0).attr('data-count', total || null);
        };
        $assetLists.each(function () {
            new MutationObserver(countAssets).observe(this, { childList: true });
        });
    }

    // Dequeue Assets files
    $('#add-dequeue').on('click', function (e) {
        e.preventDefault();

        const temlate = $('#clone-dequeue').clone().html();

        $(temlate).insertBefore('#dequeue .btn-add-display');
    });

    $('#dequeue').on('click', '.dashicons-trash', function () {
        const parent = $(this).closest('.wowp-fields__group');
        $(parent).remove();
    });

    // Add shortcode attributes
    // Also at the foot of the list, where a long list ends.
    $('#add-attribute, #add-attribute-bottom').on('click', function (e) {
        e.preventDefault();
        const temlate = $('#clone-attributes').clone().html();
        const $newRow = $(temlate.trim());
        $newRow.insertBefore('#code-attributes .btn-add-display');
        $newRow.find('.wowp-attr-name input').trigger('focus');
    });

    // Remove at once, with Undo (as in WP Coder Pro): nothing is saved until
    // Save, but a slip used to take the row's values with it. One removal can be
    // undone at a time; the next one (or ten seconds) makes it final.
    const $attrUndo = $('#wowp-attr-undo');
    let pendingRemoval = null;

    const dropPendingRemoval = function () {
        if (pendingRemoval) {
            clearTimeout(pendingRemoval.timer);
            pendingRemoval = null;
        }
        $attrUndo.prop('hidden', true);
    };

    $('#code-attributes').on('click', '.dashicons-trash', function () {
        const $row = $(this).closest('.wowp-fields__group');
        const $list = $row.parent();
        dropPendingRemoval();
        const name = $.trim($row.find('.wowp-attr-name input').val()) || '…';
        pendingRemoval = {
            $row: $row,
            $list: $list,
            index: $list.children('.wowp-fields__group').index($row),
            timer: setTimeout(dropPendingRemoval, 10000),
        };
        $row.detach();
        $attrUndo.find('.wowp-attr-undo__text').text(String($attrUndo.data('removed') || '').replace('%s', name));
        $attrUndo.prop('hidden', false);
        $('#wowp-attr-undo-button').trigger('focus');
    });

    $('#wowp-attr-undo-button').on('click', function () {
        if (!pendingRemoval) {
            return;
        }
        const removal = pendingRemoval;
        const $rows = removal.$list.children('.wowp-fields__group');
        if (removal.index < $rows.length) {
            removal.$row.insertBefore($rows.eq(removal.index));
        } else {
            removal.$row.insertBefore(removal.$list.children('.btn-add-display'));
        }
        dropPendingRemoval();
        removal.$row.find('.dashicons-trash').trigger('focus');
    });

    $('.button-add-img').on('click', function (event) {
        event.preventDefault();
        var upload_button = $(this);
        var img = $(this).data('img');
        var frame;
        event.preventDefault();
        if (frame) {
            frame.open();
            return;
        }
        frame = wp.media();
        frame.on('select', function () {
            // Grab the selected attachment.
            const attachment = frame.state().get('selection').first();
            let url = attachment.attributes.url;
            url = url.replace('-scaled.', '.');
            let alt = attachment.attributes.alt;
            let title = attachment.attributes.title;
            let height = attachment.attributes.height;
            let width = attachment.attributes.width;
            let img = `<img src="${url}" alt="${alt}" loading="lazy" width="${width}" height="${height}">`;
            frame.close();
            const editorElement = $('[data-content="wowp-tab-html-code"]').find('.CodeMirror')[0];
            const editor = editorElement.CodeMirror;
            const doc = editor.getDoc();
            const cursor = doc.getCursor();
            doc.replaceRange(img, cursor);
            editor.focus();

        });
        frame.open();

    });

    $('.button-editor').not('#phpglobalnav').on('click', function (event) {
        event.preventDefault();
        const parent = $(this).closest('.wowp-settins__tabs-content');
        const id = $(this).attr('id');
        let comment;
        switch (id) {
            case 'jsnav':
            case 'phpnav':
                comment = '// NAV: ';
                break;
            case 'htmlnav':
                comment = '<!-- NAV:  -->';
                break;
            case 'cssnav':
                comment = '/* NAV:  */';
                break;
        }

        const editorElement = $(parent).find('.CodeMirror')[0];
        const editor = editorElement.CodeMirror;
        const doc = editor.getDoc();
        const cursor = doc.getCursor();
        doc.replaceRange(comment, cursor);
        editor.focus();
    });

    $('#phpglobalnav').on('click', function (event) {
        event.preventDefault();
        const parent = $(this).closest('.wowp-settings__page');
        let comment = '// NAV: ';
        const editorElement = $(parent).find('.CodeMirror')[0];
        const editor = editorElement.CodeMirror;
        const doc = editor.getDoc();
        const cursor = doc.getCursor();
        doc.replaceRange(comment, cursor);
        editor.focus();
    });

    $('.wowp-copy').on('click', function () {
        const copyText = $(this).closest('.wowp-input-group').find('input')[0];

        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices

        // Copy the text inside the text field
        navigator.clipboard.writeText(copyText.value);

        // Alert the copied text
        alert("Copied the shortcode: " + copyText.value);
    });

    $('.wowp-shortcode-copy').on('click', function () {
        const copyText = $(this).closest('.wowp-field').find('input')[0];

        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices

        // Copy the text inside the text field
        navigator.clipboard.writeText(copyText.value);

        // Alert the copied text
        alert("Copied the shortcode: " + copyText.value);
    });

    $('.quickcode').on('click', function () {
        const editor = $('[data-content="wowp-tab-html-code"] .CodeMirror')[0].CodeMirror;
        if (editor) {
            const text = $(this).data('code');
            const doc = editor.getDoc();
            const cursor = doc.getCursor();
            doc.replaceRange(text, cursor);
            editor.focus();
        }
    });


    // navigator.clipboard only exists in a secure context, so on a plain-http
    // site it is undefined and writeText() throws before anything is copied.
    // Same fallback as WP Coder Pro.
    function wpcoderCopyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }

        return new Promise(function (resolve, reject) {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try {
                if (document.execCommand('copy')) {
                    resolve();
                } else {
                    reject(new Error('execCommand failed'));
                }
            } catch (err) {
                reject(err);
            } finally {
                document.body.removeChild(ta);
            }
        });
    }

    function flashTooltip($element, message, delay) {
        const original = $element.attr('data-tooltip');
        $element.attr('data-tooltip', message);
        setTimeout(function () {
            $element.attr('data-tooltip', original);
        }, delay);
    }

    // Copy
    $('.can-copy').on('click', function () {
        const input = $(this).closest('.wowp-field').find('input');
        const $element = $(this);

        wpcoderCopyText(input.val()).then(function () {
            flashTooltip($element, 'Copied', 1000);
        }).catch(function () {
            // Leave the text selected, so it can still be copied by hand.
            input.trigger('select');
            flashTooltip($element, 'Press Ctrl/Cmd+C', 2500);
        });
    });

    // Snippet editor: the shortcode field shows [wp_code id="N"]; this button
    // copies the form with every attribute, which no longer fits in the field.
    $('.wowp-copy-full').on('click', function () {
        const $button = $(this);
        const full = String($button.data('copy'));

        wpcoderCopyText(full).then(function () {
            flashTooltip($button, 'Copied', 1000);
        }).catch(function () {
            $('#shortcode').val(full).trigger('select');
            flashTooltip($button, 'Press Ctrl/Cmd+C', 2500);
        });
    });

    // The page header names the snippet; keep it in step with the Title field.
    const $pageTitle = $('#wowp-page-title');
    if ($pageTitle.length) {
        $('#title').on('input', function () {
            const value = $.trim($(this).val());
            $pageTitle.text(value !== '' ? value : $pageTitle.data('empty'));
        });
    }

    // Quick Code

    const button_quickcodes = document.getElementById('open-quickcodes');

    if (button_quickcodes) {
        button_quickcodes.addEventListener('click', (e) => {
            e.preventDefault();
            const dialog = document.getElementById('quickcodes-dialog');
            const search = document.getElementById('qc-search');
            if (dialog && typeof dialog.showModal === 'function') {
                dialog.showModal();
                if (search) search.focus();
            }
        });
    }


    // Open on Cmd+K
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('quickcodes-dialog').showModal();
            document.getElementById('qc-search').focus();
        }
    });

// Filter list

    const searchInput = document.getElementById('qc-search');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('#qc-list li').forEach(li => {
                li.style.display = li.textContent.toLowerCase().includes(term) ? 'flex' : 'none';
            });
        });
    }

// Insert at cursor position
    document.querySelectorAll('#qc-list li').forEach(li => {
        li.addEventListener('click', () => {
            const code = li.getAttribute('data-code');
            insertAtCursor(document.querySelector('textarea'), code);
            document.getElementById('quickcodes-dialog').close();
        });
    });

// Helper function
    function insertAtCursor(el, text) {
        const start = el.selectionStart;
        const end = el.selectionEnd;
        el.value = el.value.slice(0, start) + text + el.value.slice(end);
        el.selectionStart = el.selectionEnd = start + text.length;
        el.focus();
    }

    const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
    const shortcut = isMac ? 'Cmd + K' : 'Ctrl + K';
    const quickcode_hotkey = document.querySelector('#quickcode-hotkey');
    if (quickcode_hotkey) {
        quickcode_hotkey.textContent = `(${shortcut})`;
    }


    // Stippets control-checkbox
    $('.wowp-snippet__item > .has-checkbox input[type="checkbox"] ').each(snippet_checkbox).on('click', snippet_checkbox);

    function snippet_checkbox() {
        const expand = $(this).closest('.wowp-snippet__item').find('.wowp-snippet__item-expand');
        if ($(this).is(':checked') && expand) {
            $(expand).removeClass('is-hidden');
        } else {
            $(expand).addClass('is-hidden');
        }
    }

    // Popover

    // ── Customize tabs (Settings::init(), Settings::hidden_tabs()) ─────────────
    // Which tabs to show while empty: the user's choice, kept as a WordPress
    // user setting and applied at once — not snippet data, so no Save and no
    // "Unsaved changes". A tab that holds code, attributes or files is always
    // shown: hidden, its code would still run out of sight.
    const $tabsPopover = $('#popover-box');
    const $tabsToggle = $('#popover-toggle');
    if ($tabsPopover.length && $tabsToggle.length) {
        const tabsSetting = String($tabsPopover.data('setting'));
        let hiddenPref = String($tabsPopover.data('hidden') || '').split(/[_-]/).filter(Boolean);

        const tabHolds = function (file) {
            const $label = $('label[for="wowp-tab-' + file + '"]');
            return $label.hasClass('has-content') || $label.hasClass('has-count');
        };

        const saveTabs = function () {
            if (typeof window.setUserSetting === 'function') {
                // "_", not "-": WordPress drops "-" when it stores the setting.
                window.setUserSetting(tabsSetting, hiddenPref.length ? hiddenPref.join('_') : 'none');
            }
        };

        // onlyShow: content appeared (an AI result, a restored row) — reveal its
        // tab, but never hide the tab someone is working in.
        const applyTabs = function (onlyShow) {
            let hiddenCount = 0;
            $tabsPopover.find('.wowp-tab-toggle').each(function () {
                const $box = $(this);
                const file = String($box.data('file'));
                const locked = tabHolds(file);
                const show = locked || hiddenPref.indexOf(String($box.data('tab'))) === -1;
                const $radio = $('#wowp-tab-' + file);
                const $label = $('label[for="wowp-tab-' + file + '"]');

                $box.prop({checked: show, disabled: locked});
                $box.closest('.wowp-tabs-popover__row').toggleClass('is-locked', locked);

                if (show) {
                    $radio.prop('disabled', false);
                    $label.removeClass('is-hidden');
                } else if (!onlyShow || $label.hasClass('is-hidden')) {
                    const wasOpen = $radio.prop('checked');
                    $radio.prop({checked: false, disabled: true});
                    $label.addClass('is-hidden');
                    if (wasOpen) {
                        $('.wowp-settins__tabs-radio:not(:disabled)').first().prop('checked', true);
                    }
                }
                if ($label.hasClass('is-hidden')) {
                    hiddenCount++;
                }
            });
            const $badge = $('#wowp-hidden-tabs');
            $badge.prop('hidden', hiddenCount === 0).text(String($badge.data('label')).replace('%d', hiddenCount));
        };

        $tabsPopover.on('change', '.wowp-tab-toggle', function () {
            const key = String($(this).data('tab'));
            hiddenPref = hiddenPref.filter(function (k) {
                return k !== key;
            });
            if (!$(this).prop('checked')) {
                hiddenPref.push(key);
            }
            saveTabs();
            applyTabs(false);
        });

        // A user who never chose takes this snippet's old per-snippet flags as
        // their choice, so the tabs they hid stay hidden everywhere.
        if (String($tabsPopover.data('seed')) === '1') {
            saveTabs();
        }

        // Tabs gaining content show themselves (marks are kept live elsewhere).
        if (window.MutationObserver) {
            const observer = new MutationObserver(function () {
                applyTabs(true);
            });
            $('.wowp-settins__tabs-tab').each(function () {
                observer.observe(this, {attributes: true, attributeFilter: ['class', 'data-count']});
            });
        }

        const openTabs = function (open, refocus) {
            $tabsPopover.prop('hidden', !open);
            $tabsToggle.attr('aria-expanded', open ? 'true' : 'false');
            if (open) {
                $tabsPopover.find('.wowp-tab-toggle:not(:disabled)').first().trigger('focus');
            } else if (refocus) {
                $tabsToggle.trigger('focus');
            }
        };

        $tabsToggle.on('click', function (event) {
            event.preventDefault();
            openTabs($tabsPopover.prop('hidden'), false);
        });

        $tabsPopover.on('keydown', function (event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                openTabs(false, true);
            }
        });

        $(document).on('click', function (event) {
            if (!$tabsPopover.prop('hidden') && !$(event.target).closest('.popover-container').length) {
                openTabs(false, false);
            }
        });
    }

    // Attributes tab: the number of attribute rows, kept in step as rows are
    // added and removed (Settings::tab_marks() sets it on load).
    const attrBox = document.getElementById('code-attributes');
    const $attrTab = $('label[for="wowp-tab-attributes"]');
    if (attrBox && $attrTab.length && window.MutationObserver) {
        const countAttributes = function () {
            const count = $(attrBox).children('.wowp-attr-row').length;
            $attrTab.toggleClass('has-count', count > 0).attr('data-count', count || null);
            $('.wowp-attr-empty').prop('hidden', count > 0);
            $('#code-attributes .btn-add-display').prop('hidden', count === 0);
        };
        new MutationObserver(countAttributes).observe(attrBox, { childList: true });
    }

    // The ⁱ hints show on :focus too, but a <sup> never got focus — keyboard
    // users could not read them at all.
    $('sup.wowp-tooltip[data-tooltip]').each(function () {
        if (!this.hasAttribute('tabindex')) {
            this.setAttribute('tabindex', '0');
            this.setAttribute('aria-label', this.getAttribute('data-tooltip'));
        }
    });

    // Snippet note (sidebar.php): keep the folded summary — "Add a note" or
    // "Note", the dot, the first line, the full text on hover — in step with the
    // text, so a note typed and then folded still shows. The field grows with
    // the note, and opening the note puts the cursor in it.
    const $note = $('#note');
    if ($note.length) {
        const $details = $note.closest('.wowp-settings__publish-note');
        const $summary = $details.children('summary');
        const $label = $details.find('.wowp-note__label');

        const fitNote = function () {
            const el = $note[0];
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight + 2, 320) + 'px';
        };

        $note.on('input', function () {
            const text = $note.val();
            const filled = $.trim(text) !== '';
            $details.toggleClass('has-note', filled);
            $details.find('.wowp-note__preview').text(text.split('\n')[0]);
            $label.text(filled ? $label.data('filled') : $label.data('empty'));
            $summary.attr('title', filled ? text : null);
            fitNote();
        });

        $details.on('toggle', function () {
            if (!$details.prop('open')) {
                return;
            }
            fitNote();
            const el = $note[0];
            el.focus();
            el.setSelectionRange(el.value.length, el.value.length);
        });
    }

    // Code editor hints (Settings::editor_hint()): × hides one for good — a
    // WordPress user setting, so the server stops printing it.
    $(document).on('click', '.wowp-editor-hint__close', function () {
        const $hint = $(this).closest('.wowp-editor-hint');
        if (typeof window.setUserSetting === 'function') {
            window.setUserSetting($hint.data('hint'), '1');
        }
        $hint.remove();
    });

});
