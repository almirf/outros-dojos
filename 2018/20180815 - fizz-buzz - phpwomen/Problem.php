<?php
class Problem
{
    public function fizzBuzz()
    {
        $arr=array();
        for ($a=1; $a<=100; $a++){
           
          array_push($arr, $a);  
          
          
        } 
        
        
        $arr[2]="Fizz";
        return $arr; 
    }
}