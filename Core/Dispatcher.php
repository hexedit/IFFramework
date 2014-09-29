<?php

namespace IFFramework\Core
{

	require_once 'IFFramework/Object.php';
	require_once 'IFFramework/Core/Response.php';
	require_once 'IFFramework/Core/Session.php';

	class Dispatcher extends \IFFramework\Object
	{

		protected $app;

		protected $context;
		
		protected $module;
		
		protected $action;

		public function __construct( Application $app, Context $context )
		{
			ob_start();

			$this->app = $app;
			$this->context = $context;
			$this->module = $context->module;
			$this->action = $context->action;
		}

		public function prepare()
		{
			global $modules;

			$this->context->view = $this->app->defaultView;

			if ( $this->module == null )
			{
				$this->context->res->redirect( $this->context->uri_for( '/' . $this->context->defaultModule . '/' ) );
				return;
			}
			include_once $this->app->modulePath . '/' . $this->module . '.php';
			isset( $modules[$this->module] ) or $this->no_module();

			in_array( $this->action, $modules[$this->module]['EXPORTS'] ) or $this->no_action();
		}

		public function run()
		{
			global $modules;

			$mod_class = $modules[$this->module]['CLASS_NAME'];
			$mod = new $mod_class();
			$action = $this->action;
			$mod->$action( $this->context, $this->context->args );
		}

		public function finish()
		{
			if ( !$this->context->res->body )
			{
				$view;
				try
				{
					include_once $this->app->viewPath . '/' . $this->context->view . '.php';
					$view = 'View_' . $this->context->view;
					$view = new $view( $this->app, $this->context, $this );
				} catch ( Exception $e )
				{
					die( 'Ошибка вызова представления' );
				}

				// Подготовка общих данных
				$this->context->stash->debug = ob_get_contents();
				$this->context->stash->runtime = microtime( true ) - $_SERVER["REQUEST_TIME_FLOAT"];

				$view->process( $this->context, $this->module, $this->action );
			}

			header( 'HTTP/1.1 ' . $this->context->res->code );
			header( 'Content-Type: ' . $this->context->res->content_type . ( $this->context->res->encoding ? '; charset=' . $this->context->res->encoding : '' ) );

			error_log( ob_get_clean() );
			ob_end_clean();

			echo $this->context->res->body;
		}

		protected function no_module()
		{
			echo ( 'Модуль "' . $this->module . '" неверный или отсутствует' );
			$this->error( 404 );
		}

		protected function no_action()
		{
			echo ( 'Действие "' . $this->action . '" не найдено в модуле "' . $this->module . '"' );
			$this->error( 404 );
		}

		public function error( $code )
		{
			$this->module = 'error';
			$this->action = $code;
			$this->context->res->code = $code;
			$this->finish();
			exit();
		}

	}

}
