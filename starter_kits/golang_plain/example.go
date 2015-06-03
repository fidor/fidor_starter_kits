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
	"crypto/rand"
	"encoding/hex"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"strings"
)

// The following sections define the settings you require for the
// app to be able to connect to and authorize itself against the api.

type Config struct {
	AppUrl        string // where to reach this application
	ClientId      string // OAuth Client_id parameter
	ClientSecret  string // OAuth Client_secret parameter
	FidorApiUrl   string // API endpoint (this changes between Sandbox and Production)
	FidorOauthUrl string // OAuth endpoint (this changes between Sandbox and Production)
}

const fidorConfig = Config{
	AppUrl:        "<APP_URL>",
	ClientId:      "<CLIENT_ID>",
	ClientSecret:  "<CLIENT_SECRET>",
	FidorApiUrl:   "<FIDOR_API_URL>",
	FidorOauthUrl: "<FIDOR_OAUTH_URL>",
}

func main() {
	// register a handler function (see next function) to service
	// requests ...
	http.HandleFunc("/", indexHandler)
	fmt.Printf("Now open %s\n", fidorConfig.AppUrl)
	// ... and start listening.
	if u, err := url.Parse(fidorConfig.AppUrl); err != nil {
		fmt.Printf("Can't make sense of configured url: %s\nBye.\n", fidorConfig.AppUrl)
	} else {
		var hostPort = strings.Split(u.Host, ":")
		var port = ":8080"
		if len(hostPort) == 2 {
			port = ":" + hostPort[1]
		}
		http.ListenAndServe(port, nil)
	}
}

func indexHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != "GET" {
		w.WriteHeader(403)
		return
	}

	switch r.URL.Path {
	case "/":
		renderWelcome(w)
	case "/transactions":
		render("/transactions", w, r)
	case "/accounts":
		render("/accounts", w, r)
	case "/oauth":
		handleOAuthCallback(w, r)
	default:
		w.WriteHeader(404)
	}
}

const COOKIE_NAME = "GO_SESSION"

var sessions = make(map[string]string)

func createSession(w http.ResponseWriter, accessToken string) {
	println(accessToken)
	rnd := make([]byte, 20, 20)
	rand.Read(rnd)
	session := hex.EncodeToString(rnd)
	sessions[session] = accessToken
	cookie := http.Cookie{
		Name:  COOKIE_NAME,
		Value: session,
	}
	http.SetCookie(w, &cookie)
}

func render(endpoint string, w http.ResponseWriter, r *http.Request) {
	// check if request has cookie set
	if cookie, err := r.Cookie(COOKIE_NAME); err != nil {
		// else redirect to OAuth Authorization EP
		redirectToOAuth(w, r, endpoint)
		return
	} else {
		session := cookie.Value
		accessToken := sessions[session]

		// pipe api endpoint
		ep := fmt.Sprintf("%s/%s", fidorConfig.FidorApiUrl, endpoint)
		if api_req, err := http.NewRequest("GET", ep, nil); err != nil {
			w.WriteHeader(500)
			w.Write([]byte(err.Error()))
			return
		} else {
			api_req.Header.Set("Authorization", fmt.Sprintf("Bearer %s", accessToken))

			client := &http.Client{}
			if api_resp, err := client.Do(api_req); err != nil {
				w.WriteHeader(500)
				w.Write([]byte(err.Error()))
				return
			} else {

				contentType := http.CanonicalHeaderKey("content-type")
				w.Header().Set(contentType, api_resp.Header.Get(contentType))
				w.WriteHeader(api_resp.StatusCode)
				io.Copy(w, api_resp.Body)
			}
		}
	}
}

func redirectToOAuth(w http.ResponseWriter, r *http.Request, target_endpoint string) {
	_redirectURI := fmt.Sprintf("%s/oauth?ep=%s", fidorConfig.AppUrl, target_endpoint)
	redirectURI := url.QueryEscape(_redirectURI)

	oauthRedirectURL := fmt.Sprintf("%s/authorize?client_id=%s&state=321&response_type=code&redirect_uri=%s", fidorConfig.FidorOauthUrl, fidorConfig.ClientId, redirectURI)
	http.Redirect(w, r, oauthRedirectURL, 307)
}

func handleOAuthCallback(w http.ResponseWriter, r *http.Request) {
	code := r.FormValue("code")
	target := r.FormValue("ep")

	if code == "" || target == "" {
		w.WriteHeader(500)
		w.Write([]byte("missing code or target ep"))
		return
	}

	if token, err := retrieveTokenFromCode(code, target); err != nil {
		w.WriteHeader(500)
		w.Write([]byte(err.Error()))
		return
	} else {
		createSession(w, token)
		http.Redirect(w, r, target, 307)

	}

}

//	// check whether we have a GET parameter named `code`, if so,
//	// this is a redirect back from the Fidor OAuth server.
//	values := r.URL.Query()
//	if values["code"] != nil {
//		// retrieve the actual OAuth access token ....
//		code := values.Get("code")
//		if token, err := retrieveTokenFromCode(code); err != nil {
//			fmt.Printf("err: %v\n", err)
//			w.WriteHeader(500)
//			fmt.Fprintf(w, "Unfortunately, an error occurred retrieving oauth token")
//		} else {
//			// ... and finally, greet the user and assemble links
//			renderWelcome(w, token)
//		}
//	} else {
//		// we don't have an oauth `code` yet, so we need to
//		// redirect the user to the OAuth provider to get one ...
//		oauth_url := fmt.Sprintf("%s/authorize?client_id=%s&state=123&response_type=code&redirect_uri=%s",
//			fidor_oauth_url,
//			client_id,
//			url.QueryEscape(oauth_cb_url))
//
//		header := w.Header()
//		header.Add("location", oauth_url)
//		w.WriteHeader(307)
//	}
//}

// Our TokenResponse representation used to pick it out from the JSON
// returned by the OAuth server.
type TokenResponse struct {
	Token string `json:"access_token"`
}

// Use the OAuth code that the user's browser picked up from the OAuth
// server to request an OAuth access_token to use in API requests.
func retrieveTokenFromCode(code string, target_endpoint string) (token string, err error) {
	// assemble the API endpoint URL and request payload
	redirect_uri := fmt.Sprintf("%s/oauth?ep=%s", fidorConfig.AppUrl, target_endpoint)
	tokenPayload := url.Values{
		"client_id":     {fidorConfig.ClientId},
		"client_secret": {fidorConfig.ClientSecret},
		"code":          {code},
		"redirect_uri":  {url.QueryEscape(redirect_uri)},
		"grant_type":    {"authorization_code"},
	}
	// Call API
	tokenUrl := fmt.Sprintf("%s/token", fidorConfig.FidorOauthUrl)
	if resp, err := http.PostForm(tokenUrl, tokenPayload); err != nil {
		println(err)
		return "", err
	} else {
		if resp.StatusCode != 200 {
			return "", errors.New(resp.Status)
		}
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

func renderWelcome(w http.ResponseWriter) {
	w.Write([]byte(indexTemplate))
}

var indexTemplate = `
<html>
<head>
</head>
<body>
	<h1>Welcome!</h1>
	<p><a href="/transactions">Transactions</a></p>
	<p><a href="/accounts">Accounts</a></p>
</body>
</html>
`
