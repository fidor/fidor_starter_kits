<?php

  $app_url = "<APP_URL>";                   # default http://localhost:8000/example.php
  $app_id = "<CLIENT_ID>";
  $app_secret = "<CLIENT_SECRET>";
  $fidor_oauth_url = "<FIDOR_OAUTH_URL>";   # e.g https://fidor.com/api_sandbox/oauth
  $fidor_api_url = "<FIDOR_API_URL>";       # e.g https://fidor.com/api_sandbox vs /api

  $code = $_REQUEST["code"];
  # 1. redirect to authorize url
  if(empty($code)) {
    $dialog_url = $fidor_oauth_url . "/authorize?" .
                  "client_id=". $app_id .
                  "&redirect_uri=" . urlencode($app_url);

    echo("<script> top.location.href='" . $dialog_url . "'</script>");
  }
  # 2. build url to get the access token
  $token_url = $fidor_oauth_url . "/token";

  $data = array('client_id' => $app_id,
                'client_secret' => $app_secret,
                'code' => $code,
                'redirect_uri' => urlencode($app_url)
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

  # 3. GET Data e.g.info about current user
  $usr_url = $fidor_api_url . "/users/current?access_token=" . $resp->access_token;
  $user = json_decode(file_get_contents($usr_url));
  $transactions_url = $fidor_api_url . "/transactions?access_token=" . $resp->access_token;
  echo( "<h2>Hello " . $user->email . "</h2>
         <i>May i present the access token response:</i>
         <blockquote>");
   print_r($resp);
   echo("</blockquote>
        <p>Now use the access token in <br> <a href='" . $transactions_url . "'>".$transactions_url."</a></p>");

?>