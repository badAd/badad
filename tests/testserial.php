<?php
echo "<html>

<body>";

$lastSerial = "a";

function increment(&$string){
    $last_char=substr($string,-1);
    $rest=substr($string, 0, -1);
    switch ($last_char) {
    case '':
        $next= 'a';
        break;
    case 'z':
        $next= 'A';
        break;
    case 'Z':
        $next= '0';
        break;
    case '9':
        increment($rest);
        $next= 'a';
        break;
    default:
        $next= ++$last_char;
    }
    $string=$rest.$next;
}

// Increment the serial number
increment($lastSerial);
echo "$lastSerial";










echo "</body>
</html>";
?>
