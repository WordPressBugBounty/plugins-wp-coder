'use strict';

jQuery(function ($) {


    // All checkboxes
    $('.wowp-settings').on('click', 'input:checkbox', function () {
        checkboxchecked(this);
    });

    function checkboxchecked(el) {
        if ($(el).prop('checked')) {
            $(el).next('input[type="hidden"]').val('1');
        } else {
            $(el).next('input[type="hidden"]').val('');
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
        const parent = $(this).closest('.wowp-fields-group');
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
        const parent = $(this).closest('.wowp-fields-group');
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
        const parent = $(this).closest('.wowp-fields-group');
        $(parent).remove();
    });

    $('#display-rules .display-option select').each(displayRules);
    $('#display-rules').on('change', '.display-option select', displayRules);

    function displayRules() {
        let type = $(this).val();
        const parent = $(this).closest('.wowp-fields-group');
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

        const temlate = $('#clone-includes').clone().html();

        $(temlate).insertBefore('#includes-files .btn-add-display');
        $('#includes-files .display-option select').each(includeFiles);
    });

    $('#includes-files').on('click', '.dashicons-trash', function () {
        const parent = $(this).closest('.wowp-fields-group');
        $(parent).remove();
    });

    $('#includes-files .display-option select').each(includeFiles);
    $('#includes-files').on('change', '.display-option select', includeFiles);

    function includeFiles() {
        let type = $(this).val();
        const parent = $(this).closest('.wowp-fields-group');
        $(parent).find('.js-attr').addClass('is-hidden');

        console.log(type);

        switch (type) {
            case 'css':
                $(parent).find('.js-attr').addClass('is-hidden');
                break;
            case 'js':
                $(parent).find('.js-attr').removeClass('is-hidden');
                break;
        }

    }

    // Dequeue Assets files
    $('#add-dequeue').on('click', function (e) {
        e.preventDefault();

        const temlate = $('#clone-dequeue').clone().html();

        $(temlate).insertBefore('#dequeue .btn-add-display');
    });

    $('#dequeue').on('click', '.dashicons-trash', function () {
        const parent = $(this).closest('.wowp-fields-group');
        $(parent).remove();
    });

    // Add shortcode attributes
    $('#add-attribute').on('click', function (e) {
        e.preventDefault();
        const temlate = $('#clone-attributes').clone().html();
        $(temlate).insertBefore('#code-attributes .btn-add-display');
    });

    $('#code-attributes').on('click', '.dashicons-trash', function () {
        const parent = $(this).closest('.wowp-fields-group');
        $(parent).remove();
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
            const editorElement = $('[data-content="html-code"]').find('.CodeMirror')[0];
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
        const parent = $(this).closest('.tab-content');
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
        const parent = $(this).closest('fieldset');
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

});