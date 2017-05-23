<?php

   $ENDPOINT_ID_LENGTH  = 5;
   $ENDPOINT_PATH_PREFIX = "ealj/";

   print("<h2>analyis</h2>");
   print("<p>possible number of different combinations: ");
   $combinations = pow(36,$ENDPOINT_ID_LENGTH);
   print("</p>"); 
   print($combinations);
   $dircontent = scandir($ENDPOINT_PATH_PREFIX);
   if ($dircontent) {
      $currentamount = count($dircontent) -2;
      $fillrate = 100 * $currentamount / $combinations;
      print("<p>currentcount: $currentamount -> fillrate: $fillrate</p>");
   }


?>
