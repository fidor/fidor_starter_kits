// This is an example node.js app that demonstrates the use of the Fidor
// API to retrieve information about transactions executed by a user's
// customer.
//
// This sample intentionally does not require any further dependencies.
// We tried to keep things as simple as possible in order to illustrate the
// underlying mechanisms of the API. This comes at the expense of having
// to handled cookies and session in the sample...
//
// Using this code
// ---------------
//
// The provided source code is divided into three sections:
// 1.) Handling OAuth calls
// 2.) Cookie and Session Handling
// 3.) Handling Browser Requests
//
// In order to use this code, a sample app needs to be installed in the
// Fidor App Manager. Settings for this app are displayed in the app
// manager an need to be transfered into the config below, if you
// downloaded the code directly from the Fidor App Manager, these
// settings should be filled in for you:

var fidor_config = {
  app_url        : "<APP_URL>",
  client_id      : "<CLIENT_ID>",
  client_secret  : "<CLIENT_SECRET>",
  fidor_api_url  : "<FIDOR_API_URL>",
  fidor_oauth_url: "<FIDOR_OAUTH_URL>"
}


/************************************************************************
/* BEGIN: OAuth Calls
************************************************************************/

//
// redirect the user to the OAuth authorization endpoint with the
// following params:
//   - client_id
//   - state
//   - response_type
//   - redirect_uri
//
function redirect_to_oauth(response, target_endpoint){
  var _redirect_uri = fidor_config.app_url+"/oauth?ep="+target_endpoint
  var redirect_uri = encodeURIComponent(_redirect_uri)
  var oauth_url = fidor_config.fidor_oauth_url+
                  "/authorize?client_id="+fidor_config.client_id+
                  "&state=123&response_type=code&"+
                  "redirect_uri="+redirect_uri
  response.writeHead(307, {"location" : oauth_url})
  response.end()
  return
}

//
// Execute a POST request against the OAUTH token endpoint
// in order to exchange: code, client_id, client_secret, 
// redirect_uri and grant_type for an auth_token.
//
// This corresponds to OAuth 3.2 Token Endpoint / 4.1.3 Access Token
// Request.
//
// Parameter:
// - the code that was returned from the Authorization Endpoint
// - the target endpoint (transactions/ or accounts/) that was requested
//   in order to reconstruct the 'redirect-uri' parameter of the
//   Authorization call.
// - callback(error, access_token)
//
function retrieve_access_token_from_code( code, target_endpoint, cb ) {
  var oauth_url = url.parse(fidor_config.fidor_oauth_url)
  
  // where to send the data ...
  var postOptions = {
    method: "POST",
    path  : oauth_url.path+"/token",
    port  : oauth_url.port,
    host  : oauth_url.hostname
  }

  // ... what to send
  var redirect_uri = fidor_config.app_url+"/oauth?ep="+target_endpoint
  var postData = {
    code          : code,
    client_id     : fidor_config.client_id,
    client_secret : fidor_config.client_secret,
    redirect_uri  : encodeURIComponent(redirect_uri),
    grant_type    : "authorization_code"
  }
  postData = querystring.stringify(postData)

	var http_module   = oauth_url.protocol == "https:" ? https : http

  var token_request = http_module.request(postOptions, function (res) {
    // collect the data chunks we received and reassemble them
    // on request end ...
    var data = new Buffer(0)
    res.on('data', function(chunk) {
      data = Buffer.concat([data, chunk])
    })

    res.on('end', function() {
      var oauth_response = JSON.parse(data)
      cb(oauth_response.error, oauth_response.access_token)
    })
  })

  token_request.on('error', function(e) {
    cb(e, null)
  })

  token_request.write(postData);
  token_request.end()
}

/************************************************************************
/* END: OAuth Calls
************************************************************************/


/************************************************************************
/* BEGIN: Cookie & Session Handling
************************************************************************/
var COOKIE_NAME="NODE_SESSION"
var sessions = {}

// retrieve the random session_id from the cookie.
function getSession(req) {
  var cookies = req.headers.cookie
  var session = null
  if (cookies) {
    var cookieArr = cookies.split(";")
    for (var i = 0 ; i != cookieArr.length ; ++i) {
      var keyValue = cookieArr[i].split("=")
      if (keyValue && keyValue.length == 2 && keyValue[0].trim() == COOKIE_NAME) {
        session = keyValue[1]
        break
      } 
    }
  }
  return session
}
function getAccessTokenFromSession(req) {
  var session = getSession(req)
  var access_token = null
  if (session) {
    access_token = sessions[session]
  }
  return access_token
}
function createSession(resp, access_token) {
  function randomString(len) {
    var hex = "0123456789abcdef"
    var rnd = ""
    for (var i = 0; i!= len; ++i) {
      rnd += hex[Math.floor(Math.random()*hex.length)]
    }
    return rnd
  }
  var rnd = randomString(20)
  sessions[rnd] = access_token
  resp.setHeader("Set-Cookie", COOKIE_NAME+"="+rnd)
}
function removeSession(req, resp) {
  // find cookie
  var session = getSession(req)
  // delete from sessions
  delete sessions[session]
  // set expired
  resp.setHeader("Set-Cookie", COOKIE_NAME+"=nothing; expires=Thu, 01 Jan 1970 00:00:00 GMT")
}

