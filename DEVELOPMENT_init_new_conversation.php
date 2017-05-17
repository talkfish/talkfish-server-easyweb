<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

print("<h1>talkfish conversation creator</h1>");

print("<h2>generating new endpoint</h2>");


function generateRandomName($endpoint_id_length) {
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
      // print($i);
      // print(":");
      // print($randcharcode);
      // print("->");
      // print($rand_char);
      // print("<br/>");
      $endpoint_string_arr[$i] = $rand_char;
   }
   $endpoint_string = implode($endpoint_string_arr);
   return $endpoint_string;
}

$length = 1;
$prefix = "test2/";
$max_try_counts = 5;



$success = false;
$counter = 0;
do {
   $counter += 1;
   $endpoint_string_candidate = generateRandomName($length);
   $newpath = $prefix . $endpoint_string_candidate;
   if (!file_exists($newpath)) {
      mkdir($newpath,0775);
      $success = true;

      print("<p>"); 
      print("created directory " . $newpath . " at $counter run(s).");
      print("</p>"); 
   } 
} while ($counter < $max_try_counts and ! $success);

if (!$success) {
   print("<p>"); 
   print("ERROR: took $counter runs, but could not find an empty slot.");
   print("</p>"); 
}

print("<h2>copying template</h2>");
copy("template/index.php", "$newpath/index.php");
mkdir("$newpath/get",0775);
copy("template/get/current.txt", "$newpath/get/current.txt");


print("<h2>analyis</h2>");
print("<p>possible number of different combinations: ");
$combinations = pow(36,$length);
print("</p>"); 
print($combinations);
$dircontent = scandir($prefix);
if ($dircontent) {
   $currentamount = count($dircontent) -2;
   $fillrate = 100 * $currentamount / $combinations;
   print("<p>currentcount: $currentamount -> fillrate: $fillrate</p>");
}


print("<h2>TODO</h2>");
print("<p>TODO - Prio A: cp template to new instance</p>");
print("<p>TODO - Prio B: prevent multiple parallel executions by an exclusive lock</p>");
print("<p>TODO - Prio C: toplevel directories to group subdirs and prevent to many subdirectories</p>");

?>
