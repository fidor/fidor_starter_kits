<?php

error_reporting( E_ALL );

/**
 * Set session lifetime to 24 hours for testing
 */
session_set_cookie_params( 24 * 60 * 60 );
session_start();

/*
 * Load class loader. You can remove this line when using composer.
 */
require_once '../Fidor/SDK/Autoload.php';
