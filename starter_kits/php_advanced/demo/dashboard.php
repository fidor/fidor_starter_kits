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
 * Create API client
 */
$client = new Fidor\Client( $config );

/**
 * Load all accounts
 */
$accounts = $client->accounts->get();
if ( ! empty( $accounts['message'] ) ) {
    header( 'Location: error_token_expired.php?error=' . $accounts['message'] );
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>FIDOR API Starter Kit</title>

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
            Congratulation, here’s your access-token, I will use it for all theAPI calls: <strong><?php echo $_SESSION['oauth']['access_token']; ?></strong>. <br />
            It will expire on <strong><?php echo gmdate( 'r', ( $_SESSION['oauth']['auth_at']+$_SESSION['oauth']['expires_in'] ) ); ?></strong>.<br /><br />
            The refresh-token is <strong><?php echo $_SESSION['oauth']['refresh_token']; ?></strong>. You can use it to request anew access-token.
        </p>

        <h2>Data access</h2>
        <p>
            Cool, I have access to this account (GET api.sandbox.fidor.com/accounts):
        </p>

        <?php
            /**
             * Selecting the first account then show
             */
            $account = is_array( $accounts ) ? current( $accounts ) : array();
        ?>

        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered table-condensed table-hover table-striped col-md-6">
                    <tbody>
                        <tr>
                            <th scope="row">ID</th>
                            <td><?php echo array_key_exists( 'id', $account ) ? $account['id'] : '&nbsp;'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">IBAN</th>
                            <td><?php echo array_key_exists( 'iban', $account ) ? $account['iban'] : '&nbsp;'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">BIC</th>
                            <td><?php echo array_key_exists( 'bic', $account ) ? $account['bic'] : '&nbsp;'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Owner</th>
                            <td><?php echo array_key_exists( 'customers', $account ) ? ( $account['customers'][0]['first_name'] . ' ' . $account['customers'][0]['last_name'] ) : '&nbsp;'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <?php ?>

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
            <li><a href="http://docs.fidor.de/#accounts" target="_blank">http://docs.fidor.de/#accounts</a></li>
        </ul>


    </div> <!-- .container -->

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
