<?php
namespace IFFramework\Core
{

	require_once 'IFFramework/Object.php';
	require_once 'IFFramework/Core/Response.php';
	require_once 'IFFramework/Core/Session.php';

	class Dispatcher extends \IFFramework\Object
	{

		protected $controller;

		protected $action;

		protected $args;

		protected $params;

		public function __construct( $params )
		{
			global $controller;
			ob_start();
			
			$this->params = (object) $params;
			
			$this->splitPath( $params[ 'uriPath' ] );
			include_once $params[ 'controllerDir' ] . DIRECTORY_SEPARATOR . $this->controller . '.php';
		}

		public function __destruct()
		{
			ob_end_clean();
		}

		protected function splitPath( $path )
		{
			$path_parts = explode( '/', ltrim( $path, '/' ) );
			$this->controller = ( isset( $path_parts[ 0 ] ) && count( $path_parts ) > 1 ) ? $path_parts[ 0 ] : $this->params->defaultController;
			$this->action = $path_parts[ count( $path_parts ) - 1 ] ? $path_parts[ count( $path_parts ) - 1 ] : $this->params->defaultAction;
			unset( $path_parts[ count( $path_parts ) - 1 ] );
			unset( $path_parts[ 0 ] );
			$this->args = array_values( $path_parts );
		}

		public function runAction( $ctx )
		{
			global $controller;
			
			if ( !isset( $controller[ $this->controller ] ) && !isset( $controller[ $this->controller ][ 'CLASS_NAME' ] ) )
				throw new \Exception( sprintf( "Controller '%s' not found", $this->controller ), 404 );
			
			$mod_class = $controller[ $this->controller ][ 'CLASS_NAME' ];
			if ( !class_exists( $mod_class ) )
				throw new \Exception( sprintf( "Controller '%s' unavailable", $this->controller ), 404 );
			
			$mod = new $mod_class( $ctx );
			
			if ( !isset( $controller[ $this->controller ][ 'EXPORTS' ] ) && !isset( $controller[ $this->controller ][ 'EXPORTS' ][ $this->action ] ) )
				throw new \Exception( sprintf( "Action '%s' is not found in '%s'", $this->action, $this->controller ), 404 );
			
			$action = $controller[ $this->controller ][ 'EXPORTS' ][ $this->action ];
			if ( !method_exists( $mod, $action ) )
				throw new \Exception( sprintf( "Action '%s' is not found in '%s'", $this->action, $this->controller ), 404 );
			
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
				
				$view->render( $ctx, $this->controller, $this->action );
			}
		}

		public function getLog()
		{
			return ob_get_contents();
		}
	}
}
