# FidorStarterKits - BETA

This repo features Fidor application examples. The apps use oAuth to
authenticate with fidor, so you must register an app at fidor first to get your
personal app credentials.

For a quick start the Fidor App Manager(where you register your app) features a
'create&download app' method. This creates a new app, writes the required oauth
information into the source files and delivers the example as zipped download.

If you choose the manual way to explore our example apps, simply checkout this
repo, register and app and add the required credentials, URL's to the sources.

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

opts = {
  app_name: 'sinatra_plain',
  client_id: 'my-client-id',
  client_secret: 'my-client-secret',
  app_url: 'http://my-app-url:3002',
  fidor_oauth_url: 'https://fidor-oauth-url.de/oauth',
  fidor_api_url: 'https://fidor-api-url.de/api_sandbox'
}

zip_file_path = FidorStarterKits.build(opts)
# => /tmp/sinatra_plain-xyz/sinatra_plain.zip
# => mv / cp / download is up to you babee
```

## Build your own starter kit

As a quickstart for new developers we zip and download the examples in our
application manager. Before the following placeholders inside in your main
example.xy file are substituted with the according values from the app
(client_id/secret) and the values in .fidor_meta.json:

    <APP_URL>          # default http://localhost:8000/example.php
    <CLIENT_ID>
    <CLIENT_SECRET>
    <FIDOR_OAUTH_URL>  # e.g Sandbox: https://apm.sandbox.fidor.com/oauth / Live: https://apm.fidor.de/oauth
    <FIDOR_API_URL>    # e.g Sandbox: https://api.sandbox.fidor.com / Live: https://api.fidor.de

So just add those to example.[rb, php, ..] and see existing examples and specs
for a reference.

## Contributing

1. Fork it (https://github.com/fidor/fidor_starter_kits/fork )
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request
