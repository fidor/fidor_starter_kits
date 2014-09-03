var http        = require('http')
var url         = require('url')
var querystring = require('querystring')

var fidor_config = {
  app_url        : "http://localhost:3001",
  client_id      : "96a1cb8cd65b7717",
  client_secret  : "484dab6add45dd0c2e494c74433e616e",
  fidor_oauth_url: "http://localhost:3000/api_sandbox/oauth",
  fidor_api_url  : "http://localhost:3000/api_sandbox/"
}

function getOAuthRedirect() {
  return fidor_config.fidor_oauth_url+"/authorize?client_id="+fidor_config.client_id+"&redirect_uri="+fidor_config.app_url+"/code"
}

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


function apiGetTransactions(accessToken, cb) {
  var tx_url = fidor_config.fidor_api_url+"/transactions?access_token="+accessToken
  
  var get = http.get(tx_url, function(res){
    // TODO != 200
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
    console.log(">error")
    console.log(e)
    console.log("<error")
  })
}

function getTransactions(request, response) {
  console.log(getCookies(request))
  var oauth_token = getCookie(request, "oauth_token")
  if (!oauth_token) {
    response.writeHead(307, {"location" : getOAuthRedirect()})
    response.end()
    // redirect to oauth
    return
  }
  // do get transactions request.
    console.log("here2")
    console.log(oauth_token)

  apiGetTransactions(oauth_token, function (err, transactions) {
    if (err) {
      console.log("error>>>")
      console.log(err)
      console.log("error>>>")
      response.writeHead(500, "Borked.")
      response.end("Borked.")
      return
    }
    response.writeHead(200, {"Content-Type": "application/json; charset=utf-8"})
    response.write(JSON.stringify(transactions, null, " "))
    response.end()

  })
}

function getOAuthToken(code, cb) {
  var oauth_url = url.parse(fidor_config.fidor_oauth_url)

  var postOptions = {
    method: "POST",
    path  : oauth_url.path+"/token",
    port  : oauth_url.port,
    host  : oauth_url.hostname
  }

  var postData = {
    code          : code,
    client_id     : fidor_config.client_id,
    client_secret : fidor_config.client_secret
  }
  
  var token_request = http.request(postOptions, function (res) {
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

  token_request.write(querystring.stringify(postData));
  token_request.end()
}

function setOAuthToken(request, response) {
  var u = url.parse(request.url)
  var code = querystring.parse(u.query)["code"]

  if (!code) {
    response.writeHead(400, "Bad Request")
  }
  
  getOAuthToken(code, function (err, token) {
    if (err) {
      console.log("error>>>")
      console.log(err)
      console.log("error>>>")
      response.writeHead(500, "Borked.")
      response.end("Borked.")
      return
    }
    setCookie(response, "oauth_token", token)
    response.writeHead(307, {"location" : "/transactions"})
    response.end()
  })
}

function listener(req, resp) {
  
  if (req.method !== "GET") {
    resp.writeHead(403, "Forbidden")
  }
  
  var u = url.parse(req.url)
  switch (u.pathname) {
    case "/":
      console.log("here")
      resp.writeHead(200, {"Content-Type" : "text/html"})
      resp.end(hello_template)
      break
    case "/transactions":
      console.log("transactions!")
      getTransactions(req, resp)
      break
    case "/code":
      console.log("received code redirect")
      setOAuthToken(req, resp)
      break
    case "/clear_all_cookies":
      console.log("clearing cookies")
      clearCookies(req, resp)
      resp.writeHead(307, {"location": "/"})
      resp.end()
      break
    default:
      console.log("unknown: "+u.path)
  }

}

var server = http.createServer(listener)
    server.listen(process.env.PORT || 3001)



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
