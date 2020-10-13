<?php


$chnd = curl_init();
    curl_setopt ($chnd, CURLOPT_URL, "https://accounts.google.com/ServiceLogin?service=grandcentral&passive=1209600&continue=https://voice.google.com/signup&followup=https://voice.google.com/signup");
    // curl_setopt ($chnd, CURLOPT_POST, FALSE);
    curl_setopt ($chnd, CURLOPT_FOLLOWLOCATION, TRUE); 
    curl_setopt ($chnd, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($chnd, CURLOPT_SSL_VERIFYPEER, TRUE);

    curl_setopt ($chnd, CURLOPT_USERAGENT, 'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36');
    curl_setopt($chnd, CURLOPT_HTTPHEADER, array(
       // Headers here
    ));
  echo  $data = curl_exec($chnd);

?>