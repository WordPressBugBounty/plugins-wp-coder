<?php

namespace WPCoder\Dashboard;

defined( 'ABSPATH' ) || exit;

// Exit if accessed directly.
use WPCoder\Optimization\CodeProcessor;
use WPCoder\WPCoder;

class FolderManager {

	public static function create(): void {
		$basedir = self::path_upload_dir();
		$index_file = $basedir . 'index.php';

		if ( ! file_exists( $basedir ) ) {
			wp_mkdir_p( $basedir );
		}

		if ( is_dir( $basedir ) && wp_is_writable( $basedir ) && ! file_exists( $index_file ) ) {
			file_put_contents($index_file, '<?php // Silence is golden.');
		}
	}


	/**
	 * Write a file into the plugin's uploads folder.
	 *
	 * Creates the folder when it is missing — the activation hook may never have
	 * created it (uploads not there yet, folder deleted, site migrated without
	 * it) — and answers false instead of a PHP warning on every request when it
	 * still cannot be written. AdminNotices reports that state on the plugin's
	 * pages, so a failed write is not silent.
	 *
	 * @return int|false Bytes written, or false.
	 */
	public static function write( string $path, string $content ) {
		$dir = dirname( $path );
		if ( ! is_dir( $dir ) ) {
			self::create();
		}

		if ( ! is_dir( $dir ) || ! wp_is_writable( $dir ) || ( file_exists( $path ) && ! wp_is_writable( $path ) ) ) {
			return false;
		}

		$written = file_put_contents( $path, $content );

		// A PHP file's cached safety verdict is checked against its mtime and size;
		// an edit within the same second that keeps the size would reuse the old one.
		if ( $written !== false && substr( $path, -4 ) === '.php' ) {
			\WPCoder\Publisher\CodeSafety::forget( $path );
		}

		return $written;
	}

	/** Whether the uploads folder exists (it is created when possible) and can be written. */
	public static function is_writable(): bool {
		$dir = self::path_upload_dir();
		if ( ! is_dir( $dir ) ) {
			self::create();
		}

		return is_dir( $dir ) && wp_is_writable( $dir );
	}

	public static function path_upload_dir(): string {
		$upload = wp_upload_dir();

		return $upload['basedir'] . '/' . WPCoder::FOLDER . '/';
	}

	public static function path_upload_url(): string {
		$upload = wp_upload_dir();

		return $upload['baseurl'] . '/' . WPCoder::FOLDER . '/';
	}

	/**
	 * Whether a snippet row is usable as an asset source: get_data_by_id()
	 * answers false or null for a missing row, which used to write an empty
	 * "style-.css" / "script-.js".
	 */
	private static function is_usable( $result ): bool {
		return is_object( $result ) && ! empty( $result->id );
	}

	public static function get_style_url( $result ): string {
		if ( ! self::is_usable( $result ) ) {
			return '';
		}

		$path_style = self::path_upload_dir() . 'style-' . $result->id . '.css';

		if ( ! file_exists( $path_style ) ) {
			// Read the row's own param: this used an undefined $data, so a file
			// recreated here was never minified.
			$param = ! empty( $result->param ) ? maybe_unserialize( $result->param ) : [];
			self::write( $path_style, CodeProcessor::css( (string) ( $result->css_code ?? '' ), is_array( $param ) ? $param : [] ) );
		}

		// Not written (folder not writable): a URL to it would only be a 404.
		if ( ! file_exists( $path_style ) ) {
			return '';
		}

		return self::path_upload_url() . 'style-' . $result->id . '.css';
	}

	public static function get_js_url( $result ): string {
		if ( ! self::is_usable( $result ) ) {
			return '';
		}

		$path_js = self::path_upload_dir() . 'script-' . $result->id . '.js';

		if ( ! file_exists( $path_js ) ) {
			// Same processing update_script() applies, so a recreated file is not
			// served raw while the snippet says Minify or Obfuscate.
			$param = ! empty( $result->param ) ? maybe_unserialize( $result->param ) : [];
			self::write( $path_js, CodeProcessor::js( (string) ( $result->js_code ?? '' ), is_array( $param ) ? $param : [] ) );
		}

		if ( ! file_exists( $path_js ) ) {
			return '';
		}

		return self::path_upload_url() . 'script-' . $result->id . '.js';
	}

