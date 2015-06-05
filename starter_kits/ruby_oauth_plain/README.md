# Ruby Plain oAuth Login - Example

A single view that demonstrates the Fidor API OAuth login flow and how to get 
an access token. Uses Sinatra on its server side.

## Usage

This uses bundler to install the required gems:

  cd ruby_oauth_plain
  bundle install
  ruby example.rb
  
  # or run on different port than :4567 provided by WEBrick
  ruby example.rb -p 3004

## Configuration

In case you downloaded this project from the Fidor AppManager, all the
configuration should have already been set up for you. In case you
retrieved this example from another source, you'll need to open the
`example.rb` file and fill in the configuration values at the top of the
file. You will be able to find out the values in the AppManager, create
a new App and use the configuration from the new App's detail page.
