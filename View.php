<?php
namespace IFFramework
{

	use IFFramework\Core\Application;
	use IFFramework\Core\Context;
	use IFFramework\Core\Dispatcher;

	require_once 'IFFramework/Core/Application.php';
	require_once 'IFFramework/Core/Context.php';
	require_once 'IFFramework/Core/Dispatcher.php';

	abstract class View
	{

		public function __construct( Context $ctx )
		{}

		abstract public function render( Context $ctx, $controller, $action );
	}
}