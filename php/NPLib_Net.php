<?php
require_once("mail/htmlMimeMail.php");

function redirect($page) {
   if (isset($_ENV['HTTP_HOST'])) {
    $host  = $_ENV['HTTP_HOST'];
    if (endsWith($_ENV["SCRIPT_URL"], ".php"))
        $uri  = rtrim(dirname($_ENV["SCRIPT_URL"]), '/\\');
    else
        $uri  = rtrim($_ENV["SCRIPT_URL"], '/\\');
   } else {
     $host  = $_SERVER['HTTP_HOST'];
     if (endsWith($_SERVER["SCRIPT_NAME"], ".php"))
        $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    else
        $uri  = rtrim($_SERVER['PHP_SELF'], '/\\');
   }
	header("Location: http://$host$uri/$page");
	exit;
}

function sendMail($from, $to, $subject, $body) {
    $mail = new htmlMimeMail();
    $mail->setFrom($from);
    $mail->setSubject($subject);
    $mail->setText($body);
    if (is_array($to))
        $result = $mail->send($to);
    else
        $result = $mail->send(array($to));

    return $result;
}

function sendHTMLMail($from, $to, $subject, $body) {
    $mail = new htmlMimeMail();
    $mail->setFrom($from);
    $mail->setSubject($subject);
    $mail->setHTML($body);
    if (is_array($to))
        $result = $mail->send($to);
    else
        $result = $mail->send(array($to));

    return $result;
}

function get_referer() {
    if (isset($_SERVER['HTTP_REFERER']))
        $referer = $_SERVER['HTTP_REFERER'];
    else if (isset($_ENV['HTTP_REFERER']))
        $referer = $_ENV['HTTP_REFERER'];
    else 
        return null;
	$referer = split("/", $referer);
	$referer = $referer[sizeof($referer)-1];
	$index = strpos($referer, "?");
	if ($index > 0) {
		$referer = substr($referer, 0, $index);
	}
	return $referer;
}

function NP_url_decode($obj) {
        if (gettype($obj) == "array") {
                foreach ($obj as $k => $v) {
                        $obj[$k] = NP_url_decode($v);
                }
        } else if (gettype($obj) == "object") {
                foreach (get_object_vars($obj) as $k => $v) {
                        $obj->$k = NP_url_decode($v);
                }
        }
        if (gettype($obj) == "string") {
                return urldecode($obj);
        } else {
                return $obj;
        }
}

function NP_json_encode($obj) {
	if (gettype($obj) == "object")
		$obj = clone $obj;
	return json_encode(NP_UTF8_encode($obj));
}
?>
