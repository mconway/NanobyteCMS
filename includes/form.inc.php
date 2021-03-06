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
		private $errors;
		
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
		
		public function addFilter($element,$filter){
			$this->filters[$element] = $filter;
			//var_dump($this->filters);
		}
		
		public function addRule($rule,$element){
			$this->rules[$rule][] = $element;
		}
		
		public function applyFilters(){
			foreach($this->filters as $element=>$filter){
				if($element=="__ALL__"){
					foreach($_POST as $k=>$e){
						$_POST[$k] = $filter($e);
					}
				}elseif(isset($_POST[$element])){
					$_POST[$element] = $filter($_POST[$element]);
				}
			}
		}
		
		public function applyRules(){
			$tmp = true;
			foreach($this->rules as $rule=>$elements){
				if(!is_array($rule)){
					$rule = array($this,$rule);
				}
				foreach($elements as $element){
					if(call_user_func($rule,$element)==false){
						return false;
					}
				}
			}
			return $tmp;
		}
		
		public function exportValues(){
			return $_POST;
		}
		
		public function process($callback){
			$this->applyFilters();
			call_user_func($callback,$this->exportValues());
			return true;
		}
		
		public function setDefaults($defaults){
			foreach ($defaults as $name=>$default){
				$this->defaults[$name] = $default;
			}
		}
		
		public function validate(){
			$this->applyFilters();
			return $this->applyRules();
		}

		//RULES
		private function required($element){
			$Core = BaseController::getCore();
			if(empty($_POST[$element])){
				$Core->setMessage(ucfirst($element) . " is required","error");
				return false;
			}
			return true;
		}

		private function match($elements){
			if(is_array($elements)){
				$Core = BaseController::getCore();
				$comp = array_shift($elements);
				foreach($elements as $e){
					if($_POST[$comp] != $_POST[$e]){
						$Core->setMessage("The " . $comp . " does not match the confirmation " . $comp,"error");
						return false;
					}
				}
			}
			return true;
		}

	}