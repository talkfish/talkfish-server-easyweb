<?php

class Messageposter {
   /*
    * protocol - for alpha test only
    *
    * takes GET-Value for parameter m
    * reads current message amount from get/current.txt
    * creates a new file below get
    * format is m_000.txt (3 digits)
    * -> self cleaning ringbuffer with maximum 1000 files
    * digits counting upwards and wrapping starting again at 1
    */

   var $LOCKING_TIMEOUT =  2;
   var $MAXLENGTH       = 163;
   var $COUNTER         = "get/current.txt";
   var $COUNT_BORDER    = 193;
   var $PAD_LENGTH      =  3;


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


   function process($message) {
      $result = false;
      try {
         // open guard 
         $handleToCounter = fopen($this->COUNTER, "r+");
         if (!$this->getFileLock($handleToCounter)) { 
            return false; 
         }

         // determine current message count and move forward
         $current = intval(fgets($handleToCounter));
         if ($current<0 || $this->COUNT_BORDER<=$current) { 
            return false; 
         }
         $current = 1 + $current;
         if ($this->COUNT_BORDER == $current) { 
            $current = 0; 
         }

         rewind($handleToCounter);
         ftruncate($handleToCounter, 0);
         fwrite($handleToCounter, strval($current));

         $countedfilenamepart = str_pad(strval($current), 
                                 $this->PAD_LENGTH, "0", STR_PAD_LEFT);
         $fname = "get/m_".$countedfilenamepart.".txt";

         $fhandle = fopen($fname, "w");
         if ($fhandle) {
            // TODO: harden more - stripping_tags is not sufficient: ensure only valid length + valid chars
            $message = strip_tags($_GET['m']); 

            fwrite($fhandle, $message, $this->MAXLENGTH);
            fwrite($fhandle, "\n", 1);
            fflush($fhandle);
            if (fclose($fhandle)) {
               $result = true;
            }
         }

         if (!$this->releaseFileLock($handleToCounter)) { 
            $result = false; 
         }
         fclose($handleToCounter);
      } catch (Exception $e) {
         // TODO: log exception echo "<p>"+$e->getMessage()+"</p>";
         $result = false;
      }
      return $result;
   }

}

$poster = new MessagePoster();
if (isset($_GET['m'])) {
   if ($poster->process($_GET['m'])) {
      print("OK");
   } else {
      print("ERROR");
   }
}

?>
