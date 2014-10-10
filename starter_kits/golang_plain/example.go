import (
	"fmt"
	"io/ioutil"
	"net/http"
)


func main() {
    http.HandleFunc("/", viewHandler)
    http.ListenAndServe(":8080", nil)
}

func viewHandler(w http.ResponseWriter, r *http.Request) {

	app_url := "<APP-URL>"  			//default: http://localhost:8080
 	client_id := "<CLIENT-ID>"
 	client_secret := "<CLIENT-SECRET>"
	fidor_url := "<FIDOR-URL>"

    fmt.Fprintf(w, "<h1>You made it!</h1><div>%s</div>", p)
}
