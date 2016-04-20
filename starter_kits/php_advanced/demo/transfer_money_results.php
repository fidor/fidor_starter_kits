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
 *
 */
$config = Fidor\Config::fromArray( $settings );

/**
 * Add oauth parameters ( access token, refresh token and expiration time ) to config
 */
$config->setAccessToken( $_SESSION['oauth']['access_token'] )
       ->setRefreshToken( $_SESSION['oauth']['refresh_token'] )
       ->setExpiresIn( $_SESSION['oauth']['expires_in'] + $_SESSION['oauth']['auth_at'] );

/**
 * Check if token has expired and try refreshing it
 */
/*if ( $config->hasTokenExpired() ) {
    $auth = new Fidor\Authorization( $config );
    $resp = $auth->refresh( $_SESSION['oauth']['refresh_token'] );
    print_r( $resp );
    die();
}*/

/**
 * Transaction ID is required.
 *
 */
if ( empty( $_GET['id'] ) ) {
    header( 'Location: transfer_money.php' );
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>FIDOR API Starter Kit: Transfer (Response)</title>

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

        <h1>Fidor API Starter Kit: Transfer (Response)</h1>

        <p>
            The call &quot;POST /internal_transfer&quot; retrieved the following data:
        </p>

        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered table-condensed table-hover table-striped col-md-6">
                    <tbody>

                        <?php
                            foreach ( $_GET as $k => $v ) {
                                if ( ! in_array( $k, array( 'id', 'external_uid', 'account_id', 'user_id', 'receiver', 'amount', 'subject', 'state', 'created_at', 'updated_at' ) ) ) {
                                    continue;
                                }
                                echo '<tr><th scope="row" style="text-align: left;">' . $k . '</th><td>' . $v . '</td></tr>';
                            }
                        ?>

                    </tbody>
                </table>
            </div>
        </div>

        <p>You can now try the following: </p>

        <p>
            <a href="get_customers.php" class="btn btn-primary">Retrieve personal data: GET /customers</a>
        </p>

        <p>
            <a href="get_accounts.php" class="btn btn-primary">Retrieve personal data: GET /accounts</a>
        </p>

        <p>
            <a href="get_transactions.php" class="btn btn-primary">Retrieve personal data: GET /transactions</a>
        </p>

        <p>
            <a href="transfer_money.php" class="btn btn-primary">Transfer money...</a>
        </p>

        <p>Documentation reference: </p>
        <ul>
            <li><a href="http://docs.fidor.de/#internal-transfer---fidor-to-fidor" target="_blank">http://docs.fidor.de/#internal-transfer---fidor-to-fidor</a></li>
        </ul>

    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
