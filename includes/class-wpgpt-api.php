<?php
/**
 * WPGPT API
 *
 * @package wpgpt
 * @since 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use OpenAI\Client;

/**
 * WPGPT API
 *
 * @since 0.1.0
 */
class WPGPT_API {

	/**
	 * API Key
	 *
	 * @var string $api_key The OpenAI API key.
	 * @since 0.1.0
	 */
	private string $api_key;

	/**
	 * Default Max Tokens
	 *
	 * @var int $default_max_tokens The default max tokens for the OpenAI API request.
	 * @since 0.1.0
	 */
	private int $default_max_tokens;

	/**
	 * Default Temperature
	 *
	 * @var float $default_temperature The default temperature for the OpenAI API request.
	 * @since 0.1.0
	 */
	private float $default_temperature;

	/**
	 * Default Presence Penalty
	 *
	 * @var float $default_presence_penalty The default presence penalty for the OpenAI API request.
	 * @since 0.1.0
	 */
	private float $default_presence_penalty;

	/**
	 * Default Frequency Penalty
	 *
	 * @var float $default_frequency_penalty The default frequency penalty for the OpenAI API request.
	 * @since 0.1.0
	 */
	private float $default_frequency_penalty;

	/**
	 * Default model
	 *
	 * @var string $default_model The default model for the OpenAI API request.
	 * @since 0.1.0
	 */
	private string $default_model;

	/**
	 * Default choices
	 *
	 * @var int $default_choices The default number of choices for the OpenAI API response.
	 * @since 0.1.0
	 */
	private int $default_choices;

	/**
	 * Client
	 *
	 * @var Client $client The OpenAI/Client instance.
	 * @since 0.1.0
	 */
	private Client $client;

	/**
	 * Construct
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->api_key                   = get_option( 'wpgpt_openai_api_key', '' );
		$this->default_max_tokens        = get_option( 'wpgpt_default_max_tokens', 3000 );
		$this->default_temperature       = get_option( 'wpgpt_default_temperature', 0.5 );
		$this->default_presence_penalty  = get_option( 'wpgpt_default_presence_penalty', 0 );
		$this->default_frequency_penalty = get_option( 'wpgpt_default_frequency_penalty', 0 );
		$this->default_model             = 'gpt-3.5-turbo';
		$this->default_choices           = 1;
		$this->client                    = OpenAI::client( $this->api_key );
	}

	/**
	 * Init
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 10 );
	}

	/**
	 * Register REST Routes
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function register_rest_routes() {
		register_rest_route(
			'wpgpt/v1',
			'/generate',
			array(
				'methods'             => array( 'GET' ),
				'callback'            => array( $this, 'generate' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'prompt'            => array(
						'required' => true,
						'type'     => 'string',
					),
					'choices'           => array(
						'type'    => 'number',
						'default' => $this->default_choices,
					),
					'model'             => array(
						'type'    => 'string',
						'default' => $this->default_model,
					),
					'temperature'       => array(
						'type'    => 'number',
						'default' => $this->default_temperature,
					),
					'max_tokens'        => array(
						'type'    => 'number',
						'default' => $this->default_max_tokens,
					),
					'presence_penalty'  => array(
						'type'    => 'number',
						'default' => $this->default_presence_penalty,
					),
					'frequency_penalty' => array(
						'type'    => 'number',
						'default' => $this->default_frequency_penalty,
					),
				),
			)
		);
	}

	/**
	 * Generate
	 *
	 * @since 0.1.0
	 * @param WP_REST_Request $request An incoming API request instance.
	 * @return WP_REST_Response An outgoing API response instance.
	 */
	public function generate( WP_REST_Request $request ): WP_REST_Response {

		$model             = $request->get_param( 'model' );
		$choices           = $request->get_param( 'choices' );
		$temperature       = $request->get_param( 'temperature' );
		$max_tokens        = $request->get_param( 'max_tokens' );
		$presence_penalty  = $request->get_param( 'presence_penalty' );
		$frequency_penalty = $request->get_param( 'frequency_penalty' );
		$prompt            = $request->get_param( 'prompt' );

		try {
			$result = $this->client->chat()->create(
				array(
					'model'             => $model,
					'temperature'       => $temperature,
					'max_tokens'        => $max_tokens,
					'presence_penalty'  => $presence_penalty,
					'frequency_penalty' => $frequency_penalty,
					'n'                 => $choices,
					'messages'          => array(
						array(
							'role'    => 'user',
							'content' => $prompt,
						),
					),
				)
			);

			$status = 200;
			$data   = $result->toArray();
		} catch ( Exception $error ) {
			$status = 400;
			$data   = array(
				'message' => $error->getMessage(),
				'code'    => $error->getCode(),
				'data'    => array(
					'status' => $status,
					'openai' => array(
						'client'   => 'openai-php',
						'endpoint' => 'chat',
						'model'    => $model,
					),
				),
			);
		}

		$response = new WP_REST_Response( $data, $status );
		return $response;
	}

}
