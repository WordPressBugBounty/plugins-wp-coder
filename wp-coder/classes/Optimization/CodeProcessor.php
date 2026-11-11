<?php

namespace WPCoder\Optimization;

defined( 'ABSPATH' ) || exit;

/**
 * Applies a snippet's minification/obfuscation params to its code.
 * Single source of truth for FolderManager file writes and the post combiners.
 */
class CodeProcessor {

	public static function css( string $css, array $param ): string {
		if ( ! empty( $param['minified_css'] ) ) {
			$css = CSSMinifier::minify( $css );
		}

		return $css;
	}

	public static function js( string $js, array $param ): string {
		$js = wp_specialchars_decode( $js, ENT_QUOTES );

		$minified = ! empty( $param['minified_js'] ) ? $param['minified_js'] : 'obfuscate';

		if ( $minified === 'obfuscate' ) {
			$packer = new Obfuscator( $js, 'Normal', true, false );
			$js     = $packer->pack();
		} elseif ( $minified === 'minify' ) {
			$packer = new Obfuscator( $js, 'None', true, false );
			$js     = $packer->pack();
		}

		return $js;
	}

}
