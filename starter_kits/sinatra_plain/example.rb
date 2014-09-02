require 'rubygems'
require 'sinatra'
require 'active_support/json'
require 'net/http'

get '/' do
  # settings
  @app_url         = 'http://localhost:4567'          # default for local installs: http://localhost:4567
  @client_id       = '96a1cb8cd65b7717'
  @client_secret   = '484dab6add45dd0c2e494c74433e616e'
  @fidor_oauth_url = 'http://localhost:3000/api_sandbox/oauth'  # e.g https://fidor.com/oauth
  @fidor_api_url   = 'http://localhost:3000/api_sandbox'    # e.g https://fidor.com/api_sandbox, https://fidor.com/api

  # 1. redirect to authorize url
  unless code = params["code"]
    dialog_url = "#{@fidor_oauth_url}/authorize?client_id=#{@client_id}&redirect_uri=#{CGI::escape(@app_url)}"
    redirect dialog_url
  end

  # 2. get the access token, with code returned from auth dialog above
  token_url = URI("#{@fidor_oauth_url}/token")
  # GET and parse access_token response json
  res = Net::HTTP.post_form(token_url, 'client_id' => @client_id,
                                        'redirect_uri' => CGI::escape(@app_url),
                                        'code' =>code,
                                        'client_secret'=>@client_secret)
  resp = ActiveSupport::JSON.decode(res.body)

  # GET current user
  usr_url = "#{@fidor_api_url}/users/current?access_token=#{resp['access_token']}"
  user = ActiveSupport::JSON.decode( Net::HTTP.get URI(usr_url) )
  acnt_url = "#{@fidor_api_url}accounts?access_token=#{resp['access_token']}"
  "<h2>Hello #{user['email']}</h2> <i>May i present the access token response:</i>
   <blockquote>#{resp.inspect}</blockquote>
   <p>Now use the access token in <br> <a href='#{acnt_url}'>#{acnt_url}</a></p>
   "
end
