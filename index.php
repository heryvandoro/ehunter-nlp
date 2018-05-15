<?php

include('autoload.php');

use NLP\Tokenizer;
use NLP\GenderClassification;
use Action\Connect;

printf("Processing . . . <br>");

$vacancy_id = $_GET['id'];
//Initialize Class
$tok = new Tokenizer();
$con= new Connect();
$gender = new GenderClassification();
//Query Criteria
$query = "Select Criteria from tasks where vacancy_id = '".$vacancy_id."'";
$res = $con->fetch($query);
$res_decode=NULL;
if(mysqli_num_rows($res) > 0){
    while($row = mysqli_fetch_assoc($res)) {
        $res_decode = $row["Criteria"];
        $res_decode = json_decode($res_decode);
    }
}
//Parsing to 1 variable
$isGender = false;
$isGPA = false;
$isBachelor = false;
$isSkill = false;
if (isset($res_decode->gender)) {
    $isGender = true;
    $gender_required = $res_decode->gender;
}
if (isset($res_decode->gpa)) {
    $isGPA = true;
    $gpa_required = $res_decode->gpa * 100;
}
if (isset($res_decode->bachelor)) {
    $isBachelor = true;
    $bachelor_required = $res_decode->bachelor;
}
if(isset($res_decode->skill)) {
    $isSkill = true;
    $skills = $res_decode->skill;
}

//Query Hunter
$query_hunter = "SELECT id,name,cv_raw FROM `hunter_vacancy` join hunters on hunters.id = hunter_vacancy.hunter_id where vacancy_id ='".$vacancy_id."'";
$res_hunter = $con->fetch($query_hunter);
$cv = [];
if(mysqli_num_rows($res_hunter) > 0){
    while($row = mysqli_fetch_assoc($res_hunter)) {
        array_push($cv, $row);
    }
}

//Declaration Res;
$res_id=[];
if($isGender){
    $res_gender=[];
}
if($isGPA){
    $res_gpa=[];
}
if($isBachelor){
    $res_bachelor = [];
}
if($isSkill){
    $res_skill = [];    
}


//Filter Id
foreach($cv as $key){
    array_push($res_id, $key["id"]);
}

//Filter Gender
if($isGender){
    foreach($cv as $key){
        $gen_tok = $tok->tokenize($key["name"]);
        $result_gender = $gender->detect($gen_tok[0]);
        if(strtolower($gender_required) == strtolower($result_gender["gender"])){
            array_push($res_gender, 1);
            //true 1 false 0
        }else{
            array_push($res_gender, 0);
        }
    }
}

//Filter Skill
if($isSkill){
    foreach($cv as $key){
        $skill_tok = $tok->tokenize($key["cv_raw"]);
        // $flag = 0;
        $res_filter = [];
        $res_temp = [];
        $flag = 0;
        foreach($skill_tok as $index => $key){
            foreach($skills as $skill_qualified){
                if ($skill_qualified == trim($skill_qualified) && strpos($skill_qualified, ' ') !== false) {
                    $size_qualified = count($tok->tokenize($skill_qualified));
                    $tok_qualified = $tok->tokenize($skill_qualified);
                    $temp = 0;
                    for ($i=0; $i < $size_qualified; $i++) { 
                        if(strtolower($tok_qualified[$i]) == strtolower($skill_tok[$i+$index]))
                        {
                            array_push($res_temp, strtolower($tok_qualified[$i]));
                            // array_push($res_temp, 1);
                            $temp+=1;
                        }
                    }
                }
                else{
                    if(strtolower($skill_qualified) == strtolower($key))
                    {
                        array_push($res_temp, strtolower($key));
                    }
                }
            }
        }
        $res_temp = array_unique($res_temp);
        if(empty($res_temp)){
            for ($i=0; $i < count($skills); $i++) { 
                array_push($res_filter, 0);
            }
        }else if(count($res_temp) == count($skills)){
            for ($i=0; $i < count($skills); $i++) { 
                array_push($res_filter, 1);
            }
        }else{
            foreach ($skills as $key => $value) {
                if(in_array(strtolower($value), $res_temp))
                    array_push($res_filter, 1);
                else 
                    array_push($res_filter, 0);
            }
        }
        array_push($res_skill, $res_filter);
    }
}