/************************************************************************
/* END: Cookie & Session Handling
************************************************************************/

/************************************************************************
/* BEGIN: HTTP Handling
************************************************************************/

//
// Display a friendly message and links to the API Endpoints.
//
function renderWelcome(request, response) {
  response.writeHead(200, {"Content-Type" : "text/html"})
  response.end(hello_template)
}




function render (endpoint, req, res) {
  //
  // call the api endpoint and pipe the response from the API back to
  // the caller
  //
  function pipeApi(endpoint, access_token, res) {
    var api_endpoint = url.parse(fidor_config.fidor_api_url+endpoint)
    var http_module   = api_endpoint.protocol == "https:" ? https : http
    var http_options = {
      hostname: api_endpoint.hostname,
      path: api_endpoint.path,
      port: api_endpoint.port,
      method: "GET",
      headers: {
        "Authorization": "Bearer "+access_token
      }
    }

    var api_request = http_module.request(http_options, function(api_response) {
      res.setHeader('Content-Type', api_response.headers['content-type'])
      if (api_response.statusCode == 400) { // access_token has expired.
        handleLogout(req, res, endpoint)
        return
      }
      res.writeHead(api_response.statusCode, api_response.statusMessage)
      api_response.on('data', function(chunk) {
        res.write(chunk)
      })
      api_response.on('end', function(){
        res.end()
      })
    })

    api_request.on('error', function (err) {
      res.writeHead(500, {'Content-Type': 'text/plain'})
      res.end(err.toString())
    })
    api_request.end()
  }

  //
  // utility to check whether access token is via session cookie, if not
  // redirects account holder to OAuth Authorization Endpoint.
  //
  function getAccessToken(redirect, req, res, cb) {
    var accesstoken = getAccessTokenFromSession(req)
    if (!accesstoken) {
      // start OAuth 
      redirect_to_oauth(res, redirect)
    } else {
      cb(null, accesstoken)
    }

  }

  getAccessToken(endpoint, req, res, function (err, accesstoken) {
    if (err) {
        res.writeHead(500, {'Content-Type': 'text/plain'})
        res.end(err.toString())
        return
    }
    pipeApi(endpoint, accesstoken, res)
  })
}

function renderTransactions (req, res){
  render("/transactions", req, res)
}
function renderAccounts (req, res){
  render("/accounts", req, res)
}

//
// retrieve the 'code' parameter from the redirect url the
// OAuth Authorization returns to the user's browser
//
function handleOAuthCallback(req, res) {
  var u      = url.parse(req.url)
  var query  = querystring.parse(u.query)
  var code   = query["code"]
  var target = query["ep"]

  if (code && target) {
    retrieve_access_token_from_code( code, target, function (err, token) {
      if (err) {
        res.writeHead(500, {'Content-Type': 'text/plain'})
        res.end(err.toString())
        return
      }
      createSession(res, token)
      res.writeHead(307, {"location" : target})
      res.end()
    }) 
  } else {
    res.writeHead(500, {'Content-Type': 'text/plain'})
    res.end("missing code or target")
  } 
}

function handleLogout(req, res, endpoint) {
  // clear session locally and in browser
  removeSession(req, res)
  res.writeHead(307, {"location" : endpoint})
  res.end()
}

function listener (request, response) {
  var u = url.parse(request.url)
  
  if (request.method !== "GET") {
    response.writeHead(403, "Forbidden")
    response.end()
    return
  }

  switch(u.pathname) {
    case "/":
      renderWelcome(request, response)
    break
    case "/transactions":
      renderTransactions(request, response)
    break
    case "/accounts":
      renderAccounts(request, response)
      break
    case "/oauth":
      handleOAuthCallback(request, response)
    break
    case "/logout":
      handleLogout(request, response, "/")
    break
    default:
      response.writeHead(404, "Not Found")
  }

}

// Execution starts here...

var url           = require("url")
var querystring   = require("querystring")
var http          = require("http")
var https         = require("https")

var _url = url.parse(fidor_config.app_url)
fidor_config.app_port = _url.port

// start the server
var server = http.createServer(listener)
    server.listen(fidor_config.app_port)

console.log("listening on : "+fidor_config.app_url)


var hello_template = ""+
"<html>"+
"<head>"+
"</head>"+
"<body>"+
"	<h1>Welcome!</h1>"+
"	<p><a href='/transactions'>Transactions</a></p>"+
"	<p><a href='/accounts'>Accounts</a></p>"+
"	<p><a href='/logout'>Log Out</a></p>"+
"</body>"+
"</html>"
