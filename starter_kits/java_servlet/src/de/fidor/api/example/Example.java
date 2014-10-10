package de.fidor.api.example;

import java.io.IOException;
import java.io.PrintWriter;
import java.net.URI;
import java.net.URISyntaxException;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.http.HttpResponse;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpUriRequest;
import org.apache.http.client.methods.RequestBuilder;
import org.apache.http.impl.client.HttpClients;
import org.apache.http.util.EntityUtils;
import org.json.simple.JSONObject;
import org.json.simple.parser.JSONParser;
import org.json.simple.parser.ParseException;

@WebServlet("/Example")
public class Example extends HttpServlet {
	private static final long serialVersionUID = 1L;
	private final String app_url = "<APP_URL>";
	private final String client_id = "<CLIENT_ID>";
	private final String client_secret = "<CLIENT_SECRET>";
	private final String fidor_oauth_url = "<FIDOR_API_URL>/oauth";
	private final String fidor_api_url = "<FIDOR_API_URL>";
	private HttpClient httpClient;
	private JSONParser parser;
    
	/**
     * @see HttpServlet#HttpServlet()
     */
    public Example() {
        super();
        httpClient = HttpClients.createDefault();
        parser = new JSONParser();
    }

	/**
	 * @see HttpServlet#doGet(HttpServletRequest request, HttpServletResponse response)
	 */
	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
		String code = request.getParameter("code");
		if(code == null) {
			response.sendRedirect(getCodeUrl());
		} else {
			try {
				String token = getToken(code);
				if(token != null) {
					HttpResponse userResponse = httpClient.execute(RequestBuilder.get().setUri(getUserUri(token)).build());
					String body = EntityUtils.toString(userResponse.getEntity());
					JSONObject user = (JSONObject)parser.parse(body);
					
					response.setContentType("text/html");
					PrintWriter writer = response.getWriter();
					writer.append(
							"<h2>Hello " + user.get("email") + "</h2>"
							+ "<i>May i present the access token response:</i>"
							+ "<blockquote>" + body + "</blockquote>"
							+ "<p>Now use the access token in <br> <a href='" + getAccountUrl(token) +"'>" + getAccountUrl(token) +"</a></p>");
				}
			} catch (URISyntaxException e) {
				e.printStackTrace();
			} catch (ParseException e) {
				e.printStackTrace();
			}
		
		}
	}

	private URI getUserUri(String token) throws URISyntaxException {
		return new URI(fidor_api_url + "/users/current?access_token=" + token);
	}

	private String getToken(String code) throws URISyntaxException, ClientProtocolException, IOException, ParseException {
		HttpUriRequest req = (HttpUriRequest) RequestBuilder.post()
				.setUri(getTokenUri())
				.addParameter("client_id", client_id)
				.addParameter("redirect_uri", app_url)
				.addParameter("code", code)
				.addParameter("client_secret", client_secret)
				.build();
		
		HttpResponse resp = httpClient.execute(req);
		String body = EntityUtils.toString(resp.getEntity());
		Object jsonResponse = parser.parse(body);
		if(jsonResponse instanceof JSONObject) {
			JSONObject responseObject = (JSONObject)jsonResponse;
			return responseObject.get("access_token").toString();
		} else {
			return null;
		}
	}

	private URI getTokenUri() throws URISyntaxException {
		return new URI(fidor_oauth_url + "/token"); 
	}

	private String getCodeUrl() {
		return fidor_oauth_url + "/authorize?client_id=" + client_id + "&redirect_uri=" + app_url;
	}
	
	private String getAccountUrl(String token) {
		return fidor_api_url + "/accounts?access_token=" + token;
	}
	
}