	public static function update_style( $data, $id ) {
		$path_style = self::path_upload_dir() . 'style-' . $id . '.css';
		$param      = ! empty( $data['param'] ) ? maybe_unserialize( $data['param'] ) : [];

		return self::write( $path_style, CodeProcessor::css( (string) $data['css_code'], is_array( $param ) ? $param : [] ) );
	}

	public static function update_script( $data, $id ) {
		if ( ! is_numeric( $id ) ) {
			return false;
		}

		$path_js = self::path_upload_dir() . 'script-' . $id . '.js';
		$param   = ! empty( $data['param'] ) ? maybe_unserialize( $data['param'] ) : [];

		return self::write( $path_js, CodeProcessor::js( (string) $data['js_code'], is_array( $param ) ? $param : [] ) );
	}

	public static function code_path( $result ): string {

		$path = self::path_upload_dir() . 'wp-code-' . $result->id . '.php';

		if ( ! file_exists( $path ) ) {
			$content = "<?php\n\n  defined( 'ABSPATH' ) || exit;\n";
			$content .= self::strip_open_tag( (string) $result->php_code );
			self::write( $path, $content );
		}

		return self::path_upload_dir() . 'wp-code-' . $result->id . '.php';
	}

	/**
	 * PHP code without a leading `<?php` — for a snippet's PHP tab and Global PHP.
	 *
	 * Both files already open PHP (update_code(), SaveGlobal::send()), so a
	 * second `<?php` is a parse error and none of the code runs — and code
	 * copied from a tutorial or a functions.php usually starts with one.
	 * Removing it changes nothing else: the code is in PHP mode either way.
	 * Blank lines after the tag go with it. Only the tag at the very start: a
	 * `?>` … `<?php` further down is valid as written.
	 */
	public static function strip_open_tag( string $code ): string {
		return (string) preg_replace( '/^(?:\xEF\xBB\xBF)?\s*<\?php(?:\s*\R|[ \t]+|$)/i', '', $code );
	}

	public static function update_code( $data, $id) {

		if ( ! is_numeric( $id ) ) {
			return false;
		}

		$path = self::path_upload_dir() . 'wp-code-' . $id . '.php';

		$content = "<?php\n\n  defined( 'ABSPATH' ) || exit;\n";
		// Also here, not only in save_settings(): an import writes the file
		// without passing it.
		$content .= self::strip_open_tag( (string) ( $data['php_code'] ?? '' ) );

		return self::write( $path, $content );
	}

	/**
	 * Bring a snippet's cached files in line with its code.
	 *
	 * The PHP file is rewritten even when the code was cleared: PHPIncludes runs
	 * wp-code-{id}.php for every row with an include mode, so a file left behind
	 * kept executing the deleted code on every request. Cleared CSS or JS removes
	 * its file. Only a key present in $data counts as cleared, so a partial array
	 * cannot remove a file by omission.
	 */
	public static function update_files( $data, $id ): void {
		if ( ! empty( $data['css_code'] ) ) {
			self::update_style( $data, $id );
		} elseif ( array_key_exists( 'css_code', $data ) ) {
			self::remove_file( 'style-' . absint( $id ) . '.css' );
		}

		if ( ! empty( $data['js_code'] ) ) {
			self::update_script( $data, $id );
		} elseif ( array_key_exists( 'js_code', $data ) ) {
			self::remove_file( 'script-' . absint( $id ) . '.js' );
		}

		$php_path = self::path_upload_dir() . 'wp-code-' . absint( $id ) . '.php';
		if ( ! empty( $data['php_code'] ) || ( array_key_exists( 'php_code', $data ) && file_exists( $php_path ) ) ) {
			self::update_code( $data, $id );
		}
	}

	/**
	 * Remove every cached file of a deleted snippet. Left behind, wp-code-{id}.php
	 * kept the snippet's PHP on disk, and an import that reused the id inherited
	 * its stale CSS/JS.
	 */
	public static function delete_files( int $id ): void {
		if ( $id < 1 ) {
			return;
		}

		self::remove_file( 'style-' . $id . '.css' );
		self::remove_file( 'script-' . $id . '.js' );

		$php_path = self::path_upload_dir() . 'wp-code-' . $id . '.php';
		if ( file_exists( $php_path ) ) {
			wp_delete_file( $php_path );
		}
	}

	private static function remove_file( string $name ): void {
		if ( ! preg_match( '/^(style|script)-\d+\.(css|js)$/', $name ) ) {
			return;
		}

		$path = self::path_upload_dir() . $name;
		if ( file_exists( $path ) ) {
			wp_delete_file( $path );
		}
	}
}
