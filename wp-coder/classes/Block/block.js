( function( blocks, element, blockEditor, components ) {
    const { registerBlockType } = blocks;
    const { createElement: el, useState, useEffect, useRef } = element;
    const { InspectorControls, useBlockProps, MediaUpload } = blockEditor;
    const { BaseControl, TextControl, TextareaControl, RangeControl, DateTimePicker, PanelBody, Placeholder, Button, Spinner } = components;

    // Types WP Coder Pro edits; their values are kept as they are.
    const FREE_TYPES = [ 'text', 'textarea', 'number', 'range', 'url', 'image', 'color', 'date' ];

    /**
     * A Range's value field: "min:max:step[:default]"; a plain number is a Range
     * saved before limits existed — its default on a 0–100 slider. Same rules as
     * WP Coder Pro's rangeConfig.js and AttributeRange.
     */
    function parseRange( definition ) {
        const text = String( definition || '' ).trim();
        const num = ( v, fallback ) => ( v !== undefined && String( v ).trim() !== '' && ! isNaN( Number( v ) ) ) ? Number( v ) : fallback;

        if ( text.indexOf( ':' ) === -1 ) {
            return { min: 0, max: 100, step: 1, defaultValue: num( text, 0 ) };
        }

        const parts = text.split( ':' );
        const min = num( parts[0], 0 );
        const step = num( parts[2], 1 );

        return { min: min, max: num( parts[1], 100 ), step: step > 0 ? step : 1, defaultValue: num( parts[3], min ) };
    }

    /** What a new block starts with — the attribute's Default value. */
    function initialValue( attr ) {
        if ( attr.type === 'range' ) {
            return String( parseRange( attr.value ).defaultValue );
        }

        return FREE_TYPES.indexOf( attr.type || 'text' ) !== -1 ? ( attr.value || '' ) : '';
    }

    function fieldLabel( attr ) {
        return ( attr.label || '' ).trim() || attr.key.replace( /_/g, ' ' ).replace( /\b\w/g, ( c ) => c.toUpperCase() );
    }

    /**
     * Put the snippet's CSS into the document the preview lives in. The block
     * editor renders its content in an iframe ("editor-canvas"), so a <style> in
     * the admin page's own <head> never reached the preview. Replaced on every
     * reload, so an edited stylesheet shows up too.
     */
    function injectCSS( node, snippetId, css ) {
        const doc = node && node.ownerDocument ? node.ownerDocument : document;
        if ( ! doc || ! doc.head ) {
            return;
        }

        const styleId = 'wpcoder-style-' + snippetId;
        let style = doc.getElementById( styleId );

        if ( ! css ) {
            if ( style ) {
                style.remove();
            }
            return;
        }

        if ( ! style ) {
            style = doc.createElement( 'style' );
            style.id = styleId;
            doc.head.appendChild( style );
        }
        style.textContent = css;
    }

    function attrField( attr, value, onChange ) {
        const label = fieldLabel( attr );

        switch ( attr.type || 'text' ) {
            case 'textarea':
                return el( TextareaControl, { key: attr.key, label: label, value: value, onChange: onChange } );

            case 'number':
                return el( TextControl, { key: attr.key, label: label, type: 'number', value: value, onChange: onChange } );

            case 'url':
                return el( TextControl, { key: attr.key, label: label, type: 'url', value: value, onChange: onChange, placeholder: 'https://example.com' } );

            case 'color':
                return el( TextControl, { key: attr.key, label: label, type: 'color', value: value || '#000000', onChange: onChange } );

            case 'range': {
                const range = parseRange( attr.value );
                const current = value === '' || isNaN( Number( value ) ) ? range.defaultValue : Number( value );
                return el( RangeControl, {
                    key: attr.key, label: label, value: current, min: range.min, max: range.max, step: range.step,
                    onChange: ( val ) => onChange( val === undefined ? '' : String( val ) )
                } );
            }

            case 'date':
                // BaseControl gives the label the same look as the other fields.
                return el( BaseControl, { key: attr.key, label: label, id: 'wpcoder-attr-' + attr.key },
                    el( DateTimePicker, { currentDate: value || null, onChange: ( val ) => onChange( val || '' ) } )
                );

            case 'image':
                return el( BaseControl, { key: attr.key, label: label, id: 'wpcoder-attr-' + attr.key }, [
                    value && el( 'img', { key: 'i', src: value, alt: '', style: { maxWidth: '100%', maxHeight: '160px', display: 'block', marginBottom: '8px', border: '1px solid #ddd', padding: '4px' } } ),
                    el( MediaUpload, {
                        key: 'm',
                        allowedTypes: [ 'image' ],
                        onSelect: ( media ) => onChange( media && media.url ? media.url : '' ),
                        render: ( { open } ) => el( 'div', { style: { display: 'flex', gap: '8px' } }, [
                            el( Button, { key: 'o', variant: 'secondary', onClick: open }, value ? 'Change Image' : 'Select Image' ),
                            value && el( Button, { key: 'r', isDestructive: true, onClick: () => onChange( '' ) }, 'Remove' )
                        ] )
                    } )
                ] );

            case 'text':
                return el( TextControl, { key: attr.key, label: label, value: value, onChange: onChange } );

            default:
                // A type set up in WP Coder Pro (repeater JSON, rich text in base64…):
                // editing it as plain text would break it, so it is shown, not edited.
                return el( BaseControl, { key: attr.key, label: label, id: 'wpcoder-attr-' + attr.key, help: 'Edited in WP Coder Pro (' + attr.type + '). Its value is kept.' } );
        }
    }

    registerBlockType( 'wpcoder/shortcode', {
        // 3: the edit renders its own wrapper (useBlockProps) and works in the
        // iframed canvas. Without it the block was version 1: the editor wrapped
        // it a second time, and on WordPress 6.9 and older one v1 block left
        // every post on the site in the non-iframe editor. Also in register_block().
        apiVersion: 3,
        title: 'WP Coder Snippet',
        icon: {
            src: el(
                'svg',
                {
                    xmlns: "http://www.w3.org/2000/svg",
                    width: 20,
                    height: 20,
                    viewBox: "0 0 512 512"
                },
                el(
                    'g',
                    {},
                    el('path', {
                        fill: "#2b7fff",
                        fillRule: "evenodd",
                        stroke: "none",
                        d: "M 496 101 C 496 128.614227 473.614227 151 446 151 C 418.385773 151 396 128.614227 396 101 C 396 73.385773 418.385773 51 446 51 C 473.614227 51 496 73.385773 496 101 Z"
                    }),
                    el(
                        'g',
                        {},
                        el('path', {
                            fill: "#212121",
                            stroke: "none",
                            d: "M 245.714279 232.714294 L 74.285713 61.285736 C 60.571426 47.571411 40 47.571411 26.285713 61.285736 C 12.571427 75 12.571427 95.571442 26.285713 109.285706 L 173.714279 256.714294 L 26.285713 404.142853 C 12.571427 417.857147 12.571427 438.428589 26.285713 452.142853 C 33.142857 459 40 462.428558 50.285713 462.428558 C 60.571426 462.428558 67.428574 459 74.285713 452.142853 L 245.714279 280.714294 C 259.428589 267 259.428589 246.428558 245.714279 232.714294 Z"
                        }),
                        el('path', {
                            fill: "#212121",
                            fillRule: "evenodd",
                            stroke: "none",
                            d: "M 256 431 C 256 448.673096 270.326874 463 288 463 L 464 463 C 481.673096 463 496 448.673096 496 431 L 496 429 C 496 411.326904 481.673096 397 464 397 L 288 397 C 270.326874 397 256 411.326904 256 429 Z"
                        })
                    )
                )
            )
        },
        category: 'widgets',
        attributes: {
            id: { type: 'string', default: '' },
            extraAttrs: { type: 'array', default: [] }
        },
        supports: {
            html: false
        },

        edit: ( props ) => {
            // Merged with the block's own ref by useBlockProps(); used to find the
            // document the preview renders in (see injectCSS()).
            const previewRef = useRef( null );
            const blockProps = useBlockProps( { ref: previewRef } );
            const [ localId, setLocalId ] = useState( props.attributes.id || '' );
            const [ preview, setPreview ] = useState( '' );
            const [ loading, setLoading ] = useState( false );
            const [ availableAttrs, setAvailableAttrs ] = useState( [] );

            const loadPreview = () => {
                if ( ! localId ) {
                    setPreview('<em>Please enter Snippet ID</em>');
                    return;
                }

                props.setAttributes( { id: localId } );
                setLoading( true );

                wp.apiFetch({
                    path: '/wpcoder/v1/preview',
                    method: 'POST',
                    data: { id: localId, extraAttrs: props.attributes.extraAttrs }
                }).then((response) => {
                    setPreview( response.html || '<em>No preview</em>' );
                    setLoading( false );

                    injectCSS( previewRef.current, localId, response.css );

                });
            };

            useEffect( () => {
                if ( ! props.attributes.id ) return;

                setLoading( true );
                wp.apiFetch({
                    path: '/wpcoder/v1/preview',
                    method: 'POST',
                    data: {
                        id: localId,
                        extraAttrs: props.attributes.extraAttrs
                    }
                }).then((response) => {
                    setPreview( response.html || '<em>No preview</em>' );
                    setLoading( false );

                    injectCSS( previewRef.current, localId, response.css );

                });

                wp.apiFetch({
                    path: '/wpcoder/v1/attributes',
                    method: 'POST',
                    data: { id: props.attributes.id }
                }).then((attrs) => {
                    setAvailableAttrs(attrs || []);
                    if (attrs.length > 0 && props.attributes.extraAttrs.length === 0) {
                        const init = attrs.map(a => ({ key: a.key, value: initialValue(a) }));
                        props.setAttributes({ extraAttrs: init });
                    }
                });


            }, [ props.attributes.id ] )

            if ( ! props.attributes.id ) {
                return el( Placeholder,
                    {
                        label: 'WP Coder Snippet',
                        instructions: 'Enter snippet ID and click Load'
                    },
                    el( 'div', {
                        style: {
                            display: 'flex',
                            gap: '10px',
                            alignItems: 'baseline',
                            maxWidth: '400px'
                        }
                    }, [
                        el( TextControl, {
                            label: '',
                            placeholder: 'Snippet ID',
                            value: localId,
                            onChange: ( value ) => setLocalId( value ),
                            style: { flex: '1' }
                        }),
                        el( Button, {
                            isPrimary: true,
                            onClick: loadPreview
                        }, 'Load Preview')
                    ])
                );
            }

            return el( 'div', blockProps, [
                el( InspectorControls, {},
                    el( PanelBody, { title: 'WP Coder Settings' }, [
                        el( TextControl, {
                            label: 'Snippet ID',
                            value: localId,
                            onChange: ( value ) => setLocalId( value )
                        }),
                        el( 'div', { style: { display: 'flex', gap: '10px', marginTop: '10px' } }, [
                            el( Button, {
                                isPrimary: true,
                                onClick: loadPreview
                            }, 'Reload Preview'),
                            props.attributes.id && el( Button, {
                                isSecondary: true,
                                href: `${ wpcoderBlockData.editBaseUrl }&action=update&id=${ props.attributes.id }`,
                                target: '_blank'
                            }, 'Edit Snippet')
                        ]),
                        availableAttrs.length > 0 && el( 'div', { style: { marginTop: '15px' } },
                            el( PanelBody, { title: 'Shortcode Attributes', initialOpen: true },
                                availableAttrs.map( ( attr ) => {
                                    const stored = props.attributes.extraAttrs.find( a => a.key === attr.key );
                                    return attrField( attr, stored ? ( stored.value || '' ) : '', ( val ) => {
                                        // Upsert: an attribute added to the snippet after this
                                        // block was created has no entry yet.
                                        const rest = props.attributes.extraAttrs.filter( a => a.key !== attr.key );
                                        props.setAttributes( { extraAttrs: rest.concat( [ { key: attr.key, value: val } ] ) } );
                                    } );
                                } )
                            )
                        )
                    ])
                ),
                loading
                    ? el( Spinner, {} )
                    : el( 'div', {
                        className: 'wpcoder-preview',
                        dangerouslySetInnerHTML: { __html: preview }
                    })
            ] );
        },

        save: () => null
    } );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components );