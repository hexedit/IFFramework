<?php
namespace IFFramework\Core
{

	require_once 'IFFramework/Object.php';
	require_once 'IFFramework/Core/Response.php';
	require_once 'IFFramework/Core/Session.php';

	class Dispatcher extends \IFFramework\Object
	{

		protected $_controller;

		protected $_action;

		protected $args;

		protected $params;

		public function __construct( $params )
		{
			ob_start();
			
			$this->params = (object) $params;
			
			$path_parts = explode( '/', ltrim( $params[ 'uriPath' ], '/' ) );
			$this->controller = ( isset( $path_parts[ 0 ] ) && count( $path_parts ) > 1 ) ? $path_parts[ 0 ] : $this->params->defaultController;
			$this->action = $path_parts[ count( $path_parts ) - 1 ] ? $path_parts[ count( $path_parts ) - 1 ] : $this->params->defaultAction;
			unset( $path_parts[ count( $path_parts ) - 1 ] );
			unset( $path_parts[ 0 ] );
			$this->args = array_values( $path_parts );
		}

		public function __destruct()
		{
			ob_end_clean();
		}
		
		public function set_controller( $new )
		{
			$this->_controller = $new ? $new : $this->params->defaultController;
			global $controller;
			include_once $this->params->controllerDir . DIRECTORY_SEPARATOR . $this->_controller . '.php';
		}
		
		public function set_action( $new )
		{
			$this->_action = $new ? $new : $this->params->defaultAction;
		}

		public function runAction( Context $ctx )
		{
			global $controller;
			
			if ( !isset( $controller[ $this->_controller ] ) && !isset( $controller[ $this->_controller ][ 'CLASS_NAME' ] ) )
				throw new \Exception( sprintf( "Controller '%s' not found", $this->_controller ), 404 );
			
			$mod_class = $controller[ $this->_controller ][ 'CLASS_NAME' ];
			if ( !class_exists( $mod_class ) )
				throw new \Exception( sprintf( "Controller '%s' unavailable", $this->_controller ), 404 );
			
			$mod = new $mod_class( $ctx );
			
			if ( !isset( $controller[ $this->_controller ][ 'EXPORTS' ] ) && !isset( $controller[ $this->_controller ][ 'EXPORTS' ][ $this->action ] ) )
				throw new \Exception( sprintf( "Action '%s' is not found in '%s'", $this->action, $this->_controller ), 404 );
			
			$action = $controller[ $this->_controller ][ 'EXPORTS' ][ $this->action ];
			if ( !method_exists( $mod, $action ) )
				throw new \Exception( sprintf( "Action '%s' is not found in '%s'", $this->action, $this->_controller ), 404 );
			
			$mod->$action( $ctx, $this->args );
		}

		public function renderView( Context $ctx )
		{
			if ( !$ctx->response->body )
			{
				$view;
				try
				{
					include_once $this->params->viewDir . DIRECTORY_SEPARATOR . $ctx->view . '.php';
					$view = 'View_' . $ctx->view;
					if ( !( class_exists( $view ) && in_array( 'IFFramework\View', class_parents( $view ) ) ) )
						throw new \Exception( sprintf( "View class unavailable - %s", $view ), 500 );
					$view = new $view( $ctx );
				}
				catch ( \Exception $e )
				{
					throw new \Exception( "Unable to call renderer", 500, $e );
				}
				
				$ctx->stash->debug = $this->getLog();
				$ctx->stash->runtime = microtime( true ) - $_SERVER[ "REQUEST_TIME_FLOAT" ];
				
				$view->render( $ctx, $this->_controller, $this->action );
			}
		}

		public function getLog()
		{
			return ob_get_contents();
		}
	}
}
