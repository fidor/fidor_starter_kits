<?php

$app_url        = "<APP_URL>";           # default http://localhost:8000/example.php
$app_id         = "<CLIENT_ID>";
$app_secret     = "<CLIENT_SECRET>";
$fidor_oauth_url= "<FIDOR_OAUTH_URL>";   # e.g Sandbox: https://aps.fidor.de/oauth / Live: https://apm.fidor.de/oauth
$fidor_api_url  = "<FIDOR_API_URL>";     # e.g Sandbox: https://aps.fidor.de / Live: https://api.fidor.de


$code = $_REQUEST["code"];

  # 1. redirect to authorize url
  if(empty($code)) {
    $dialog_url = $fidor_oauth_url . "/authorize?" .
                  "client_id=". $app_id .
                  "&redirect_uri=" . urlencode($app_url) .
                  "&state=1234&response_type=code";

    echo("<script> top.location.href='" . $dialog_url . "'</script>");
  }
  # 2. build url to get the access token
  $token_url = $fidor_oauth_url . "/token";

  $data = array('client_id' => $app_id,
                'client_secret' => $app_secret,
                'code' => $code,
                'redirect_uri' => urlencode($app_url),
                'grant_type' => 'authorization_code'
                );
  // use key 'http' even if you send the request to https://...
  $options = array(
      'http' => array(
          'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
          'method'  => 'POST',
          'content' => http_build_query($data),
      ),
  );
  # get the access_token, use PHP5 internal POST method
  $context  = stream_context_create($options);
  $resp = json_decode(file_get_contents($token_url, false, $context));

  echo( "<h2>Hello</h2>
         <i>May I present the access token response:</i>
         <blockquote>");
   print_r($resp);
   echo("</blockquote>
        <p>Now use the access token in the request header in your favorite PHP HTTP method or via CURL: </p>
        <blockquote>curl -v -H \"Authorization: Bearer ".$resp->access_token."\" -H \"Accept: application/vnd.fidor.de; version=1,text/json\" ".$fidor_api_url."/transactions
        </blockquote>");

?>