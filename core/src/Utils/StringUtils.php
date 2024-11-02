<?php
namespace Utils;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;

class StringUtils
{
    public static function randomString($length = 6)
    {
        $str = "";
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

	public static function validateEmail($email){
		$emailValidator = new EmailValidator();
		return $emailValidator->isValid($email, new RFCValidation());
	}
}
