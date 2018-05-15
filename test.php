<?php
	if(in_array("the gender requirement", $feeds) && count($feeds) == 1 ){
     	echo "We are sorry. Your gender is the only problem you have";
    }else if((in_array("the degree requirement", $feeds) && count($feeds) == 1 ){){
    	echo "We are sorry. Your education degree is the only problem you have";
    }else if((in_array("the degree requirement", $feeds) && count($feeds) == 1 ){){
    	echo "We are sorry. Your GPA do not meet the requirement";
    }else if( in_array("the gender requirement", $feeds) && 
    	in_array("the degree requirement", $feeds) && count($feeds) == 1 ){
    	echo "We are sorry. Your gender and degree is not meet the requirement";
    }else if( in_array("the gender requirement", $feeds) && 
    	in_array("the GPA requirement", $feeds) && count($feeds) == 1 ){
    	echo "We are sorry. Your gender and gpa do not meet the requirement";
    }