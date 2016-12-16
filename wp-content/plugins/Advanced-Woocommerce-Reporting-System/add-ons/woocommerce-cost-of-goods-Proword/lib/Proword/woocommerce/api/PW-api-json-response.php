<?php


defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'PW_COST_GOOD_ADMIN_RESPONSE_JSON' ) ) :


/**
 * Base JSON API response class.
 *
 * @since 4.3.0
 */
abstract class PW_COST_GOOD_ADMIN_RESPONSE_JSON implements PW_COST_GOOD_ADMIN_RESPONSE_APLI {


	/** @var string string representation of this response */
	protected $raw_response_json;

	/** @var mixed decoded response data */
	public $response_data;


	/**
	 * Build the data object from the raw JSON.
	 *
	 * @since 4.3.0
	 * @param string $raw_response_json The raw JSON
	 */
	public function __construct( $raw_response_json ) {

		$this->raw_response_json = $raw_response_json;

		$this->response_data = json_decode( $raw_response_json );
	}


	/**
	 * Magic accessor for response data attributes
	 *
	 * @since 4.3.0
	 * @param string $name The attribute name to get.
	 * @return mixed The attribute value
	 */
	public function __get( $name ) {

		// accessing the response_data object indirectly via attribute (useful when it's a class)
		return isset( $this->response_data->$name ) ? $this->response_data->$name : null;
	}


	/**
	 * Get the string representation of this response.
	 *
	 * @since 4.3.0
	 * @see PW_COST_GOOD_ADMIN_RESPONSE_APLI::to_string()
	 * @return string
	 */
	public function to_string() {

		return $this->raw_response_json;
	}


	/**
	 * Get the string representation of this response with any and all sensitive elements masked
	 * or removed.
	 *
	 * @since 4.3.0
	 * @see PW_COST_GOOD_ADMIN_RESPONSE_APLI::to_string_safe()
	 * @return string
	 */
	public function to_string_safe() {

		return $this->to_string();
	}


}

endif; // class exists check
