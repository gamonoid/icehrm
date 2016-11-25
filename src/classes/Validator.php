<?php

class Validator
{
    public static function validateDate($val){
        return (DateTime::createFromFormat('Y-m-d', $val) !== false);
    }

    public static function validateDateTime($val){
        return (DateTime::createFromFormat('Y-m-d H:i:s', $val) !== false);
    }

    public function validateFloat($val){
        return filter_var($val, FILTER_VALIDATE_FLOAT);
    }


    public function validateNumber($val){
        return filter_var($val, FILTER_VALIDATE_FLOAT);
    }

    public function validateNumberOrEmpty($val){
        if(empty($val)){
            return true;
        }
        return filter_var($val, FILTER_VALIDATE_FLOAT);
    }

    public function validateRequired($val){
        if(empty($val)){
            return false;
        }
        return true;
    }

    public function validateEmail($val){
        return filter_var($val, FILTER_VALIDATE_EMAIL);
    }

    public function validateEmailOrEmpty($val){
        if(empty($val)){
            return false;
        }
        return filter_var($val, FILTER_VALIDATE_EMAIL);
    }
}