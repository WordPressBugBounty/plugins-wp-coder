<?php

namespace WPCoder\Snippets;

use WPCoder\Dashboard\Field;
use WPCoder\Dashboard\FieldHelper;
use WPCoder\WPCoder;

class SnippetManager {

	public static function init(): void {
		self::send();

		$options = get_option( '_wp_coder_snippets', [] );

		// Group keys double as the page filename and the tab icon class, so they
		// stay put even where the label changed ('optimization' → "Performance").
		$snippets = [
			'content'      => __( 'Editor & Content', 'wp-coder' ),
			'admin'        => __( 'Admin Interface', 'wp-coder' ),
			'login'        => __( 'Login & Users', 'wp-coder' ),
			'media'        => __( 'Media & Embeds', 'wp-coder' ),
			'comments'     => __( 'Comments & Feedback', 'wp-coder' ),
			'security'     => __( 'Security', 'wp-coder' ),
			'optimization' => __( 'Performance', 'wp-coder' ),
			'core'         => __( 'Core & System', 'wp-coder' ),
		];

		?>
        <div class="wowp-settings wowp-snippets">
            <form method="post">

                <div class="wowp-snippets__search">
                    <span class="icon icon-crosshair wowp-snippets__search-icon" aria-hidden="true"></span>
                    <input type="search" id="wowp-snippet-search"
                           class="wowp-snippets__search-input"
                           autocomplete="off"
                           placeholder="<?php esc_attr_e( 'Search snippets by name or description…', 'wp-coder' ); ?>"
                           aria-label="<?php esc_attr_e( 'Search snippets', 'wp-coder' ); ?>">
                    <span class="wowp-snippets__search-count" id="wowp-snippet-search-count" role="status" aria-live="polite"></span>
                </div>

                <div class="wowp-snippets__tabs" id="wowp-snippets-tabs">
					<?php
					// A saved tab that no longer exists (a renamed or removed group)
					// would leave every radio unchecked, hiding all the panels.
					$saved_tab = $options['snippet_tab'] ?? '';
					$tab       = isset( $snippets[ $saved_tab ] ) ? $saved_tab : 'content';

					foreach ( $snippets as $key => $value ) {
						echo '<input type="radio" class="wowp-snippets__tabs-radio" name="wp_coder_snippet[snippet_tab]" value="' . esc_attr( $key ) . '" id="wowp-tab-' . esc_attr( $key ) . '" ' . checked( $tab, $key, false ) . '>';
					}
					echo '<div class="wowp-snippets__tabs-labels">';
					foreach ( $snippets as $key => $value ) {
						echo '<label  class="wowp-snippets__tabs-tab" for="wowp-tab-' . esc_attr( $key ) . '"><span class="icon icon-' . esc_attr( $key ) . '"></span>' . esc_html( $value ) . '</label>';
					}
					echo '</div>';
					foreach ( $snippets as $key => $value ) {

						echo '<div class="wowp-snippets__tabs-content" data-content="wowp-tab-' . esc_attr( $key ) . '">';

						// Isolate each group: a runtime error in one page must not blank
						// that tab AND every tab rendered after it in this loop.
						try {
							require plugin_dir_path( __FILE__ ) . 'pages/' . $key . '.php';
						} catch ( \Throwable $e ) {
							error_log( '[WP-Coder] Snippet group "' . $key . '" failed to load: ' . $e->getMessage() );
							echo '<div class="notice notice-error inline"><p>'
								. esc_html__( 'This snippet group could not be loaded:', 'wp-coder' ) . ' '
								. esc_html( $e->getMessage() )
								. '</p></div>';
						}

						echo '<input type="submit" name="submit" class="wowp-button button button-dark button-hero" value="' . esc_attr__( 'Save', 'wp-coder' ) . '">';
						echo '</div>';
					}

					?>
                </div>
				<?php
				wp_nonce_field( WPCoder::PREFIX . '_snippets_action', WPCoder::PREFIX . '_save_snippet' );
				?>
            </form>

        </div>
		<?php
		self::search_script();
	}

