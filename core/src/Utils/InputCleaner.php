<?php
namespace Utils;

class InputCleaner
{
    public static function cleanParameters($input)
    {
        foreach ($input as $key => $value) {
            $cleaned = self::cleanParameter($value);
            $input[$key] = $cleaned;
        }

        return $input;
    }

    public static function cleanParameter($val)
    {
        $val = strip_tags($val, TAGS_TO_PRESERVE);
        /*
         /              # Start Pattern
        <             # Match '<' at beginning of tags
        (             # Start Capture Group $1 - Tag Name
                [a-z]         # Match 'a' through 'z'
                [a-z0-9]*     # Match 'a' through 'z' or '0' through '9' zero or more times
        )             # End Capture Group
        [^>]*?        # Match anything other than '>', Zero or More times, not-greedy (wont eat the /)
        (\/?)         # Capture Group $2 - '/' if it is there
        >             # Match '>'
        /i            # End Pattern - Case Insensitive
        */
        $val =  preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i", '<$1$2>', $val);
        return $val;
    }

    public static function escape($data)
    {
        return htmlentities($data, ENT_QUOTES, 'UTF-8');
    }
}