//Filter GPA
if($isGPA){
    $qualified_gpa = [];
    foreach ($cv as $key) {
        $gpa_tok = $tok->tokenize($key["cv_raw"]);
        $gpa = 0;
        foreach($gpa_tok as $key => $value)
        {
            if(strtolower($value) == strtolower("GPA"))
            {
                for($i = $key; $i < count($gpa_tok); $i++)
                {
                    if(ctype_digit($gpa_tok[$i]))
                    {
                        if((int)$gpa_tok[$i] >= $gpa_required && (int)$gpa_tok[$i] <= 400)
                            $gpa = 1;
                        break;
                    }
                }
            }
        }
        array_push($res_gpa, $gpa);
    }
}
//Filter Bachelor
if($isBachelor){
    $qualified_bachelor = [];
    foreach($cv as $key){
        $bachelor_tok = $tok->tokenize($key["cv_raw"]);
        $bachelor = 0;
        foreach($bachelor_tok as $key => $value){
            if(strtolower($value) == strtolower("Bachelor")){
                if(ctype_digit($bachelor_tok[$key-1]) && strlen($bachelor_tok[$key-1]) == 4){
                    $bachelor = 1;
                } else {
                    for($i = $key; $i < count($bachelor_tok); $i++)
                    {
                        if(ctype_digit($bachelor_tok[$i]))
                        {
                            if((strtolower($bachelor_tok[$i+1]) != strtolower("present") && strtolower($bachelor_tok[$i+1]) != strtolower("now") && strlen($bachelor_tok[$i+1]) == 4) || (strtolower($bachelor_tok[$i+2]) != strtolower("present") && strtolower($bachelor_tok[$i+2]) != strtolower("now") && strlen($bachelor_tok[$i+2]) == 4))
                                $bachelor = 1;
                            break;
                        }
                    }
                }
            }     
        }
        array_push($res_bachelor, $bachelor);
    }
}
$hasil = [];
$temp = "";
printf("Classification . . . <br>");
for ($i=0; $i < count($res_id); $i++) { 
    # code...
    $temp=[];
    $feeds=[];
    $flag =0;
    $flagSkill = 0;
    $nilai = 0;
    $status = "";
    if($isGender){
        $flagGender = 0;
        $nilai += (int) $res_gender[$i];
        if($res_gender[$i] == 0 ){
            array_push($feeds, "the gender requirement");
        }
        $flag +=1;
    }
    if($isBachelor){
        $flagBachelor = 0;
        $nilai += (int) $res_bachelor[$i];
        if($res_bachelor[$i] == 0 ){
            array_push($feeds, "the degree requirement");
        }
        $flag +=1;
    }
    if($isGPA){
        $flagGPA = 0;
        $nilai += (int) $res_gpa[$i];
        if($res_gpa[$i] == 0 ){
            array_push($feeds, "the GPA requirement");
        }
        $flag +=1;
    }
    if($isSkill){
        $flagSkill = 0;
        foreach ($res_skill[$i] as $key => $value) {
            $nilai += (int)$value;
            $flag +=1;
            if($value== 0 ){
                array_push($feeds, "the ".$skills[$key]." skill");
                $flagSkill +=1;
            }   
        }
    }
    if(count($feeds) == 0){
        $status = "Congratulation. You meet the requirement.";
    }else{
        $status = "We are sorry, you are not meet ";
        for ($j=0; $j < count($feeds); $j++) { 
            # code...
            if($j+1 != count($feeds))
                $status .= $feeds[$j].", ";
            else if(count($feeds) == 1)
                $status .= $feeds[$j]. ".";
            else if($j+1 == count($feeds))
                $status .= "and ".$feeds[$j].".";
        }
    }
    $percentage = (int)($nilai * 100 / ($flag));
    if($isGender){
        array_push($temp, [ "gender" => $res_gender[$i]]);
    }
    if($isBachelor){
        array_push($temp, [ "bachelor" => $res_bachelor[$i] ]);
    }
    if($isGPA){
        array_push($temp, [ "gpa" => $res_gpa[$i] ] );
    }
    if($isSkill){
        array_push($temp, [ "skill" => $res_skill[$i] ]);
    }
    array_push($temp, [ "id" => $res_id[$i] ]);
    $temp = ltrim(json_encode($temp),"[");
    $temp = rtrim($temp,"]");
    $temp = str_replace("},{",",",$temp);
    $query_result = "Update hunter_vacancy SET result = '".$temp."' where vacancy_id = '".$vacancy_id."' AND hunter_id ='".$res_id[$i]."'";
    $con->exec($query_result);  
    $query_result = "Update hunter_vacancy SET score = '".$percentage."' where vacancy_id = '".$vacancy_id."' AND hunter_id ='".$res_id[$i]."'";
    $con->exec($query_result);
    $query_result = "Update hunter_vacancy SET reason = '".$status."' where vacancy_id = '".$vacancy_id."' AND hunter_id ='".$res_id[$i]."'";
    $con->exec($query_result);
    $query_result = "Update vacancies SET status = '2' where id = '".$vacancy_id."'";
    $con->exec($query_result);

    if($isSkill){
        $percent = (int)($flagSkill * 100 / count($skills));
    }else
        $percent = 0;
    if($percent > 70){
        $feedback =  "Your skill does not meet the requirement. Please find other job with your current skill.";
    }
    else if($percent > 50){
        $feedback =  "Your skill barely meet the requirement. Please study more than the others";
    }else if(in_array("the degree requirement", $feeds) && count($feeds) == 1 ){
        $feedback =  "Your education degree is the only problem you have";
    }else if(in_array("the degree requirement", $feeds) && count($feeds) == 1 ){
        $feedback =  "Your GPA do not meet the requirement";
    }else if(in_array("the gender requirement", $feeds) && count($feeds) == 1 ){
        $feedback =  "Your gender is the only problem you have";
    }else if( in_array("the gender requirement", $feeds) && 
        in_array("the degree requirement", $feeds) && count($feeds) == 1 ){
        $feedback =  "Your gender and degree is not meet the requirement";
    }else if( in_array("the gender requirement", $feeds) && 
        in_array("the GPA requirement", $feeds) && count($feeds) == 1 ){
        $feedback =  "Your gender and gpa do not meet the requirement";
    }else if(in_array("the degree requirement", $feeds)){
        $feedback =  "I think you need to graduate first";
    }else if(in_array("the GPA requirement", $feeds)){
        $feedback =  "I think you need to maximize your skill with your current GPA";
    }else if($percent <= 50){
        $feedback = "You are at least meet the requirement. Congratulation";
    }else{
        echo "Thank you for applying in eHunter";
    }

    $query_result = "Update hunter_vacancy SET feedback = '".$feedback."' where vacancy_id = '".$vacancy_id."' AND hunter_id ='".$res_id[$i]."'";
    $con->exec($query_result);
}
printf("Success <br>");
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:3000/vacancies/".$vacancy_id."/email");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$output = curl_exec($ch);
curl_close($ch);