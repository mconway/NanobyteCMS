<?php
	/*
	*Copyright (c) 2009, Michael Conway
	*All rights reserved.
	*Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
    *Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
   	*Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
	*Neither the name of the Nanobyte CMS nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
	*THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	*/
	
	class Form{
		
		public $name;
		public $method;
		public $action;
		public $elements = array();
		public $defaults = array();
		private $filters = array();
		private $rules = array();
		
		public function __construct($form_params){
			$this->name = $form_params['name'];
			$this->method = $form_params['method'];
			$this->action = $form_params['action'];
		}
		
		public function addElement($element){
			if(!isset($element['group'])){
				$element['group'] = 0;
			}
			if($element['type']=='header'){
				$this->elements[$element['group']]['header'] = $element['label'];
				return;
			}
			$this->elements[$element['group']]['elements'][$element['name']] = $element;
			
			if(isset($this->defaults[$element['name']])){
				$this->elements[$element['group']]['elements'][$element['name']]['value'] = $this->defaults[$element['name']];
			}
			
			if(($element['type']=='checkbox' || $element['type']=='radio') && $this->elements[$element['group']]['elements'][$element['name']]['value']==1){
				$this->elements[$element['group']]['elements'][$element['name']]['options']['checked'] = 'checked';
			}

//			if($elements[0]=='select'){
//				$this->generateSelect();
//			}
		}
		
		public function applyFilters(){
			foreach($this->filters as $filter_pair){
				foreach($filter_pair as $element=>$filter){
					if($element=="__ALL__"){
						foreach($_POST as $e){
							$filter($e);
						}
					}elseif(isset($_POST[$element])){
						$filter($_POST[$element]);
					}
				}
			}
		}
		
		public function addFilter($element,$filter){
			$this->filters[] = array($element=>$filter);
		}
		
		public function exportValues(){
			return $_POST;
		}
		
		public function process($callback){
			foreach($this->filters as $filter){
				array_walk_recursive($_POST,$filter);
			}
			call_user_func($callback,$_POST);
			return true;
		}
		
		public function setDefaults($defaults){
			foreach ($defaults as $name=>$default){
				$this->defaults[$name] = $default;
			}
		}
	
		public function validate(){
			$this->applyFilters();
			foreach($this->rules as $rule){
				
			}
			return true;
		}

	}