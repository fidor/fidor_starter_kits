# Golang GET Transactions

This is a basic example project that demonstrates the Fidor API OAuth
flow, saving access-tokens to a session and grabbing transactions and account 
details.

## Usage

In order to run the example you'll need a Go environment installed (see
www.golang.org) and can start the sample with:

    go run example.go

The server will listen on port 8080, you can access the example in your
browser under the following URL:

  http://localhost:8080

## Configuration

In case you downloaded this project from the Fidor AppManager, all the
configuration should have already been set up for you. In case you
retrieved this example from another source, you'll need to open the
`example.go` file and fill in the configuration values at the top of the
file. You will be able to find out the values in the AppManager, create
a new App and use the configuration from the new App's detail page.

