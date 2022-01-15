<?php

class randomNameGenerator {

	private $version;
	public $allowedFormats;
	public $inputFormat;

	public function __construct( $output = 'array' ) {

		$this->version = '1.0.0';
		$this->allowedFormats = array('array', 'json', 'associative_array');
		$this->inputFormat = 'json';

		if ( !in_array( $output, $this->allowedFormats ) ) {
			throw new Exception('Unrecognized format');
		}

		$this->output = $output;
	}

	private function getList( $type ) {
		$json = file_get_contents($type . '.' . $this->inputFormat, FILE_USE_INCLUDE_PATH );
		$data = json_decode( $json, true );

		return $data;
	}

	public function getName() {

        $first_names = $this->getList('first-names');
		$last_names  = $this->getList('last-names');
        $random_fname_index = array_rand( $first_names );
		$random_lname_index = array_rand( $last_names );
		return $first_names[$random_fname_index] . ' ' . $last_names[$random_lname_index];
	}

}