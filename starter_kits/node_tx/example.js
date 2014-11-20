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
  app_port       : 3141, // you might want to change this to match your app_url
  app_url        : "<APP_URL>", // must also include the port e.g http://localhost:3141
  client_id      : "<CLIENT_ID>",
  client_secret  : "<CLIENT_SECRET>",
  fidor_api_url  : "<FIDOR_API_URL>"
}

// This app can be started by executing:
//
//    node example.js
//
// Once the app is running, it can be accessed on:
//
//    http://localhost:3141
//
// or on whatever port is configured above.
//
// The app checks whether an OAuth access token is available for the
// user, if not, the user is redirected to the OAuth endpoint. After
// successful authentication and authorization, the OAuth endpoint
// redirects the user back to the app. Specifically to:
//
//     http://localhost:3141/code
//
// This redirect will contain a query with the OAuth code. The app uses
// this code to request an OAuth access-token. The retrieved token is stored with
// the users session and is used to authenticate API requests.

var http        = require('http')
var url         = require('url')
var querystring = require('querystring')



// the actual call to the api.
// @param accessToken : OAuth access_token for this user.
// @param cb          : callback function(err, transactions)
function apiGetTransactions(accessToken, cb) {
  // URL endpoint to call to retrieve transactions.
  var tx_url = fidor_config.fidor_api_url+
               "/transactions?access_token="+
               accessToken

  var get = http.get(tx_url, function(res){
    // response may come in numerous chunks, we need to collect
    // them and reassemble the entire answer when all data has
    // been retrieved.
    var data = new Buffer(0)
    res.on('data', function(chunk){
      data = Buffer.concat([data, chunk])
    })
    res.on('end', function() {
      if (res.statusCode !== 200) {
        cb(data.toString(), null)
        return
      }
      var d = JSON.parse(data)
      cb(d.error, d.transactions)
    })
  })

  get.on('error', function(e) {
    cb (e, null)
  })
}

//
// The handle for the Apps /transactions endpoint.
// @param request  : http request object
// @param response : http response
//
function getTransactions(request, response) {
  var cookie_token = getCookie(request, "oauth_token")
  // if we don't have a token for this user already, redirect
  // the user to the OAuth server
  if (!cookie_token) {
    var oauth_url = fidor_config.fidor_api_url+"/oauth/authorize?"+
                    "client_id="+fidor_config.client_id+
                    "&redirect_uri="+fidor_config.app_url+"/code"
    response.writeHead(307, {"location" : oauth_url})
    response.end()
    return
  }

  // trade in the key we stored in the cookie for the actual oauth token.
  var oauth_token = getToken(cookie_token)

  // call the api with the OAuth token:
  apiGetTransactions(oauth_token, function (err, transactions) {
    if (err) {
      // 500 Server Error in case of any problems

      console.log(">>>error")
      console.log(err)
      console.log("<<<error")

      response.writeHead(500, "Borked.")
      response.end("Borked.")
      return
    }
    // if everything went well, dump the received json.
    response.writeHead(200, {"Content-Type": "application/json; charset=utf-8"})
    response.write(JSON.stringify(transactions, null, " "))
    response.end()

  })
}

