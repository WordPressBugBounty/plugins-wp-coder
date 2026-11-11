<?php

namespace WPCoder\Publisher;

defined( 'ABSPATH' ) || exit;

/**
 * The forbidden-function check for user PHP — a copy of WP Coder Pro's
 * (WPCoderPro\Publisher\CodeSafety): the same list, the same scanner and the
 * same cached verdicts (one option), so a snippet behaves the same in both.
 *
 * Every include of snippet PHP asks scan_file() first:
 *   - WOWP_Public::get_content() — the PHP tab of a rendered snippet.
 *   - PHPIncludes                — "PHP include" files and Global PHP.
 *
 * Free used to include both unchecked.
 */
class CodeSafety {

	/**
	 * Functions that are never allowed in user PHP. `eval` and backticks are
	 * detected separately (as language constructs), but listing `eval` here keeps
	 * the blocklist readable and lets the settings page display the full set.
	 */
	const FORBIDDEN_FUNCS = [
		'eval',
		'exec',
		'system',
		'shell_exec',
		'passthru',
		'popen',
		'proc_open',
		'pcntl_exec',
		'assert',
		'create_function',
		'base64_decode',
	];

	/**
	 * Scan PHP source for forbidden functions and the backtick shell operator.
	 *
	 * Accepts either bare snippet code or a full PHP file that already opens with
	 * `<?php` (the file is scanned as-is so token offsets are not shifted).
	 *
	 * A forbidden name only counts as a hit when it is actually *called* (followed
	 * by `(`), so using one as a plain string — e.g. `$fn = 'eval';` — stays safe.
	 *
	 * @param string $code PHP source to inspect.
	 *
	 * @return array{0: bool, 1: string[]} [ is_safe, hits ].
	 */
	public static function scan( string $code ): array {
		$hits = [];

		// Accept both bare code and a full PHP file that already opens with <?php.
		$source = ( strpos( ltrim( $code ), '<?php' ) === 0 ) ? $code : "<?php\n" . $code;
		$tokens = token_get_all( $source );

		// Backtick shell operator (shell_exec shorthand).
		if ( preg_match( '/`.+`/s', $code ) ) {
			$hits[] = 'backticks';
		}

		foreach ( $tokens as $i => $tok ) {
			if ( ! is_array( $tok ) ) {
				continue;
			}

			// eval is a language construct, tokenized as T_EVAL (not T_STRING).
			if ( $tok[0] === T_EVAL ) {
				$hits[] = 'eval()';
				continue;
			}

			if ( $tok[0] !== T_STRING ) {
				continue;
			}

			$name = strtolower( $tok[1] );
			if ( ! in_array( $name, self::FORBIDDEN_FUNCS, true ) ) {
				continue;
			}

			// Only a hit when it is a call: the next non-whitespace token is "(".
			$j = $i + 1;
			while ( isset( $tokens[ $j ] ) && is_array( $tokens[ $j ] ) && $tokens[ $j ][0] === T_WHITESPACE ) {
				$j++;
			}
			$next = $tokens[ $j ] ?? null;
			if ( $next === '(' || ( is_array( $next ) && $next[0] === '(' ) ) {
				$hits[] = $name . '()';
			}
		}

		return [ empty( $hits ), $hits ];
	}

	/** Per-file verdicts of scan_file(), keyed by basename. Not autoloaded. */
	public const FILE_CACHE_OPTION = 'wow_coder_php_safe_cache';

	/**
	 * scan() for a PHP file, with the verdict cached per file and checked against
	 * its mtime and size, so token_get_all() runs only when the file changes.
	 * FolderManager::write() drops the entry of a file it writes, for an edit that
	 * keeps both (same size, same second).
	 *
	 * Every place that includes snippet PHP asks this first (see the class).
	 *
	 * @return array{0: bool|null, 1: string[]} [ is_safe, hits ]; is_safe is null when unreadable.
	 */
	public static function scan_file( string $path ): array {
		clearstatcache( true, $path );
		$mtime = @filemtime( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$size  = @filesize( $path );  // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( $mtime === false || $size === false ) {
			return [ null, [] ];
		}

		$key       = basename( $path );
		$signature = $mtime . ':' . $size;
		$cache     = get_option( self::FILE_CACHE_OPTION, [] );
		if ( ! is_array( $cache ) ) {
			$cache = [];
		}

		if ( isset( $cache[ $key ]['sig'] ) && $cache[ $key ]['sig'] === $signature ) {
			return [ (bool) $cache[ $key ]['safe'], (array) $cache[ $key ]['hits'] ];
		}

		$content = @file_get_contents( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( $content === false ) {
			return [ null, [] ];
		}

		[ $safe, $hits ] = self::scan( $content );

		$cache[ $key ] = [
			'sig'  => $signature,
			'safe' => $safe,
			'hits' => $hits,
		];
		update_option( self::FILE_CACHE_OPTION, $cache, false );

		return [ $safe, $hits ];
	}

	/**
	 * Drop a file's cached verdict, or every one when $path is null (Developer
	 * Tools' "Reset Safety Cache").
	 */
	public static function forget( ?string $path = null ): void {
		if ( $path === null ) {
			delete_option( self::FILE_CACHE_OPTION );

			return;
		}

		$cache = get_option( self::FILE_CACHE_OPTION, [] );
		$key   = basename( $path );
		if ( is_array( $cache ) && isset( $cache[ $key ] ) ) {
			unset( $cache[ $key ] );
			update_option( self::FILE_CACHE_OPTION, $cache, false );
		}
	}
}
