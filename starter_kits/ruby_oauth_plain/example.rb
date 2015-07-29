require 'rubygems'
require 'sinatra'
require 'httparty'

get '/' do
  # settings
  @app_url         = '<APP_URL>'          # default for local installs: http://localhost:4567
  @client_id       = '<CLIENT_ID>'
  @client_secret   = '<CLIENT_SECRET>'
  @fidor_oauth_url = '<FIDOR_OAUTH_URL>'  # e.g Sandbox: https://aps.fidor.de/oauth / Live: https://apm.fidor.de/oauth
  @fidor_api_url   = '<FIDOR_API_URL>'    # e.g Sandbox: https://aps.fidor.de / Live: https://api.fidor.de

  # 1. redirect to authorize url
  unless code = params["code"]
    dialog_url = "#{@fidor_oauth_url}/authorize?client_id=#{@client_id}&redirect_uri=#{CGI::escape(@app_url)}&state=1234&response_type=code"
    redirect dialog_url
  end

  # 2. get the access token, with code returned from auth dialog above
  token_url = URI("#{@fidor_oauth_url}/token")
  post_params = { client_id: @client_id,
                  redirect_uri: CGI::escape(@app_url),
                  code: code,
                  #client_secret: @client_secret,
                  grant_type: 'authorization_code' }
  auth = {:username => @client_id, :password => @client_secret}
  resp = HTTParty.post(token_url, body: post_params, basic_auth: {} )

  # GET current user setting the access-token in the request header
  user = HTTParty.get( "#{@fidor_api_url}/users/current",
                       headers: { 'Authorization' => "Bearer #{resp['access_token']}",
                                  'Accept'        => "application/vnd.fidor.de; version=1,text/json"} )

  "<h2>Hello #{user['email']}</h2>
   <i>May i present the access token response:</i>
   <blockquote>#{resp.body}</blockquote>
   <p>Now use the access token in the Header of your Requests, e.g. using CURL</p>
   <blockquote>
   curl -v -H \"Authorization: Bearer #{resp['access_token']}\" #{@fidor_api_url}/accounts
   </blockquote>"
end
