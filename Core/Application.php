<?php
namespace IFFramework\Core
{

	require_once 'IFFramework/Object.php';
	require_once 'IFFramework/Core/Context.php';
	require_once 'IFFramework/Core/Dispatcher.php';

	class Application extends \IFFramework\Object
	{

		private static $instance;

		private $params;

		private $config;

		private function __construct()
		{
			$this->params = array(
				'version' => '0.3.0',
				'basePath' => realpath( dirname( $_SERVER[ 'SCRIPT_FILENAME' ] ) ),
				'baseUri' => str_replace( $_SERVER[ 'DOCUMENT_ROOT' ], '', dirname( $_SERVER[ 'SCRIPT_FILENAME' ] ) ),
				'uriPath' => isset( $_SERVER[ 'PATH_INFO' ] ) ? $_SERVER[ 'PATH_INFO' ] : '',
				'debug' => defined( 'IFF_DEBUG' ) ? IFF_DEBUG : false
			);
			
			$conf_name = defined( 'IFF_CONFIG' ) ? IFF_CONFIG : basename( $this->basePath ) . '.conf';
			$this->config = $this->loadConfig( $this->basePath . DIRECTORY_SEPARATOR . $conf_name );
			
			$this->params[ 'title' ] = isset( $this->config->title ) ? $this->config->title : basename( $this->basePath );
		}

		private function loadConfig( $path )
		{
			$config = array();
			
			if ( file_exists( $path ) && ( $_config = parse_ini_file( $path ) ) )
			{
				foreach ( $_config as $key => $val )
				{
					// Convert $key to lowerCamelCase format
					$key = strtolower( $key );
					$toupper = create_function( '$c', 'return strtoupper($c[1]);' );
					$key = preg_replace_callback( '/_([a-z])/', $toupper, $key );
					
					$config[ $key ] = $val;
				}
			}
			else
			{
				throw new \Exception( "Failed to read configuration" );
			}
			
			return (object) $config;
		}

		public static function getInstance()
		{
			if ( !isset( static::$instance ) )
				static::$instance = new static();
			return static::$instance;
		}

		public function run()
		{
			$dispatcher = null;
			try
			{
				$dispatcher = new Dispatcher( array(
					'uriPath' => $this->uriPath,
					'controllerDir' => $this->getPath( isset( $this->config->controllerDir ) ? $this->config->controllerDir : 'controller' ),
					'viewDir' => $this->getPath( isset( $this->config->viewDir ) ? $this->config->viewDir : 'view' ),
					'defaultController' => isset( $this->config->defaultController ) ? $this->config->defaultController : 'root',
					'defaultAction' => isset( $this->config->defaultAction ) ? $this->config->defaultAction : 'index'
				) );
				
				$context = new Context( array(
					'baseUri' => $this->baseUri,
					'defaultView' => 'HTML', // TODO defaultView
					'modelDir' => $this->getPath( isset( $this->config->modelDir ) ? $this->config->modelDir : 'model' ),
					'config' => $this->config
				) );
				
				$dispatcher->runAction( $context );
				$dispatcher->renderView( $context );
			}
			catch ( \Exception $e )
			{
				error_log( sprintf( "[%s] %s%s (#%d)", $this->title, $e->getMessage(), $e->getPrevious() ? sprintf( ": %s", $e->getPrevious()->getMessage() ) : '', $e->getCode() ) );
				if ( defined( 'IFF_FORWARD_EXCEPTIONS' ) && IFF_FORWARD_EXCEPTIONS )
				{
					$dispatcher = null;
					throw $e;
				}
				else
				{
					ob_start();
					echo sprintf( "Application error: %s%s (#%d)\n", $e->getMessage(), $e->getPrevious() ? sprintf( ": %s", $e->getPrevious()->getMessage() ) : '', $e->getCode() );
					if ( $this->debug )
					{
						echo sprintf( "At line %d in %s\n", $e->getLine(), $e->getFile() );
						echo "\nStack trace:\n" . $e->getTraceAsString();
					}
					$context->response->code = $e->getCode();
					$context->response->body = ob_get_contents();
					ob_end_clean();
				}
			}
			finally
			{
				$dispatcher = null;
				http_response_code( $context->response->code );
				header( 'Content-Type: ', $context->response->contentType . '; charset=' . $context->response->encoding );
				echo $context->response->body;
			}
		}

		public function __get( $prop )
		{
			return isset( $this->params[ $prop ] ) ? $this->params[ $prop ] : parent::__get( $prop );
		}

		public function getPath( $rel )
		{
			return $this->basePath . DIRECTORY_SEPARATOR . $rel;
		}
	}
}