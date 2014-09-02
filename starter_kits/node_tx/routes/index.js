var express  = require('express');
var url      = require('url');
var http     = require('http');
var querystr = require('querystring');

var fidor_api = require('../lib/fidor_api.js')

var router   = express.Router();






/* GET home page. */
router.get('/', function(req, res) {
  res.render('index', { title: 'Fidor Transaction Viewer' });
});

router.get('/transactions', function(req,res) {
  // request will first arrive with no cookie and no code
  //    redirect to oauth. As a result we will receive a 
  // second request with no cookie and a code. trade this code
  //    for a token and set the token in the cookie

  if (!req.cookies.token && !req.query.code ) {
    console.log("redirecting to OAuth")
    res.redirect(307, fidor_api.getOAuthRedirectUrl(req.path))
    return
  }

  if (req.query.code) {
    console.log("converting code to token")
    fidor_api.getOAuthToken(req.query.code, function(err, token) {
      res.cookie("token", token)
      res.redirect(307, req.path)
    })
    return
  }

  
  var token = req.cookies.token
  // TODO if ! token

  console.log("servicing request with token: "+token)

  fidor_api.getTransactions(token, function(err, txs) {
    if (err) {
      res.status(500).send(err)
    } else {
      res.render('transactions', {transactions: txs})
    }
  })

  // have session?
    // redirect to token url

})

//////////////////////////////////////////////////////////
// DEBUG
//////////////////////////////////////////////////////////

// clear the cookie we set
router.get('/clear_cookies', function (req, res) {
  if (req.cookies.token) {
    res.clearCookie("token")
    res.redirect(307, '/')
  }
})

// clear all cookies ( app manager in debug probably running on
// same domain)
router.get('/clear_all_cookies', function (req, res) {

  for (var p in req.cookies) {
    console.log(p)
    res.clearCookie(p)
  }
  res.redirect(307, '/')
})


module.exports = router;
