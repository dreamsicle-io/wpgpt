<?php
/**
 * WPGPT Caption Generator
 *
 * @package wpgpt
 * @since 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPGPT Caption Generator
 *
 * @since 0.1.0
 */
class WPGPT_Caption_Generator {

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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 10 );
		add_action( 'admin_print_styles', array( $this, 'print_admin_style' ), 10 );
		add_action( 'admin_print_footer_scripts', array( $this, 'print_admin_script' ), 10 );
	}

	/**
	 * Enqueue Admin Scripts
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function enqueue_admin_scripts() {
		if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery' );
		}
		if ( ! wp_script_is( 'thickbox', 'enqueued' ) ) {
			wp_enqueue_script( 'thickbox' );
		}
		if ( ! wp_style_is( 'thickbox', 'enqueued' ) ) {
			wp_enqueue_style( 'thickbox' );
		}
	}

	/**
	 * Add Editor Button
	 *
	 * @since 0.1.0
	 * @param string $editor The ID of the editor.
	 * @return void
	 */
	public function add_editor_button( string $editor ) {
		if ( $editor === 'content' ) {
			$thickbox_id = sprintf( 'wpgpt-caption-generator-thickbox-%1$s', $editor );
			$url         = WPGPT_Utils::get_thickbox_url(
				array(
					'inlineId' => $thickbox_id,
				)
			); ?>
			<a 
			href="<?php echo esc_attr( $url ); ?>"
			id="<?php printf( 'wpgpt-caption-generator-%1$s', sanitize_key( $editor ) ); ?>" 
			class="button thickbox wpgpt-caption-generator" 
			data-editor="<?php echo esc_attr( $editor ); ?>">
				<span class="dashicons dashicons-share" style="vertical-align:middle;pointer-events:none;margin-top:-0.2em;"></span>
				<span style="pointer-events:none;"><?php esc_html_e( 'Generate Caption', 'wpgpt' ); ?></span>
			</a>
			<div id="<?php echo esc_attr( $thickbox_id ); ?>" style="display:none;">
				<?php $this->render_generate_caption_modal( $editor ); ?>
			</div>
		<?php }
	}

	/**
	 * Render Generate Caption Modal
	 *
	 * @since 0.1.0
	 * @param string $editor The ID of the editor.
	 * @return void
	 */
	public function render_generate_caption_modal( string $editor ) {
		$modal_id = sprintf( 'wpgpt-caption-generator-modal-%1$s', $editor ); ?>
		<div id="<?php echo esc_attr( $modal_id ); ?>" class="wpgpt-caption-generator-modal">
			<div class="wpgpt-caption-generator-modal__header">
				<h2 class="wpgpt-caption-generator-modal__title">
					<?php esc_html_e( 'Generate a Social Media Caption', 'wpgpt' ); ?>
				</h2>
				<p class="wpgpt-caption-generator-modal__description">
					<?php esc_html_e( 'Click "Generate" to generate a social media caption using the current post content as context.', 'wpgpt' ); ?>
				</p>
			</div>
			<div class="wpgpt-caption-generator-modal__scroller">
				<pre class="wpgpt-caption-generator-modal__output"></pre>
				<div class="wpgpt-caption-generator-modal__loader">
					<div class="wpgpt-caption-generator-modal__loader-container">
						<img 
						class="wpgpt-caption-generator-modal__loader-spinner"
						src="<?php echo esc_url( home_url( '/wp-includes/images/spinner-2x.gif' ) ); ?>" 
						width="36" 
						height="36" />
						<h2 class="wpgpt-caption-generator-modal__loader-title">
							<?php esc_html_e( 'Generating Caption...', 'wpgpt' ); ?>
						</h2>
					</div>
				</div>
			</div>
			<div class="wpgpt-caption-generator-modal__actions">
				<button 
				type="button"
				data-editor="<?php echo esc_attr( $editor ); ?>"
				class="button-primary wpgpt-caption-generator-modal__submit">
					<?php echo esc_html_e( 'Generate', 'wpgpt' ); ?>
				</button>
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

		<style id="wpgpt-caption-generator-style">

			#TB_ajaxContent {
				position: relative;
				width: 100% !important;
				height: calc(100% - 30px) !important;
				box-sizing: border-box;
			}

			.wpgpt-caption-generator-modal {
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				display: flex;
				flex-flow: column;
				align-items: stretch;
				justify-content: flex-start;
			}

			.wpgpt-caption-generator-modal__scroller {
				flex-grow: 1;
				overflow-y: auto;
				padding: 20px;
				position: relative;
			}

			.wpgpt-caption-generator-modal__header {
				background-color: #fafafa;
				border-bottom: 1px solid #dddddd;
				padding: 20px;
			}

			.wpgpt-caption-generator-modal__title {
				margin: 0 0 4px;
				padding: 0;
			}

			.wpgpt-caption-generator-modal__description {
				margin: 0;
				padding: 0;
			}

			.wpgpt-caption-generator-modal__output {
				width: 100%;
				height: 100%;
				margin: 0;
				padding: 0;
				box-sizing: border-box;
				white-space: pre-wrap;
				white-space: pre-wrap;
				font-size: 18px;
				line-height: 1.25;
			}

			.wpgpt-caption-generator-modal__loader {
				background-color: #ffffff;
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				display: none;
			}

			.wpgpt-caption-generator-modal__loader-container {
				display: flex;
				flex-flow: column;
				align-items: center;
				justify-content: center;
				width: 100%;
				height: 100%;
			}

			.wpgpt-caption-generator-modal__loader-title {
				text-align: center;
			}

			.wpgpt-caption-generator-modal__actions {
				border-top: 1px solid #dddddd;
				padding: 20px;
				display: flex;
				flex-flow: row wrap;
				align-items: center;
				justify-content: flex-end;
			}

			.wpgpt-caption-generator-modal--loading .wpgpt-caption-generator-modal__loader {
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

		<script id="wpgpt-caption-generator-script">

			(function() {

				function init() {
					destroy();
					const buttons = document.querySelectorAll('button.wpgpt-caption-generator-modal__submit');
					buttons.forEach(function(button) {
						button.addEventListener('click', handleButtonClick);
					});
				}

				function destroy() {
					const buttons = document.querySelectorAll('button.wpgpt-caption-generator-modal__submit');
					buttons.forEach(function(button) {
						button.removeEventListener('click', handleButtonClick);
					});
				}

				function handleButtonClick(e) {
					e.preventDefault();
					const editorId = e.target.dataset.editor;
					console.log(editorId);
					generateCaption(editorId);
				}

				function setOutputContent(editorId, messages) {
					const output = document.querySelector('#wpgpt-caption-generator-modal-' + editorId + ' .wpgpt-caption-generator-modal__output');
					const content = messages.join('\n\n');
					output.innerHTML = content;
				}

				function getFormData() {
					const form = document.querySelector('form#post');
					return new FormData(form);
				}

				function getContent() {
					const formData = getFormData();
					return formData.get('content')
						.replace( /(<([^>]+)>)/ig, '') // strip tags.
						.replace(/\n\n/ig, ' ') // Replace double line breaks with spaces.
						.replace(/\n/ig, ' ') // Replace single line breaks with spaces.
						.replace(/^(.{1000}[^\s]*).*/ig, '$1') // Get only the first n characters (without breaking words).
						.trim();
				}

				function getShortlink() {
					const formData = getFormData();
					const postId = formData.get('post_ID');
					const params = new URLSearchParams();
					params.set('p', postId);
					return window.location.origin + '?' + params.toString();
				}

				function getPrompt() {
					const content = getContent();
					const url = getShortlink();
					var prompt = 'Write a social media caption for the following post:\n\n';
					prompt += '```\n';
					prompt += content + '\n';
					prompt += '```\n\n';
					prompt += 'Instructions: Begin with an emoji. Include at least 3 popular hashtags. Include the URL "' + url + '".\n';
					prompt += 'Personality: Write in a creative and urgent tone as if you were a social media export for a popular publication. Do not write in the first person.\n\n';
					return prompt;
				}

				function setLoading(editorId, isLoading) {
					const modal = document.getElementById('wpgpt-caption-generator-modal-' + editorId);
					if (isLoading) {
						modal.classList.add('wpgpt-caption-generator-modal--loading');
					} else {
						modal.classList.remove('wpgpt-caption-generator-modal--loading');
					}
				}

				function generateCaption(editorId) {
					setLoading(editorId, true);
					const prompt = getPrompt();
					console.info('Generating Caption...\n\n', prompt);
					const response = wp.apiRequest({
						path: '/wpgpt/v1/generate',
						data: {
							prompt: prompt,
							choices: 3,
							max_tokens: 1000,
							frequency_penalty: 1,
							presence_penalty: 1,
							temperature: 0.9,
						},
					})
					.then(function(response) {
						console.info('Caption generated!\n\n', response);
						const messages = response.choices.map(function(choice) {
							return choice.message.content.trim();
						})
						setOutputContent(editorId, messages);
					})
					.catch(function(response) {
						const errorStatus = response.responseJSON.data.status;
						const errorMessage = response.responseJSON.message;
						console.error(errorStatus + ': ' + errorMessage);
					})
					.always(function() {
						setLoading(editorId, false);
					});
				}

				window.wpgpt = window.wpgpt || {};
				window.wpgpt.captionGenerator = {
					init: init,
					destroy: destroy,
				};

				document.addEventListener('DOMContentLoaded', function(e) {
					window.wpgpt.captionGenerator.init();
				});

			})();

		</script>

	<?php }
}
