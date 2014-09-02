
var http        = require('http')
var querystring = require('querystring')
var url = require('url')

var config

function init(cfg) {
  config = cfg
}

function getConfig() {
  return config
}


function getOAuthRedirectURL(redirect_path) {
  return config.fidor_oauth_url+"/authorize?client_id="+config.client_id+"&redirect_uri="+config.app_url+redirect_path
}

function getOAuthToken(code, cb) {
  var oauth_url = url.parse(config.fidor_oauth_url)

  var postOptions = {
    method: "POST",
    path  : oauth_url.path+"/token",
    port  : oauth_url.port,
    host  : oauth_url.hostname
  }

  var postData = {
    code          : code,
    client_id     : config.client_id,
    client_secret : config.client_secret
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
    console.log(">>> error")
    console.log(e)
    console.log("<<< error")
  })

  token_request.write(querystring.stringify(postData));
  token_request.end()
}

function getTransactions(accessToken, cb) {
  var tx_url = config.fidor_api_url+"/transactions?access_token="+accessToken
  
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
module.exports.init                = init
module.exports.getOAuthRedirectUrl = getOAuthRedirectURL
module.exports.getOAuthToken       = getOAuthToken
module.exports.getConfig           = getConfig
module.exports.getTransactions     = getTransactions

