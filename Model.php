<?php

namespace IFFramework
{

	abstract class Model
	{

		protected $params;

		public function __construct( $params )
		{
			$this->params = $params;
		}

	}

}