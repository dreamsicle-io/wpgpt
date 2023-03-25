<?php
/**
 * @package wpgpt
 */

class WPGPT {

	private string $api_key = '';
	private OpenAI $client;

	public function __construct() {
		$this->client = OpenAI::client( '$yourApiKey' );
	}

	public function init() {
		var_dump( $this->client );
	}

	protected function generate() {
		$result = $this->client->completions()->create(
			array(
				'model'  => 'text-davinci-003',
				'prompt' => 'PHP is',
			)
		);
		echo $result['choices'][0]['text'];
	}
}
