<?php

namespace IFFramework\Core
{

	require_once 'IFFramework/Object.php';

	class Context extends \IFFramework\Object
	{

		protected $_req; // Данные запроса
		protected $_res; // Данные ответа
		protected $_session; // Данные сессии
		protected $_stash; // Данные приложения
		protected $_view; // Содержит имя текущего представления
		protected $_title; // Заголовок приложения
		protected $_config; // Конфигурация приложения
		protected $_module;
		protected $_action;
		protected $_args;
		protected $_defaultModule;
		protected $_defaultAction;
		protected $_isSecure;

		protected $baseUri;

		protected $modelPath;

		protected $params;

		public function __construct( $params )
		{
			$this->_title = $params['title'];
			$this->_config = $params['config'];
			$this->_defaultModule = ( isset($params['default_module']) && !empty($params['default_module']) ) ? $params['default_module'] : 'root';
			$this->_defaultAction = ( isset($params['default_action']) && !empty($params['default_action']) ) ? $params['default_action'] : 'index';
			$this->_isSecure = filter_var( getenv( 'HTTPS' ), FILTER_VALIDATE_BOOLEAN );
			$this->baseUri = $params['base_uri'];
			$this->modelPath = $params['model_path'];

			$this->_req = (object)array(
					'get' => (object)$_GET,
					'post' => (object)$_POST,
					'params' => (object)$_REQUEST
			);

			$this->_res = new Response();

			session_start();
			$this->_session = new Session( $_SESSION );

			$this->_stash = (object)array();
			$this->_view = null;

			// Разбиваем path на составляющие
			$path = explode( '/', $_GET['_path'] );
			$this->_module = ( $path[0] && count( $path ) > 1 ) ? $path[0] : '';
			$this->_action = $path[count( $path ) - 1] ? $path[count( $path ) - 1] : $this->defaultAction;
			unset( $path[count( $path ) - 1] );
			unset( $path[0] );
			$this->_args = array_values( $path );

			$this->params = $params;
		}

		protected function set_view( $new )
		{
			$this->_view = $new;
		}

		public function model( $model )
		{
			try
			{
				require_once $this->modelPath . '/' . $model . '.php';
				return new $model( $this->params );
			} catch ( Exception $e )
			{
				return null;
			}
		}

		public function uri_for( $path )
		{
			$http_scheme = $this->isSecure ? 'https' : 'http';
			return $http_scheme . '://' . $_SERVER['HTTP_HOST'] . $this->baseUri . $path;
		}

		public function is_current( $path )
		{
			return urldecode( $_SERVER['REQUEST_URI'] ) === $this->baseUri . $path;
		}

	}

}