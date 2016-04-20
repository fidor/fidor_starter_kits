<?php

/**
 * Check the error code
 */
if ( empty( $_REQUEST['error'] ) OR ! in_array( $_REQUEST['error'], array( 'invalid_request', 'invalid_client', 'invalid_grant', 'unauthorized_client', 'unsupported_grant_type' ) ) ) {
    header( 'Location: index.php' );
    exit;
}

/**
 * Startup
 */
require_once 'setup.php';

/**
 * Alias Fidor SDK namespace
 */
use Fidor\SDK as Fidor;

/**
 * Load Fido API settings
 */
$settings = include 'config.php';

/**
 * Create Config instance. Check Config class for alternative calls.
 */
$config = Fidor\Config::fromArray( $settings );

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>FIDOR API Starter Kit - Error</title>
    
    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    
    <div class="container">
        <h1>Error</h1>

        <p>I was unable to retrieve an access-token.</p>

        <p>The reported error code was:</p>

        <p><?php echo $_REQUEST['error']; ?></p>

        <p>
            <a href="authorize.php" class="btn btn-primary">Retry to get a new access token</a>
        </p>
    </div>
    
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
</body>
</html>
