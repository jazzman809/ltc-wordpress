/**
 * Editor side of the "Lottery results" and "Next lottery draw" blocks.
 *
 * Plain JavaScript on WordPress's globals, no build step. The preview is the
 * real server-rendered output (ServerSideRender), so what the editor shows is
 * what the page shows. Strings and the game list come from PHP
 * (window.lotalyticsEditor), already translated.
 *
 * @package Lotalytics\Results
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var TextControl = wp.components.TextControl;
	var RangeControl = wp.components.RangeControl;
	var ToggleControl = wp.components.ToggleControl;
	var ServerSideRender = wp.serverSideRender;

	var data = window.lotalyticsEditor || { games: [], defaultGame: 'powerball', strings: {} };
	var s = data.strings || {};

	function gameControl( attributes, setAttributes ) {
		var value = attributes.game || data.defaultGame;
		if ( ! data.games || ! data.games.length ) {
			return el( TextControl, {
				label: s.slug,
				help: s.noGames,
				value: value,
				onChange: function ( v ) {
					setAttributes( { game: v } );
				},
			} );
		}
		return el( SelectControl, {
			label: s.game,
			value: value,
			options: data.games.map( function ( g ) {
				return { label: g.name, value: g.slug };
			} ),
			onChange: function ( v ) {
				setAttributes( { game: v } );
			},
		} );
	}

	function preview( name, attributes, blockProps ) {
		return el(
			'div',
			blockProps,
			el( ServerSideRender, { block: name, attributes: attributes } )
		);
	}

	wp.blocks.registerBlockType( 'lotalytics/latest-results', {
		edit: function ( props ) {
			var a = props.attributes;
			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: s.settings, initialOpen: true },
						gameControl( a, props.setAttributes ),
						el( RangeControl, {
							label: s.draws,
							min: 1,
							max: 5,
							value: a.draws,
							onChange: function ( v ) {
								props.setAttributes( { draws: v } );
							},
						} ),
						el( ToggleControl, {
							label: s.showTitle,
							checked: a.showTitle,
							onChange: function ( v ) {
								props.setAttributes( { showTitle: v } );
							},
						} ),
						el( ToggleControl, {
							label: s.compact,
							checked: a.compact,
							onChange: function ( v ) {
								props.setAttributes( { compact: v } );
							},
						} )
					)
				),
				preview( 'lotalytics/latest-results', a, useBlockProps() )
			);
		},
		save: function () {
			return null;
		},
	} );

	wp.blocks.registerBlockType( 'lotalytics/next-draw', {
		edit: function ( props ) {
			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el( PanelBody, { title: s.settings, initialOpen: true }, gameControl( props.attributes, props.setAttributes ) )
				),
				preview( 'lotalytics/next-draw', props.attributes, useBlockProps() )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
