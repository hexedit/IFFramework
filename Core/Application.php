<?php

namespace IFFramework\Core
{

	require_once 'IFFramework/Object.php';
	require_once 'IFFramework/Core/Context.php';
	require_once 'IFFramework/Core/Dispatcher.php';

	class Application extends \IFFramework\Object
	{

		protected $params;

		protected $context;

		public function __construct( $params )
		{
			$this->params = $params;
			$this->context = new Context( $params );
		}

		public function run()
		{
			$dispatcher = new Dispatcher( $this, $this->context );
			$dispatcher->prepare();
			$dispatcher->run();
			$dispatcher->finish();
		}

		protected function get_title()
		{
			return $this->params['title'];
		}

		protected function get_defaultView()
		{
			return $this->params['default_view'];
		}

		protected function get_modulePath()
		{
			return $this->params['module_path'];
		}

		protected function get_viewPath()
		{
			return $this->params['view_path'];
		}

		protected function get_templatePath()
		{
			return $this->params['template_path'];
		}

		protected function get_tmpDir()
		{
			return $this->params['tmp_dir'];
		}

		protected function get_cacheDir()
		{
			return $this->params['cache_dir'];
		}

	}

}