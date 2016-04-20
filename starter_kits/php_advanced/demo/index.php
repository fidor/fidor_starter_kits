<?php
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

    <title>FIDOR API - Starter Kit</title>

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
        <h1>Fidor API Starter Kit</h1>

        <p>
            With this small program you can test the basic functions of the Fidor API. Look into the code and learn how it works.
            You can also integrate the code into your own software.
        </p>

        <h2>Current configuration (you can change this in the code): </h2>

        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered table-condensed table-hover table-striped col-md-6">
                    <tbody>
                        <tr>
                            <th scope="row">Authorization</th>
                            <td><?php echo $config->getOAuthUrl(); ?></td>
                        </tr>
                        <tr>
                            <th scope="row">API</th>
                            <td><?php echo $config->getApiUrl(); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <p>
            <a href="authorize.php" class="btn btn-primary">Connect to Fidor and get new access token</a>
        </p>

        <p>Documentation reference: </p>
        <ul>
            <li><a href="http://docs.fidor.de/#understand-oauth" target="_blank">http://docs.fidor.de/#understand-oauth</a></li>
        </ul>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
