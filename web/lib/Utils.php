<?php

class Utils {

    public static function rotateArray($arr) {
        $sub = array_slice($arr, 1);
        $rotated = array_merge(array_slice($sub, 1), array_slice($sub, 0, 1));
        return array_merge(array_slice($arr,0,1),$rotated);
    }

}
