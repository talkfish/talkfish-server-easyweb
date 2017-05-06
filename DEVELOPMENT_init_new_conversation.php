<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

print("<p>Hello, world!</p>");

print("<p>generating new instance</p>");

$endpoint_id_length = 5;
$endpoint_string_arr = array();
for($i=0;$i<$endpoint_id_length;$i++) {
   // mapping of possible randcharcodes
   //  1 to 26: abcdefghijklmnopqrstuvwxyz
   // 27 to 36: 0123456789
   $randcharcode = rand(1,36);
   $rand_char = '_'; // should not appear in target string
   if ($randcharcode>=1 && $randcharcode<=26) {
      $rand_char = chr(ord('a')+$randcharcode-1);
   }
   if ($randcharcode>=27 && $randcharcode<=36) {
      $rand_char = chr(ord('0')+$randcharcode-27);
   }
   print($i);
   print(":");
   print($randcharcode);
   print("->");
   print($rand_char);
   print("<br/>");
   $endpoint_string_arr[$i] = $rand_char;
}
$endpoint_string = implode($endpoint_string_arr);

print("<p>finally got: "); 
print($endpoint_string);
print("</p>"); 

print("<p>possible number of different combinations: ");
print(pow(36,$endpoint_id_length));
print("</p>"); 

print("<p>TODO: cp template to new instance</p>");



?>
