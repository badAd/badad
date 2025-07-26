<?php

/* Use by including:
// Generate the ridiculously long random string
require_once ('./includes/string_functions.inc.php');
$newString = longString(255);
$newString = longDashScoreString(255);
$newString = digitString(255);
*/

// Alphanumeric random string
function longString($length = 10) {
  // if (preg_match ('/[a-zA-Z0-9]$/i', $_GET['string']))
    $chrs = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $chrsL = strlen($chrs);
    $renderedString = '';
    for ($i = 0; $i < $length; $i++) {
        $renderedString .= $chrs[rand(0, $chrsL - 1)];
    }
    return $renderedString;
}

// Alphanumeric hyphen underscore random string
// if (preg_match ('/[a-zA-Z0-9-_]$/i', $_GET['string']))
function longDashScoreString($length = 10) {
    $chrs = '-_0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $chrsL = strlen($chrs);
    $renderedString = '';
    for ($i = 0; $i < $length; $i++) {
        $renderedString .= $chrs[rand(0, $chrsL - 1)];
    }
    return $renderedString;
}

// Digits random string
// if (preg_match ('/[0-9]$/i', $_GET['string']))
// if (filter_var($_POST['string'], FILTER_VALIDATE_INT, array('min_range' => 10, 'max_range' => 10)))
function digitString($length = 10) {
    $chrs = '0123456789';
    $chrsL = strlen($chrs);
    $renderedString = '';
    for ($i = 0; $i < $length; $i++) {
        $renderedString .= $chrs[rand(0, $chrsL - 1)];
    }
    return $renderedString;
}
