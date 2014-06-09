require 'rubygems'
require 'sinatra'
require 'active_support/json'
require 'net/http'


get '/' do
  # settings
  @id = '4d25677ffd39b748'
  @secret = 'f263d0470c0e073b6894d3d6e794aaa2'
  @url = 'http://localhost:4567'
  @fidor_url = 'http://localhost:3000'


  unless code = params["code"] # redirect to authorize url
    dialog_url = "#{@fidor_url}/oauth/authorize?client_id=#{@id}&redirect_uri=#{CGI::escape(@url)}"
    redirect dialog_url
  end

  # get the access token
  token_url = URI("#{@fidor_url}/oauth/token")
  # token_url = "#{@fidor_url}/oauth/token?client_id=#{@id}&redirect_uri=#{CGI::escape(@url)}&client_secret=#{@secret}&code=#{code}"
  # GET and parse access_token response json
  res = Net::HTTP.post_form(token_url, 'client_id' => @id,
                                        'redirect_uri' => CGI::escape(@url),
                                        'code' =>code,
                                        'client_secret'=>@secret)
  resp = ActiveSupport::JSON.decode(res.body)

  # GET info about current user
  # usr_url = "#{@fidor_url}/api/users/current?access_token=#{resp['access_token']}"
  # u =  Net::HTTP.get(usr_url)
  # usr = ActiveSupport::JSON.decode(u.body_str)
  
  "Now you have an access token: #{resp.inspect}"
end
