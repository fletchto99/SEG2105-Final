<?php

/**
 *  Details     :  A class containing various helper functions used throughout the application
 *  Author(s)   :   Matt Langlois
 *
 */
class Utils {

    /**
     * Circular shift all elements in the array to the right, except for the item at index 0
     *
     * @param array $arr The array being rotated
     * @return array The newly rotated array
     */
    public static function rotateArray($arr) {
        $tmp = $arr;//Prevent the main from being modified
        $sub = array_slice($tmp, 1);
        array_unshift($sub, array_pop($sub));
        return array_merge(array_slice($tmp,0,1), $sub);
    }

    /**
     * Finds the next 2^n number. Done by flooding all bits to 1, then adding one
     *
     * @param Integer $number The number to determine the next 2^n number
     * @return int
     */
    public static function getNextTwoPower($number) {
        $result = $number - 1;//Subtract 1 to handle the case that we are already a power of 2
        $result |= $result >> 1;//shift all bits to 1
        $result |= $result >> 2;//shift all bits to 1
        $result |= $result >> 4;//shift all bits to 1
        $result |= $result >> 8;//shift all bits to 1
        $result |= $result >> 16;//Support up to 32bit integer or 0x8000_0000
        return $result + 1;
    }

    /**
     * Determines the previous 2^n number. E.G. 9 would be 8. Done by finding the next power then shifting right by one bit
     *
     * @param int $number The number to find the previous 2^n number
     * @return int The previous 2^n number
     */
    public static function getPreviousTwoPower($number) {
        return Utils::getNextTwoPower($number) >> 1;
    }

    /**
     * Checks if a number is a valid power of two. Done using some basic logic operations
     * by performing the and between the original number and the original -1
     *
     * I.E. 8: 1000 & 0111 = 0
     *
     * @param int $number The number to check if its a power of tow
     * @return bool
     */
    public static function isPowerOfTwo($number) {
        return ($number & ($number - 1)) == 0;
    }

    /**
     * Determines the number of knockout rounds which will occur based on the number of
     * teams that are registered in the tournament. Note: The number of teams must be a
     * valid number which is n^2
     *
     * @param Integer $numTeams The number of teams registered in the tournament
     * @return int The number of rounds to determine a winning team
     */
    public static function calculateNumRounds($numTeams) {
        return intval(log($numTeams, 2));
    }

}
