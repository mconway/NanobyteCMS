<?php

	interface Module{
		public function __construct();
		
		public function install();
		
		public function uninstall();
	}

?>