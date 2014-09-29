<?php

namespace IFFramework\Core
{

	class Response
	{

		protected $_code;

		protected $_content_type;

		protected $_encoding;

		protected $_body;

		public function __construct()
		{
			$this->_code = 200;
			$this->_content_type = 'text/plain';
			$this->_encoding = null;
			$this->_body = null;
		}

		public function __get( $prop )
		{
			$_prop = "_$prop";
			return $this->$_prop;
		}

		public function __set( $prop, $val )
		{
			$_prop = "_$prop";
			$this->$_prop = $val;
		}

		public function redirect( $url, $code = 301 )
		{
			header( "Location: $url", true, $code );
			$this->_code = $code;
		}

	}

}