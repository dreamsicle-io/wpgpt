<?php
/**
 * WPGPT Elaborator
 *
 * @package wpgpt
 * @since 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPGPT Elaborator
 *
 * @since 0.1.0
 */
class WPGPT_Elaborator {

	/**
	 * Construct
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

	}

	/**
	 * Init
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function init() {
		add_action( 'media_buttons', array( $this, 'add_editor_button' ), 10 );
		add_action( 'admin_print_styles', array( $this, 'print_admin_style' ), 10 );
		add_action( 'admin_print_footer_scripts', array( $this, 'print_admin_script' ), 10 );
		add_action( 'admin_footer', array( $this, 'render_loader' ), 10 );
	}

	/**
	 * Add Editor Button
	 *
	 * @since 0.1.0
	 * @param string $editor the ID of the editor.
	 * @return void
	 */
	public function add_editor_button( string $editor ) {
		if ( $editor === 'content' ) { ?>
			<button 
			type="button" 
			id="<?php printf( 'wpgpt-elaborator-%1$s', sanitize_key( $editor ) ); ?>" 
			class="button wpgpt-elaborator" 
			data-editor="<?php echo esc_attr( $editor ); ?>">
				<span class="dashicons dashicons-welcome-add-page" style="vertical-align:middle;pointer-events:none;margin-top:-0.2em;"></span>
				<span style="pointer-events:none;"><?php esc_html_e( 'Elaborate', 'wpgpt' ); ?></span>
			</button>
		<?php }
	}

	/**
	 * Render Loader
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function render_loader() { ?>
		<div id="wpgpt-wpgpt-elaborator-loader" class="wpgpt-elaborator-loader">
			<div class="wpgpt-elaborator-loader__container">
				<img 
				class="wpgpt-elaborator-loader__spinner"
				src="<?php echo esc_url( home_url( '/wp-includes/images/spinner-2x.gif' ) ); ?>" 
				width="36" 
				height="36" />
				<h2 class="wpgpt-elaborator-loader__title">
					<?php esc_html_e( 'Elaborating Selection...', 'wpgpt' ); ?>
				</h2>
			</div>
		</div>
	<?php }

	/**
	 * Print Admin Style
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function print_admin_style() { ?>

		<style id="wpgpt-elaborator-style">

			.wpgpt-elaborator-loader {
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background-color: rgba(0, 0, 0, 0.75);
				z-index: 100000;
				display: none;
			}

			.wpgpt-elaborator-loader__container {
				display: flex;
				flex-flow: column;
				align-items: center;
				justify-content: center;
				width: 100%;
				height: 100%;
			}

			.wpgpt-elaborator-loader__spinner {
				background-color: #ffffff;
				border-radius: 50%;
				padding: 4px;
			}

			.wpgpt-elaborator-loader__title {
				color: #ffffff;
			}

			body.wpgpt-elaborator-loading .wpgpt-elaborator-loader {
				display: block;
			}

		</style>

	<?php }

	/**
	 * Print Admin Script
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function print_admin_script() { ?>

		<script id="wpgpt-elaborator-script">

			(function() {

				function init() {
					destroy();
					const buttons = document.querySelectorAll('button.wpgpt-elaborator');
					buttons.forEach(function(button) {
						button.addEventListener('click', handleButtonClick);
					});
				}

				function destroy() {
					const buttons = document.querySelectorAll('button.wpgpt-elaborator');
					buttons.forEach(function(button) {
						button.removeEventListener('click', handleButtonClick);
					});
				}

				function handleButtonClick(e) {
					e.preventDefault();
					const editorId = e.target.dataset.editor;
					elaborate(editorId);
				}

				function getFormData() {
					const form = document.querySelector('form#post');
					return new FormData(form);
				}

				function getPrompt(editorId) {
					const selection = getEditorSelection(editorId);
					var prompt = 'Elaborate on the following text:\n\n';
					prompt += '```\n';
					prompt += selection + '\n';
					prompt += '```\n\n';
					prompt += 'Instructions: Format as HTML. Seperate consecutive paragraphs with `<br/><br/>`. Do not wrap the the content in document or body elements; and do not output any elements except for `ol`, `ul`, `li`, `strong`, `em`, `del`, `a`, `pre`, `code`.\n\n';
					prompt += 'Personality: Write in a creative tone as if you were a staff writer for a popular publication with expert sources. Do not write in the first person.\n\n';
					return prompt.trim();
				}

				function getEditorSelection(editorId) {
					const editor = tinyMCE.get(editorId);
					return selection = editor.selection.getContent({format : 'html'});
				}

				function setEditorSelection(editorId, content) {
					const editor = tinyMCE.get(editorId);
					return selection = editor.selection.setContent(content, {format : 'html'});
				}

				function setLoading(isLoading) {
					if (isLoading) {
						document.body.classList.add('wpgpt-elaborator-loading');
					} else {
						document.body.classList.remove('wpgpt-elaborator-loading');
					}
				}

				function elaborate(editorId) {
					setLoading(true);
					const prompt = getPrompt(editorId);
					console.info('Elaborating selection...\n\n', prompt);
					const response = wp.apiRequest({
						path: '/wpgpt/v1/generate',
						data: {
							prompt: prompt,
						},
					})
					.then(function(response) {
						console.info('Selection elaborated!\n\n', response);
						const message = response.choices[0].message.content.trim();
						setEditorSelection(editorId, message);
					})
					.catch(function(response) {
						const errorStatus = response.responseJSON.data.status;
						const errorMessage = response.responseJSON.message;
						console.error(errorStatus + ': ' + errorMessage);
					})
					.always(function() {
						setLoading(false);
					});
				}

				window.wpgpt = window.wpgpt || {};
				window.wpgpt.elaborator = {
					init: init,
					destroy: destroy,
				};

				document.addEventListener('DOMContentLoaded', function(e) {
					window.wpgpt.elaborator.init();
				});

			})();

		</script>

	<?php }
}
