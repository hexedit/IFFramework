<?php

namespace IFFramework {

	abstract class Object
	{

		public function __get( $prop )
		{
			$get = "get_$prop";
			$_prop = "_$prop";
			
			if ( method_exists( $this, $get ) )
				return $this->$get();
			else if ( isset( $this->$_prop ) )
				return $this->$_prop;
			else
				throw new \Exception( "Property '$prop' not found" );
		}

		public function __set( $prop, $val )
		{
			$set = "set_$prop";
			if ( !method_exists( $this, $set ) ) throw new \Exception( "Property access violation - '$prop'" );
			$this->$set( $val );
		}

	}

}