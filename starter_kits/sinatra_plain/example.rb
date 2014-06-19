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

  # GET current user
  usr_url = "#{@fidor_url}/api/users/current?access_token=#{resp['access_token']}"
  user = ActiveSupport::JSON.decode( Net::HTTP.get URI(usr_url) )
  acnt_url = "#{@fidor_url}/api/accounts?access_token=#{resp['access_token']}"
  # account_resp =  Net::HTTP.get URI(acnt_url)
  # accounts = ActiveSupport::JSON.decode(account_resp)
  "<h2>Hello #{user['email']}</h2>
   <i>May i present the access token response:</i>
   <blockquote>#{resp.inspect}</blockquote>
   <p>Now use the access token like this <br>
      <a href='#{acnt_url}'>#{acnt_url}</a></p>
   "
end
