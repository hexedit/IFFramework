<?php
namespace IFFramework
{

	abstract class Model
	{

		protected $context;

		public function __construct( $ctx )
		{
			$this->context = $ctx;
		}
	}
}