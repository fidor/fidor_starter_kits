require 'rubygems'
require 'sinatra'
require 'active_support/json'
require 'net/http'

get '/' do
  # settings
  @app_url = '<APP-URL>'  #default: http://localhost:4567
  @client_id = '<CLIENT-ID>'
  @client_secret = '<CLIENT-SECRET>'
  @fidor_url = '<FIDOR-URL>'

  # 1. redirect to authorize url
  unless code = params["code"]
    dialog_url = "#{@fidor_url}/oauth/authorize?client_id=#{@client_id}&redirect_uri=#{CGI::escape(@app_url)}"
    redirect dialog_url
  end

  # 2. get the access token, with code returned from auth dialog above
  token_url = URI("#{@fidor_url}/oauth/token")
  # GET and parse access_token response json
  res = Net::HTTP.post_form(token_url, 'client_id' => @client_id,
                                        'redirect_uri' => CGI::escape(@app_url),
                                        'code' =>code,
                                        'client_secret'=>@client_secret)
  resp = ActiveSupport::JSON.decode(res.body)

  "You now have an access token: #{resp.inspect}"

  # GET info about current user
  # usr_url = "#{@fidor_url}/api/users/current?access_token=#{resp['access_token']}"
  # u =  Net::HTTP.get(usr_url)
  # usr = ActiveSupport::JSON.decode(u.body_str)
end
