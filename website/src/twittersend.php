<?php
function postSignupToTwitter($message, $user, $url, $testON)
{

require_once('twitteroauth/twitteroauth.php');

// In Testmode there are other tokens used then in Productive Mode
if ($testON)
{
	$consumer_key = '4WJOU7IkSXnMNuEtoEM9IA';
	$consumer_secret = '84VPyJgUzCIgSPSmcOAW0Q7BEUTQSa9OKVe9nGYpQ';
	$access_key = '515208942-29BXn6dh6GDsfd7OZu68kupz4Ro4vaXXGQoNyF16';
	$access_secret = 'rKlmMZebx6Ap32ag9vOQwrsGEfcV8mqSBLoAUeGIWG20q';    
}
else
{
	$consumer_key = 'E4vWAqhSZlg8UyIwHDQ';
	$consumer_secret = '1DV0TucLm7ypjXAGthIFJzlx9a2KrdFZGzWKEkeRQw';
	$access_key = '497623117-WayZAinfYbulxIx880INTLHvsnhYCh7RIG1kvOxf';
	$access_secret = 'DzWKic0FcTtIkRh3DydUWQBJottWnJnMCeswQdVw0E6SE';    
}

	$twitter = new TwitterOAuth (
               $consumer_key,  
               $consumer_secret, 
               $access_key, 
               $access_secret
               );
	$max = "135";  // Länge, die maximal zulässig ist bevor Twitter die Nachricht
	// blockt. 
        //$link = get_fazzt_url($url); /* URL verkuerzen */
	$bitlink=strlen($url)+1;           // URL plus Leerzeichen	
	$lenuser = strlen($user)+1;        // User und Leerzeichen	
	$msglen = strlen($message)+1;      // Message und Leerzeichen
	
	if ($msglen > ($max-$bitlink-$lenuser)) 
    { 
        $message = substr($message, 0, ($max-$bitlink-$lenuser-3)).'...';
	}
	$twitter->post('statuses/update', array('status' => utf8_encode($message).' '.$user.' '.$url));
	
	//echo "verschickte Nachricht: $message $user $url <br />";
    echo "erledigt <br />";	
}
//postSignupToTwitter("dies ist ein Tweet,","");
?>