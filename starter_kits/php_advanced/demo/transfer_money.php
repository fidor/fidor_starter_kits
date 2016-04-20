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

$notices = array();

/*
 * Handling request for money transfer
 */
if ( isset( $_REQUEST['send-money'] ) ) {

    /**
     * Validation (ensure we have the mandatory information with correct format)
     */
    if ( '' === trim( $_REQUEST['account_id'] ) ) {
        $notices[] = '<p class="alert alert-danger">Your account ID is required</p>';
    }
    if ( '' === trim( $_REQUEST['receiver'] ) ) {
        $notices[] = '<p class="alert alert-danger">Receiver is required</p>';
    }
    if ( '' === preg_replace( '/[\.0\s]/s', '', trim( $_REQUEST['amount'] ) ) ) {
        $notices[] = '<p class="alert alert-danger">Amount is required</p>';
    } elseif ( ! preg_match( '/^\d+(\.\d+)?$/', trim( $_REQUEST['amount'] ) ) ) {
        $notices[] = '<p class="alert alert-danger">Amount is not a valid number</p>';
    } elseif ( $_REQUEST['amount'] < 1 ) {
        $notices[] = '<p class="alert alert-danger">1&euro; minimum required</p>';
    }
    if ( '' === trim( $_REQUEST['external_uid'] ) ) {
        $notices[] = '<p class="alert alert-danger">External UID is required</p>';
    }

    /**
     * No validation errors, proceed with transaction
     */
    if ( count( $notices ) === 0 ) {

        /**
         * Create API client
         */
        $client = new Fidor\Client( $config );

        /**
         * Send money
         */
        $resp = $client->transfers->internal->create( array(
            'account_id'    => $_REQUEST['account_id'],
            'receiver'      => $_REQUEST['receiver'],
            'external_uid'  => $_REQUEST['external_uid'],
            'amount'        => $_REQUEST['amount'] * 100, // e.g.: 100 for 1 EUR
            'subject'       => $_REQUEST['subject'],
        ) );

        if ( ! empty( $resp['id'] ) ) {
            header( 'Location: transfer_money_results.php?' . http_build_query( $resp ) );
            exit;
        }

        /**
         * If transaction failed show errors to user.
         */
        if ( isset( $resp['errors'] ) && ! empty( $resp['message'] ) ) {
            $notices[] = sprintf( '<p class="alert alert-danger">Transfer failed - Reason: <strong>%s</strong> (Code: %s)</p>', $resp['message'], $resp['code'] );
            //echo Fidor\Client::get_debug_log();
            //exit;
        } elseif ( ! empty( $resp['message'] ) ) {
            header( 'Location: error_token_expired.php?error=' . $resp['message'] );
            exit;
        }
    }
}

$client = new Fidor\Client( $config );
$current_account = $client->users->current();
if ( ! empty( $current_account['message'] ) ) {
    header( 'Location: error_token_expired.php?error=' . $current_account['message'] );
    exit;
}
$accounts = $client->accounts->get();
$account = current( $accounts );
$currency_code = 'EUR';
$currency_html = '&euro;';
if ( ! empty( $account['currency'] ) && 'GBP' === $account['currency'] ) {
    $currency_code = 'GBP';
    $currency_html = '&pound;';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>FIDOR API Starter Kit: Transfer</title>

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

        <h1>Fidor API Starter Kit: Transfer</h1>

        <p class="alert alert-info">Be careful! If you are connected to the live system this will take real money from your account send it to somewhere!</p>

        <?php
            if ( ! empty( $notices ) ) {
                foreach ( $notices as $notice ) {
                    echo $notice;
                }
            }
        ?>

        <p>
            The call &quot;POST /internal_transfers&quot; with the following data:
        </p>

        <div class="row">
            <div class="col-md-8">

                <form action="" method="post" class="form-horizontal">

                    <div class="form-group">
                        <label for="account_id" class="col-sm-4 control-label">(your) account_id</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="account_id" id="account_id" value="<?php echo ( ! empty( $_REQUEST['account_id'] ) ? $_REQUEST['account_id'] : $current_account['id'] ); ?>" placeholder="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="receiver" class="col-sm-4 control-label">receiver (nickname, e-mail, etc...)</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="receiver" id="receiver" value="<?php echo ( ! empty( $_REQUEST['receiver'] ) ? $_REQUEST['receiver'] : '' ); ?>" placeholder="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="amount" class="col-sm-4 control-label">amount (<?php echo $currency_code; ?>)</label>
                        <div class="col-sm-6">
                            <div class="input-group">
                                <div class="input-group-addon"><?php echo $currency_html; ?></div>
                                <input type="text" class="form-control" name="amount" id="amount" value="<?php echo ( ! empty( $_REQUEST['amount'] ) ? $_REQUEST['amount'] : '' ); ?>" placeholder="">
                                <div class="input-group-addon">.00</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="subject" class="col-sm-4 control-label">subject</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="subject" id="subject" value="<?php echo ( ! empty( $_REQUEST['subject'] ) ? $_REQUEST['subject'] : '' ); ?>" placeholder="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="external_uid" class="col-sm-4 control-label">external uid</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="external_uid" id="external_uid" value="<?php echo ( ! empty( $_REQUEST['external_uid'] ) ? $_REQUEST['external_uid'] : Fidor\Transfers::make_external_uid() ); ?>" placeholder="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-4">
                          <button type="submit" name="send-money" id="send-money" class="btn btn-danger">Send money (you should know what you do)!</button>
                          <br /><br /><a href="dashboard.php" class="btn btn-primary">cancel and go back</a>
                        </div>
                    </div>

                </form>

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
