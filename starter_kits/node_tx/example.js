// This is an example node.js app that demonstrates the use of the Fidor
// API to retrieve information about transactions executed by a user's
// customer.
//
// This sample intentionally does not require any further dependencies.
// We tried to keep things as simple as possible in order to illustrate the
// underlying mechanisms of the API.
//
// Using this code
// ---------------
//
// In order to use this code, a sample app needs to be installed in the
// Fidor App Manager. Settings for this app are displayed in the app
// manager an need to be transfered into the config below:

var fidor_config = {
  app_url        : "<APP_URL>",
  client_id      : "<CLIENT_ID>",
  client_secret  : "<CLIENT_SECRET>",
  fidor_api_url  : "<FIDOR_API_URL>"
}




// redirect the user to the OAuth authorization endpoint with the
// following params:
//   - client_id
//   - state
//   - response_type
//   - redirect_uri
function redirect_to_oauth(response){
  var redirect_uri = encodeURIComponent(fidor_config.app_url)
  var oauth_url = fidor_config.fidor_api_url+
                  "/oauth/authorize?client_id="+fidor_config.client_id+
                  "&state=123&response_type=code&"+
                  "redirect_uri="+redirect_uri
  response.writeHead(307, {"location" : oauth_url})
  response.end()
  return
}

// Execute a POST request against the OAUTH token endpoint
// in order to exchange: code, client_id, client_secret, 
// rerdirect_uri and grant_type for an auth_token.
function retrieve_access_token_from_code( code, cb ) {
  var oauth_url = url.parse(fidor_config.fidor_api_url)
  // where to send the data ...
  var postOptions = {
    method: "POST",
    path  : oauth_url.path+"/oauth/token",
    port  : oauth_url.port,
    host  : oauth_url.hostname
  }

  // ... what to send
  var postData = {
    code          : code,
    client_id     : fidor_config.client_id,
    client_secret : fidor_config.client_secret,
    redirect_uri  : encodeURIComponent(fidor_config.app_url),
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
      console.log(oauth_response)
      cb(oauth_response.error, oauth_response.access_token)
    })
  })

  token_request.on('error', function(e) {
    cb(e, null)
  })

  token_request.write(postData);
  token_request.end()
}


// Display a friendly message and links to the API Endpoints.
function renderWelcome(request, response, token) {
  response.writeHead(200, {"Content-Type" : "text/html"})
  var content = hello_template.replace(/{token}/g, token)
  console.log(content)
  console.log(token)
  content = content.replace(/{api_uri}/g, fidor_config.fidor_api_url)
  response.end(content)
}

// main http functionality
function listener (request, response) {

  var u    = url.parse(request.url)
  // reject everything but GET.
  if (request.method !== "GET" || u.pathname !== "/") {
    response.writeHead(403, "Forbidden")
    response.end()
    return
  }
  var code = querystring.parse(u.query)["code"]
  if (code) {
    retrieve_access_token_from_code( code, function (err, token) {
      if (err) {}
      renderWelcome(request, response, token)
    }) 
  } else {
    // we don't have an oauth `code` yet, so we need to
		// redirect the user to the OAuth provider to get one ...
    redirect_to_oauth(response)
    return
  }
}

// Execution starts here...

var url           = require("url")
var querystring   = require("querystring")
var http          = require("http")
var https         = require("http")

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
"	<i>retrieved <tt>access_token</tt>: {token} </i>"+
"	<p><a href='{api_uri}/transactions?access_token={token}'>Transactions</a></p>"+
"	<p><a href='{api_uri}/accounts?access_token={token}'>Accounts</a></p>"+
"</body>"+
"</html>"
