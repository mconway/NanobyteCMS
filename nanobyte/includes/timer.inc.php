<?php



class Timer
{
	private $start;
	private $end;
	
	public function __construct(){
		
	}
	
	public function startTimer(){
		$this->start = microtime(true);
	}
	
	public function stopTimer(){
		$this->end = microtime(true);
	}
	
	public function calcTime(){
		return $this->end - $this->start;
	}
}

?>