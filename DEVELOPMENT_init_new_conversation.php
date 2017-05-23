<?php
// ini_set('display_errors', 'On');
// error_reporting(E_ALL | E_STRICT);

class EndpointGenerator {
   //
   // TalkFish - Toplevel endpoint generator
   //
   // creates one endpoint per conversation and returns newly created path
   //
   // print("<p>TODO - Prio C: toplevel directories to group subdirs and prevent to many subdirectories</p>");
   //


   var $LOCKING_TIMEOUT =  2;
   var $COUNTER         = "conversationcounter.txt";
   var $ENDPOINT_ID_LENGTH  = 5;
   var $ENDPOINT_PATH_PREFIX = "ealj/";
   var $MAX_TRY_COUNTS = 5;


   function shortSleep() {
      usleep(round(rand(25, 100)*1000));
   }


   function getFileLock($fp) {
      $canWrite = false;
      $startTime = microtime(true);
      do {
         $canWrite = flock($fp, LOCK_EX);
         // If lock could not obtained sleep, 
         // to avoid collision and CPU load
         if (!$canWrite) shortSleep();
      } while ((!$canWrite) && 
               ((microtime(true)-$startTime) < $LOCKING_TIMEOUT));
         return $canWrite;
   }


   function releaseFileLock($fp) {
      $startTime = microtime(true);
      do {
         $canClose = flock($fp, LOCK_UN);
         if (!$canClose) shortSleep();
      } while ((!$canClose) &&
               ((microtime(true)-$startTime) < $LOCKING_TIMEOUT));
      return $canClose;
   }


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
         $endpoint_string_arr[$i] = $rand_char;
      }
      $endpoint_string = implode($endpoint_string_arr);
      return $endpoint_string;
   }


   function createEndpoint() {
      $result = false;
      $newpath = "";
      try {
         // open guard and increase counter 
         $handleToCounter = fopen($this->COUNTER, "r+");
         if (!$this->getFileLock($handleToCounter)) { 
            return false; 
         }

         // determine current message count and move forward
         $current = intval(fgets($handleToCounter));
         if ($current<0) { 
            return false; 
         }
         $current = 1 + $current;

         rewind($handleToCounter);
         ftruncate($handleToCounter, 0);
         fwrite($handleToCounter, strval($current));

         // try to create a new endpoint

         $success = false;
         $try_counter = 0;
         do {
            $try_counter += 1;
            $endpoint_string_candidate = $this->generateRandomName($this->ENDPOINT_ID_LENGTH);
            $newpath = $this->ENDPOINT_PATH_PREFIX . $endpoint_string_candidate;
            if (!file_exists($newpath)) {
               mkdir($newpath,0775);
               $success = true;
            } 
         } while ($try_counter < $this->MAX_TRY_COUNTS and ! $success);

         if ($success) {
            // copying template
            copy("template/index.php", "$newpath/index.php");
            mkdir("$newpath/get",0775);
            copy("template/get/current.txt", "$newpath/get/current.txt");
            $result = true;
         }

         // close guard
         if (!$this->releaseFileLock($handleToCounter)) { 
            $result = false; 
         }
         fclose($handleToCounter);

      } catch (Exception $e) {
         // TODO: log exception
         // echo "<p>"+$e->getMessage()+"</p>";
         $result = false;
      }
      if($result) {
         return $newpath;
      } else {
         return "";
      }
   }
}

$endpointGenerator = new EndpointGenerator();
$newendpoint = $endpointGenerator->createEndpoint();
if ("" != $newendpoint) {
   print($newendpoint);
} else {
   print("ERROR");
}
?>
