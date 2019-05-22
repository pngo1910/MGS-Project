<?php



$expiry = strtotime('2017-05-01');
                //$date = new DateTime();
                $date = time();
                $date = $expiry < $date ? $date :  $expiry;
                //Set the new date a year from the day of the expiry date
                $newDate = date('Y-m-d', strtotime("+1 year", $date));





echo '--------';
echo $newDate;
echo '--------';
?>
