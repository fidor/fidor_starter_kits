package main

// This is a basic example to illustrate how to access the Fidor API
// using Go.
//
// To run this example type:
//
//     $ go run example.go
//
// And point your browser to:
//
//     $ http://localhost:8080

import (
	"encoding/json"
	"fmt"
	"net/http"
	"net/url"
)

// The following sections defines the settings you require for the
// app to be able to connect to and authorize itself against the api.

// app ID and secret, can be found in this apps "Details" page in the
// AppManager.
var client_id = "<CLIENT_ID>"
var client_secret = "<CLIENT_SECRET>"

// Fidor's OAuth Endpoint (this changes between Sandbox and Production)
var fidor_oauth_url = "<FIDOR_OAUTH_URL>" // e.g https://fidor.com/api_sandbox/oauth
// The OAuth Endpoint this App provides
var oauth_cb_url = "<APP_URL>"

// The URL of the Fidor API (this changes between Sandbox and
// Production)
var fidor_api_url = "<FIDOR_API_URL>" // e.g https://fidor.com/api_sandbox vs /api




func main() {
	// register a handler function (see next function) to service
	// requests ...
	http.HandleFunc("/", indexHandler)
	// ... and start listening.
	http.ListenAndServe(":8080", nil)
}

func indexHandler(w http.ResponseWriter, r *http.Request) {

	// ignore any favicon requests, etc.
	if r.URL.Path != "/" {
		w.WriteHeader(404)
		return
	}

	// check whether we have a GET parameter named `code`, if so,
	// this is a redirect back from the Fidor OAuth server.
	values := r.URL.Query()
	if values["code"] != nil {
		// retrieve the actual OAuth access token ....
		code := values.Get("code")
		if token, err := retrieveTokenFromCode(code); err != nil {
			fmt.Printf("err: %v\n", err)
			w.WriteHeader(500)
			fmt.Fprintf(w, "Unfortunately, an error occured retrieving oauth token")
		} else {
			// ... and finally, greet the user and assemble links
			renderWelcome(w, token)
		}
	} else {
		// we don't have an oauth `code` yet, so we need to
		// redirect the user to the OAuth provider to get one ...
		oauth_url := fmt.Sprintf("%s/authorize?client_id=%s&redirect_uri=%s",
			fidor_oauth_url,
			client_id,
			url.QueryEscape(oauth_cb_url))

		header := w.Header()
		header.Add("location", oauth_url)
		w.WriteHeader(307)
	}
}


// Our TokenResponse representation used to pick it out from the JSON
// returned by the OAuth server.
type TokenResponse struct {
	Token string `json:"access_token"`
}

// Use the OAuth code that the user's browser picked up from the OAuth
// server to request an OAuth access_token to use in API requests.
func retrieveTokenFromCode(code string) (token string, err error) {
	// assemble the API endpoint URL and request payload
	tokenUrl := fmt.Sprintf("%s/token", fidor_oauth_url)
	tokenPayload := url.Values{
		"client_id":     {client_id},
		"client_secret": {client_secret},
		"code":          {code},
	}
	// Call API
	if resp, err := http.PostForm(tokenUrl, tokenPayload); err != nil {
		return "", err
	} else {
		// if successful, pick the access_token out of the reply.
		var tokenResponse TokenResponse
		decoder := json.NewDecoder(resp.Body)
		if err = decoder.Decode(&tokenResponse); err != nil {
			return "", err
		} else {
			return tokenResponse.Token, nil
		}
	}
}


// Our server code only makes a single call to the API to retrieve user
// information. This is our internal representation of the returned JSON
// used to pick out the user's email.
type UserResponse struct {
	Email string `json:"email"`
}

// function to retreive user information from the API
func getUser(token string) (u UserResponse, err error) {
	// Assemble endpoint URL...
	url := fmt.Sprintf("%s/users/current?access_token=%s", fidor_api_url, token)
	if resp, err := http.Get(url); err != nil {
		return u, err
	} else {
		decoder := json.NewDecoder(resp.Body)
		if err = decoder.Decode(&u); err != nil {
			return u, err
		} else {
			return u, nil
		}
	}
}


// once all the OAuth calls have been taken care of, this function is
// called from the http handler. It retrieves the user's email address
// and inserts links to `transaction` and `accounts` endpoints.

func renderWelcome(w http.ResponseWriter, token string) {
	if user, err := getUser(token); err != nil {
		fmt.Printf("err: %v\n", err)
		w.WriteHeader(500)
	} else {
		txLink := fmt.Sprintf("%s/transactions?access_token=%s", fidor_api_url, token)
		acctsLink := fmt.Sprintf("%s/accounts?access_token=%s", fidor_api_url, token)
		fmt.Fprintf(w, indexTemplate, user.Email, token, txLink, acctsLink)
	}
}

var indexTemplate = `
<html>
<head>
</head>
<body>
	<h1>Welcome %s!</h1>
	<i>retrieved <tt>access_token</tt>: %s</i>
	<p><a href="%s">Transactions</a></p>
	<p><a href="%s">Accounts</a></p>
</body>
</html>
`
