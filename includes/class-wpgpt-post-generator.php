<?php
/**
 * WPGPT Post Generator
 *
 * @package wpgpt
 * @since 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPGPT Post Generator
 *
 * @since 0.1.0
 */
class WPGPT_Post_Generator {

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
	 * @param string $editor The ID of the editor.
	 * @return void
	 */
	public function add_editor_button( string $editor ) {
		if ( $editor === 'content' ) { ?>
			<button 
			type="button" 
			id="<?php printf( 'wpgpt-post-generator-%1$s', sanitize_key( $editor ) ); ?>" 
			class="button wpgpt-post-generator" 
			data-editor="<?php echo esc_attr( $editor ); ?>">
				<span class="dashicons dashicons-welcome-write-blog wp-media-buttons-icon" style="pointer-events:none;"></span>
				<span style="pointer-events:none;"><?php esc_html_e( 'Generate Post', 'wpgpt' ); ?></span>
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
		<div id="wpgpt-post-generator-loader" class="wpgpt-post-generator-loader">
			<div class="wpgpt-post-generator-loader__container">
				<img 
				class="wpgpt-post-generator-loader__spinner"
				src="<?php echo esc_url( home_url( '/wp-includes/images/spinner-2x.gif' ) ); ?>" 
				width="36" 
				height="36" />
				<h2 class="wpgpt-post-generator-loader__title">
					<?php esc_html_e( 'Generating Post...', 'wpgpt' ); ?>
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
		<style id="wpgpt-post-generator-style">

			.wpgpt-post-generator .wp-media-buttons-icon {
				margin-top: -0.2em !important;
			}

			.wpgpt-post-generator-loader {
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background-color: rgba(0, 0, 0, 0.75);
				z-index: 100000;
				display: none;
			}

			.wpgpt-post-generator-loader__container {
				display: flex;
				flex-flow: column;
				align-items: center;
				justify-content: center;
				width: 100%;
				height: 100%;
			}

			.wpgpt-post-generator-loader__spinner {
				background-color: #ffffff;
				border-radius: 50%;
				padding: 4px;
			}

			.wpgpt-post-generator-loader__title {
				color: #ffffff;
			}

			body.wpgpt-post-generator-loading .wpgpt-post-generator-loader {
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
		<script id="wpgpt-post-generator-script">

			(function() {

				function init() {
					destroy();
					const buttons = document.querySelectorAll('button.wpgpt-post-generator');
					buttons.forEach(function(button) {
						button.addEventListener('click', handleButtonClick);
					});
				}

				function destroy() {
					const buttons = document.querySelectorAll('button.wpgpt-post-generator');
					buttons.forEach(function(button) {
						button.removeEventListener('click', handleButtonClick);
					});
				}

				function handleButtonClick(e) {
					e.preventDefault();
					const editorId = e.target.dataset.editor;
					generatePost(editorId);
				}

				function setEditorContent(editorId, content) {
					const editorInstance = tinyMCE.get(editorId);
					editorInstance.setContent(content);
					editorInstance.save();
				}

				function getEditorContent(editorId) {
					const editorInstance = tinyMCE.get(editorId);
					content = editorInstance.getContent(content);
					return content;
				}

				function getFormData() {
					const form = document.querySelector('form#post');
					return new FormData(form);
				}

				function getTitle() {
					const formData = getFormData();
					return formData.get('post_title');
				}

				function getTags() {
					const formData = getFormData();
					const tagData = formData.get('tax_input[post_tag]') || '';
					const tags = tagData.split(',').map(function(tag) { return tag.trim(); });
					return tags.join(', ');
				}

				function getCategories() {
					const categories = [];
					const checkboxes = document.querySelectorAll('input[name="post_category[]"]:checked');
					checkboxes.forEach(function(checkbox) {
						categories.push(checkbox.parentElement.textContent.trim());
					});
					return categories.join(', ');
				}

				function getKeywords() {
					const categories = getCategories();
					const tags = getTags();
					const grouped = [];
					if (categories) {
						grouped.push(categories);
					}
					if (tags) {
						grouped.push(tags);
					}
					return grouped.join(', ');
				}

				function getPrompt() {
					const title = getTitle();
					const keywords = getKeywords();
					var prompt = 'Write an article entitled: "' + title + '".\n\n';
					prompt += 'Instructions: Format as HTML. Seperate the content into sections and subsections. Sections start with a heading wrapped in an `h2` element followed by 1-3 paragraphs, and subsections start with a heading wrapped in an `h3` element followed by 1-3 paragraphs. The first section must be a clever heading followed by an introduction. Seperate consecutive paragraphs with `<br/><br/>`. Do not wrap the the content in document or body elements; and do not output any elements except for `h2`, `h3`, `h4`, `h5`, `h6`, `ol`, `ul`, `li`, `strong`, `em`, `del`, `a`, `pre`, `code`.\n\n';
					prompt += 'Personality: Write in a creative tone as if you were a staff writer for a popular publication with expert sources. Do not write in the first person.\n\n';
					if (keywords) {
						prompt += 'Keywords: ' + keywords + '.';
					}

					return prompt.trim();
				}

				function setLoading(isLoading) {
					if (isLoading) {
						document.body.classList.add('wpgpt-post-generator-loading');
					} else {
						document.body.classList.remove('wpgpt-post-generator-loading');
					}
				}

				function generatePost(editorId) {
					setLoading(true);
					const prompt = getPrompt();
					console.info('Generating post...\n\n', prompt);
					const response = wp.apiRequest({
						path: '/wpgpt/v1/generate',
						data: {
							prompt: prompt,
						},
					})
					.then(function(response) {
						console.info('Post generated!\n\n', response);
						const message = response.choices[0].message.content.trim();
						setEditorContent(editorId, message);
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
				window.wpgpt.postGenerator = {
					init: init,
					destroy: destroy,
				};

				document.addEventListener('DOMContentLoaded', function(e) {
					window.wpgpt.postGenerator.init();
				});

			})();

		</script>
	<?php }
}
