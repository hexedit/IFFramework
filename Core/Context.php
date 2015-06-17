<?php
namespace IFFramework\Core
{

	require_once 'IFFramework/Object.php';

	class Context extends \IFFramework\Object
	{
		
		// Данные запроса
		protected $_request;
		// Данные ответа
		protected $_response;
		// Данные сессии
		protected $_session;
		// Данные приложения
		protected $_stash;
		// Имя текущего представления
		protected $_view;
		// Конфигурация приложения
		protected $_config;

		protected $params;

		protected $isSecure;

		public function __construct( $params )
		{
			$this->_request = (object) array(
				'get' => (object) $_GET,
				'post' => (object) $_POST,
				'params' => (object) $_REQUEST
			);
			$this->_response = new Response();
			$this->_session = new Session();
			$this->_stash = (object) array();
			$this->_view = $params[ 'defaultView' ];
			$this->_config = $params[ 'config' ];
			
			$this->params = (object) $params;
			$this->isSecure = filter_var( getenv( 'HTTPS' ), FILTER_VALIDATE_BOOLEAN );
		}

		protected function set_view( $new )
		{
			$this->_view = $new;
		}
		
		protected function get_title()
		{
			return $this->config->title;
		}
		
		protected function get_controller()
		{
			return $this->params->controller;
		}
		
		protected function get_action()
		{
			return $this->params->action;
		}

		public function model( $model )
		{
			try
			{
				require_once $this->params->modelDir . DIRECTORY_SEPARATOR . $model . '.php';
				return new $model( $this );
			}
			catch ( Exception $e )
			{
				return null;
			}
		}

		public function uri_for( $path )
		{
			$http_scheme = $this->params->isSecure ? 'https' : 'http';
			return $http_scheme . '://' . $_SERVER[ 'HTTP_HOST' ] . $this->params->baseUri . $path;
		}
		
		public function path_for( $path )
		{
			return $this->params->basePath . DIRECTORY_SEPARATOR . $path;
		}

		public function is_current( $path )
		{
			return urldecode( $_SERVER[ 'REQUEST_URI' ] ) === $this->params->baseUri . $path;
		}
	}
}
