# FidorStarterKits

This repo features fidor application examples. The apps use oAuth to 
authenticate with fidor, so you must register an app at fidor first to get your 
personal app credentials. 
Afterwards just copy on of the /starter_kits add your app credentials to the 
example source and start to play.

For Ruby Heros, this repo is also available as ruby gem and provides a tiny 
helper for app creation, see Install & Usage.


## Installation

Add this line to your application's Gemfile:

    gem 'fidor_starter_kits'

And then execute:

    $ bundle

Or install it yourself as:

    $ gem install fidor_starter_kits

## Usage

Create a zipped app with credentials and the fidor url:
```ruby

# FidorStarterKits.build(example_name, client_id, client_secret, fidor_url)
    
zip_file_path = FidorStarterKits.build('sinatra_plain',
                                       'your-client-id','your-client-secret', 'http://localhost:3002')
# => /tmp/sinatra_plain-xyz/sinatra_plain.zip
# => mv / cp / download is up to you babee
```

## Contributing

1. Fork it ( http://github.com/schorsch/fidor_starter_kits/fork )
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request
