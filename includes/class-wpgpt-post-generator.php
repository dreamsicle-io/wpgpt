<?php

use OpenAI\Client;

class WPGPT_Post_Generator {

	public function __construct() {

	}

	public function init() {
		add_action( 'media_buttons', array( $this, 'add_editor_button' ), 10 );
		add_action( 'admin_print_styles', array( $this, 'print_admin_style' ), 10 );
		add_action( 'admin_print_footer_scripts', array( $this, 'print_admin_script' ), 10 );
		add_action( 'admin_footer', array( $this, 'render_loader' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 10 );
	}

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

	public function add_editor_button( string $editor ) {
		if ( $editor === 'content' ) { ?>
			<button 
			type="button" 
			id="<?php printf( 'wpgpt-generate-post-%1$s', sanitize_key( $editor ) ); ?>" 
			class="button wpgpt-generate-post" 
			data-editor="<?php echo esc_attr( $editor ); ?>">
				<span class="dashicons dashicons-admin-comments" style="vertical-align:middle;pointer-events:none;"></span>
				<span style="pointer-events:none;"><?php esc_html_e( 'Generate Post', 'wpgpt' ); ?></span>
			</button>
			<button 
			type="button" 
			id="<?php printf( 'wpgpt-elaborate-selection-%1$s', sanitize_key( $editor ) ); ?>" 
			class="button wpgpt-elaborate-selection" 
			data-editor="<?php echo esc_attr( $editor ); ?>">
				<span class="dashicons dashicons-admin-comments" style="vertical-align:middle;pointer-events:none;"></span>
				<span style="pointer-events:none;"><?php esc_html_e( 'Elaborate', 'wpgpt' ); ?></span>
			</button>
		<?php }
	}

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

	public function print_admin_style() { ?>

		<style id="wpgpt-generate-post-style">

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

			body.wpgpt-loading .wpgpt-post-generator-loader {
				display: block;
			}

		</style>

	<?php }

	public function print_admin_script() { ?>

		<script id="wpgpt-generate-post-script">

			(function() {

				function init() {
					const buttons = document.querySelectorAll('button.wpgpt-generate-post');
					const elaborateButtons = document.querySelectorAll('button.wpgpt-elaborate-selection');
					buttons.forEach(function(button) {
						button.addEventListener('click', handleButtonClick);
					});
					elaborateButtons.forEach(function(button) {
						button.addEventListener('click', handleElaborateButtonClick);
					});
				}

				function destroy() {
					const buttons = document.querySelectorAll('button.wpgpt-generate-post');
					const elaborateButtons = document.querySelectorAll('button.wpgpt-elaborate-selection');
					buttons.forEach(function(button) {
						button.removeEventListener('click', handleButtonClick);
					});
					elaborateButtons.forEach(function(button) {
						button.removeEventListener('click', handleElaborateButtonClick);
					});
				}

				function handleButtonClick(e) {
					e.preventDefault();
					const editorId = e.target.dataset.editor;
					generatePost(editorId);
				}

				function handleElaborateButtonClick(e) {
					e.preventDefault();
					const editorId = e.target.dataset.editor;
					elaborateSelection(editorId);
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

				function getElaboratePrompt(editorId) {
					const selection = getEditorSelection(editorId);
					const title = getTitle();
					const tags = getTags();
					const categories = getCategories();
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
						document.body.classList.add('wpgpt-loading');
					} else {
						document.body.classList.remove('wpgpt-loading');
					}
				}

				function generatePost(editorId) {
					setLoading(true);
					const prompt = getPrompt();
					console.info('Generating post...\n\n', prompt);
					const response = wp.apiRequest({
						path: '/wpgpt/v1/generate-post',
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

				function elaborateSelection(editorId) {
					setLoading(true);
					const prompt = getElaboratePrompt(editorId);
					console.info('Elaborating selection...\n\n', prompt);
					const response = wp.apiRequest({
						path: '/wpgpt/v1/generate-post',
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

				document.addEventListener('DOMContentLoaded', function(e) {
					init();
				});

			})();

		</script>

	<?php }
}
