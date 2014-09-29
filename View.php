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

		public function __construct( Application $app, Context $ctx, Dispatcher $disp )
		{
		}

		abstract public function process( Context $ctx, $module, $action );

	}

}