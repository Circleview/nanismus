<?php
function postSignupToTwitter($user, $message, $url)
{

require_once('twitteroauth/twitteroauth.php');

	$consumer_key = '4WJOU7IkSXnMNuEtoEM9IA';
	$consumer_secret = '84VPyJgUzCIgSPSmcOAW0Q7BEUTQSa9OKVe9nGYpQ';
	$access_key = '515208942-29BXn6dh6GDsfd7OZu68kupz4Ro4vaXXGQoNyF16';
	$access_secret = 'rKlmMZebx6Ap32ag9vOQwrsGEfcV8mqSBLoAUeGIWG20q';
	$twitter = new TwitterOAuth (
               $consumer_key,  
               $consumer_secret, 
               $access_key, 
               $access_secret
               );
	$max = "140";
        //$link = get_fazzt_url($url); /* URL verkuerzen */
	$bitlink=strlen($url);
	$lenuser = strlen($user);
	
	if (strlen($message) > ($max-$bitlink-$lenuser)) 
    { 
  			$message = substr($message, 0, ($max-3)).'...';
	}
	$twitter->post('statuses/update', array('status' => $user.' '.utf8_encode($message).' '.$url));
	
    //echo "erledigt";	
}
//postSignupToTwitter("dies ist ein Tweet,","");
?>