require 'rubygems'
require 'sinatra'
require 'active_support/json'
require 'net/http'

get '/' do
  # settings
  @url = 'http://localhost:4567'
  @id = '<CLIENT-ID>'
  @secret = '<CLIENT-SECRET>'
  @fidor_url = '<FIDOR-URL>'

  # 1. redirect to authorize url
  unless code = params["code"]
    dialog_url = "#{@fidor_url}/oauth/authorize?client_id=#{@id}&redirect_uri=#{CGI::escape(@url)}"
    redirect dialog_url
  end

  # 2. get the access token, with code returned from auth dialog above
  token_url = URI("#{@fidor_url}/oauth/token")
  # GET and parse access_token response json
  res = Net::HTTP.post_form(token_url, 'client_id' => @id,
                                        'redirect_uri' => CGI::escape(@url),
                                        'code' =>code,
                                        'client_secret'=>@secret)
  resp = ActiveSupport::JSON.decode(res.body)

  "Now you have an access token: #{resp.inspect}"

  # GET info about current user
  # usr_url = "#{@fidor_url}/api/users/current?access_token=#{resp['access_token']}"
  # u =  Net::HTTP.get(usr_url)
  # usr = ActiveSupport::JSON.decode(u.body_str)
end
