<?php
	
namespace Action;

Class Connect{
	public $con = NULL;
	public function __construct(){
		$this->con=mysqli_connect("mahirkoding.com:3306","ehunter","ehunter_ehunter","ehunter");
	}

	public function fetch($str){
		return mysqli_query($this->con, $str);
	}

	public function exec($str){
		return mysqli_query($this->con, $str);
	}
}