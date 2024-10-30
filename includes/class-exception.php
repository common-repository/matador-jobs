<?php

namespace matador;

class Exception extends \Exception {

	protected $name;

	protected $level;

	public function __construct( $level, $name, $message = 'Unknown Error', $code = 0, Exception $previous = null ) {
		$this->name = $name;
		$this->level = $level;
		parent::__construct( $message, $code, $previous );

	}

	public function getName() {
		return $this->name;
	}

	public function getLevel() {
		return $this->level;
	}
}