	/**
	 * Client-side filter for the snippet list.
	 *
	 * Every tab's markup is already in the DOM (the tabs are CSS-only, inactive
	 * panels are just parked off-screen), so one pass can search all categories at
	 * once without any request.
	 *
	 * Matches are hidden with a class, never detached: the whole page is one form
	 * and saving replaces the entire option, so an input removed from the DOM would
	 * silently switch that snippet off. A `display:none` control still submits.
	 */
	private static function search_script(): void {
		?>
        <script>
            (function () {
                var input = document.getElementById('wowp-snippet-search');
                var tabs = document.getElementById('wowp-snippets-tabs');
                var count = document.getElementById('wowp-snippet-search-count');

                if (!input || !tabs) {
                    return;
                }

                var sections = Array.prototype.slice.call(
                    tabs.querySelectorAll('.wowp-snippets__tabs-content')
                );

                function haystack(item) {
                    if (!item.dataset.searchText) {
                        item.dataset.searchText = (item.textContent || '')
                            .toLowerCase().replace(/\s+/g, ' ').trim();
                    }
                    return item.dataset.searchText;
                }

                function filter() {
                    var query = input.value.trim().toLowerCase();
                    var searching = query !== '';
                    var found = 0;
                    var lastHit = null;

                    tabs.classList.toggle('is-searching', searching);

                    sections.forEach(function (section) {
                        var hits = 0;

                        Array.prototype.forEach.call(
                            section.querySelectorAll('.wowp-snippet__item'),
                            function (item) {
                                var match = !searching || haystack(item).indexOf(query) !== -1;
                                item.classList.toggle('is-search-hidden', !match);
                                if (match) {
                                    hits++;
                                }
                            }
                        );

                        section.classList.toggle('is-search-empty', searching && hits === 0);

                        if (searching && hits > 0) {
                            lastHit = section;
                        }

                        found += hits;
                    });

                    // Each panel carries its own Save button; while results span
                    // several categories keep only the last one.
                    sections.forEach(function (section) {
                        var button = section.querySelector('.wowp-button[type="submit"]');
                        if (button) {
                            button.classList.toggle('is-search-hidden', searching && section !== lastHit);
                        }
                    });

                    if (count) {
                        count.textContent = searching
                            ? (found > 0
                                ? found + <?php echo wp_json_encode( ' ' . __( 'found', 'wp-coder' ) ); ?>
                                : <?php echo wp_json_encode( __( 'Nothing found', 'wp-coder' ) ); ?>)
                            : '';
                        count.classList.toggle('is-empty', searching && found === 0);
                    }
                }

                input.addEventListener('input', filter);

                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && input.value !== '') {
                        event.preventDefault(); // Don't let the browser clear it silently.
                        input.value = '';
                        filter();
                    }
                });
            })();
        </script>
		<?php
	}

	private static function create_options( $settings ) {
		$options = get_option( '_wp_coder_snippets', [] );
		echo '<div class="wowp-snippet__list">';
		$i = 1;
		foreach ( $settings as $name => $args ) {
			$checked = array_key_exists( $name, $options ) ? 'checked' : ''; ?>
            <div class="wowp-snippet__item">
                <div class="wowp-snippet__item-header">
                    <label for="wp_coder_snippet-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $args[0] ); ?></label>
                    <p class="wowp-snippet__item-description"><?php echo esc_html( $args[1] ); ?></p>
                </div>
                <div class="wowp-field has-checkbox">
                    <label class="switch">
                        <input type="checkbox"
                               name="wp_coder_snippet[<?php echo esc_attr( $name ); ?>]" <?php echo esc_attr( $checked ); ?>
                               value="1" id="wp_coder_snippet-<?php echo esc_attr( $name ); ?>">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
			<?php
			$i ++;
		}
		echo '</div>';
	}


	private static function field( $type = 'checkbox', $name = '', $def = '', $placeholder = '' ) {
		$options = get_option( '_wp_coder_snippets', [] );
		if ( $type === 'checkbox' ) {
			$checked = array_key_exists( $name, $options ) ? 'checked' : '';
			echo '<input type="checkbox" name="wp_coder_snippet[' . esc_attr( $name ) . ']" ' . esc_attr( $checked ) . ' value="1" id="' . esc_attr( $name ) . '">';
			echo '';
		} elseif ( $type === 'text' ) {
			$value = ! empty( $options[ $name ] ) ? $options[ $name ] : $def;
			echo '<input type="text" name="wp_coder_snippet[' . esc_attr( $name ) . ']" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '">';
		} elseif ( $type === 'textarea' ) {
			$value = ! empty( $options[ $name ] ) ? $options[ $name ] : $def;
			echo '<textarea name="wp_coder_snippet[' . esc_attr( $name ) . ']" placeholder="' . esc_attr( $placeholder ) . '">' . esc_attr( $value ) . '</textarea>';
		} elseif ( $type === 'number' ) {
			$value = ! empty( $options[ $name ] ) ? $options[ $name ] : $def;
			echo '<input type="number" name="wp_coder_snippet[' . esc_attr( $name ) . ']" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '">';
		}
	}

	private static function send(): void {
		if ( ! self::verify() ) {
			return;
		}

		if ( empty( $_POST['wp_coder_snippet'] ) ) {
			$snippets = [];
		} else {
			$snippets = map_deep( wp_unslash( $_POST['wp_coder_snippet'] ), 'sanitize_text_field' );
		}

		update_option( '_wp_coder_snippets', $snippets );
	}

	private static function verify(): bool {
		$wp_coder     = $_POST['wp_coder_snippet'] ?? '';
		$nonce_name   = WPCoder::PREFIX . '_save_snippet';
		$nonce_action = WPCoder::PREFIX . '_snippets_action';
		if ( empty( $_POST[ $nonce_name ] ) ) {
			return false;
		}

		return isset( $_POST['wp_coder_snippet'] ) && wp_verify_nonce( $_POST[ $nonce_name ],
				$nonce_action ) && WPCoder::user_can_manage();
	}

}