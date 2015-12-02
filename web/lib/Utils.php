<?php

class Utils {

    public static function rotateArray($arr) {
        $sub = array_slice($arr, 1);
        $rotated = array_merge(array_slice($sub, 1), array_slice($sub, 0, 1));
        return array_merge(array_slice($arr,0,1),$rotated);
    }

    public static function getNextPowerSquared($number) {
        $result = $number - 1;//Subtract 1 to handle the case that we are already a power of 2
        $result |= $result >> 1;//shift all bits to 1
        $result |= $result >> 2;//shift all bits to 1
        $result |= $result >> 4;//shift all bits to 1
        $result |= $result >> 8;//shift all bits to 1
        $result |= $result >> 16;//Support up to 32bit integer or 0x8000_0000
        return $result + 1;
    }

    public static function getPreviousPowerSquared($number) {
        return Utils::getNextPowerSquared($number) >> 1;
    }

    public static function isPowerOfTwo($number) {
        return ($number & ($number - 1) == 0);
    }

}
