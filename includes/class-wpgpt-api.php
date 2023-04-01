<?php

use OpenAI\Client;

class WPGPT_API {

	private string $api_key;
	private int $default_max_tokens;
	private float $default_temperature;
	private float $default_presence_penalty;
	private float $default_frequency_penalty;
	private string $default_model;
	private int $default_choices;
	private Client $client;

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

	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 10 );
	}

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

	public function generate( WP_REST_Request $request ) {

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