// Trades the `code` we received from the OAuth server via client
// redirect for an actual access_token. To do this, the code needs to be
// send to the correct OAuth endppoint together with client_id and
// client_secret.
function getOAuthToken(code, cb) {
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
    client_secret : fidor_config.client_secret
  }
  postData = querystring.stringify(postData)

  var token_request = http.request(postOptions, function (res) {
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

// handler we provide to handle our user being redirected back
// to us from the OAuth server with the OAuth `code` in the
// query of the url.
function setOAuthToken(request, response) {
  var u = url.parse(request.url)
  var code = querystring.parse(u.query)["code"]

  // if the request does not contain a ?code=adsfasdfasdf
  // query, it's not valid.
  if (!code) {
    response.writeHead(400, "Bad Request")
    response.end()
    return
  }

  // exchange the code for a token ...
  getOAuthToken(code, function (err, token) {
    if (err) {
      console.log(">>>error")
      console.log(err)
      console.log("<<<error")

      response.writeHead(500, "Borked.")
      response.end("Borked.")
      return
    }

    // once we have the token, we store it in our app (see below). The
    // OAuth token is stored under a random key. We set this key in the
    // user's cookie in order to retrieve the access_token whenever we
    // may need it in the future without leaking the actual access_token
    // via the cookie

    var token_key = storeToken(token)
    setCookie(response, "oauth_token", token_key)

    // send the user back to the transactions url, this time, with a
    // valid access_token (via the cookie we just set)
    response.writeHead(307, {"location" : "/transactions"})
    response.end()
  })
}

// handler for all http requests to our app ...
function listener(req, resp) {
  // reject everything but GET.
  if (req.method !== "GET") {
    resp.writeHead(403, "Forbidden")
  }

  var u = url.parse(req.url)
  switch (u.pathname) {
    case "/":
      console.log("start page ...")
      resp.writeHead(200, {"Content-Type" : "text/html"})
      resp.end(hello_template)
      break
    case "/transactions":
      console.log("transactions ...")
      getTransactions(req, resp)
      break
    // utility code to remove cookies in order to force OAuth.
    case "/code":
      console.log("received code redirect ...")
      setOAuthToken(req, resp)
      break
    case "/clear_all_cookies":
      console.log("clearing cookies")
      clearCookies(req, resp)
      resp.writeHead(307, {"location": "/"})
      resp.end()
      break
    case "/clear_cookie":
      console.log("clearing oauth cookie")

      var key = getCookie(req, "oauth_token")
      deleteToken(key)

      clearCookie(resp, "oauth_token")
      resp.writeHead(307, {"location": "/"})
      resp.end()
      break
    default:
      console.log("unknown: "+u.path)
      resp.end()
  }

}

// start the server
var server = http.createServer(listener)
    server.listen(fidor_config.app_port)

console.log("listening on : "+fidor_config.app_port)



var hello_template = ""+
"<DOCTYPE html>" +
"<html>" +
"<head></head>" +
"<body>" +
"  <h1>Welcome to Transaction Getter!</h1>" +
"  <p><a href='/transactions'>Get Transactions</a></p>" +
"  <p><a href='/clear_cookie'>Clear Cookie</a></p>" +
"  <p><a href='/clear_all_cookies'>Clear All Cookies</a></p>" +
"</body>" +
"</html>"

// Code for setting and clearing cookies. Typically a framework would
// handle the intricacies of cookie handling, but we opted not to
// require any additional dependancies for this example so we need to
// handle cookies manually...

function setCookie(response, key, value) {
  response.setHeader("Set-Cookie", key+"="+value)
}

function clearCookie(response, key) {
  console.log("clearing: "+key)
  if (key.map) {
    key = key.map(function (k) {
      return k+"=delete; expires=Thu, 01 Jan 1970 00:00:00 GMT"
    })
  } else {
    key = key+"=delete; expires=Thu, 01 Jan 1970 00:00:00 GMT"
  }
  console.log(key)
  response.setHeader("Set-Cookie", key)
}

function clearCookies(request, response){
  clearCookie(response, Object.keys(getCookies(request)))
}


function getCookies(request) {
  var cookies = {}
  var c = request.headers["cookie"]
  if (c) {
    c.split(';').forEach(function(cookie){
      var c = cookie.split("=")
      cookies[c[0].trim()] = c[1]
    })
  }
  return cookies
}

function getCookie(request, key) {
  var cookies = getCookies(request)
  return cookies[key]
}

// token storage


var tokens = {}

function storeToken (token) {
  var key = ""
  for (var i =0 ; i!= 16; ++i) {
    key += Math.floor((Math.random() * 256)).toString(16)
  }
  tokens[key] = token
  return key
}
function getToken(key) {
  return tokens[key]
}

function deleteToken (key) {
  var tok = tokens[key]
  delete tokens[key]
  return tok
}
