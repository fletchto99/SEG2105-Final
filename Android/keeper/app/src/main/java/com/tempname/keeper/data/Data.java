package com.tempname.keeper.data;

import android.content.Context;
import android.util.Base64;

import com.android.volley.AuthFailureError;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;


public class Data {

    private static Data instance = null;
    private RequestQueue requestQueue;

    private static final String CONTROLLERS_ROOT = "https://fletchto99.com/other/sites/school/seg2105/controllers/";
    private static final String CONTROLLER_GET = "get/";
    private static final String CONTROLLER_UPDATE = "update/";
    private static final String CONTROLLER_REMOVE = "remove/";
    private static final String CONTROLLER_TYPE = ".php";
    private static final String LOGIN_CONTROLLER = "login";
    private static final String LOGOUT_CONTROLLER = "logout";
    private static final String VALIDATE_CONTROLLER = "validate-session";

    private Data(Context context) {
        this.requestQueue = Volley.newRequestQueue(context.getApplicationContext());
    }

    public static synchronized Data createInstance(final Context context) {
        if (Data.instance == null) {
            Data.instance = new Data(context);
        }
        return Data.getInstance();
    }

    public static synchronized Data getInstance() {
        if (Data.instance == null) {
            throw new NullPointerException("Instance has not yet been initialized!");
        }
        return Data.instance;
    }

    public void checkForAuthentication(final WebResponseListener listener, final WebErrorListener errorListener) {
        String url = CONTROLLERS_ROOT + VALIDATE_CONTROLLER + CONTROLLER_TYPE;
        StringRequest request = new StringRequest(
                Request.Method.GET,
                url,
                new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        try {
                            listener.onResponse(new JSONObject(response));
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                    }
                },
                generateVolleyErrorListener(errorListener));

        requestQueue.add(request);
    }

    public void authenticate(final String username, final String password, final WebResponseListener listener, final WebErrorListener errorListener) {
        String url = CONTROLLERS_ROOT + LOGIN_CONTROLLER + CONTROLLER_TYPE;
        StringRequest request = new StringRequest(
                Request.Method.GET,
                url,
                new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        try {
                            listener.onResponse(new JSONObject(response));
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                    }
                },
                generateVolleyErrorListener(errorListener)) {

            @Override
            public Map<String, String> getHeaders() throws AuthFailureError {
                return createBasicAuthHeader(username, password);
            }
        };

        requestQueue.add(request);
    }

    public void logout() {
        String url = CONTROLLERS_ROOT + CONTROLLER_UPDATE + LOGOUT_CONTROLLER + CONTROLLER_TYPE;
        requestQueue.add(new JsonObjectRequest(Request.Method.POST, url, null, null, null));

    }

    public void get(final JSONObject data, final String controller, final WebResponseListener listener, final WebErrorListener errorListener) {
        String url = CONTROLLERS_ROOT + CONTROLLER_GET + controller + CONTROLLER_TYPE;
        requestQueue.add(new JsonObjectRequest(Request.Method.POST, url, data, generateVolleyResponseListener(listener), generateVolleyErrorListener(errorListener)));
    }

    public void update(final JSONObject data, final String controller, final WebResponseListener listener, final WebErrorListener errorListener) {
        String url = CONTROLLERS_ROOT + CONTROLLER_UPDATE + controller + CONTROLLER_TYPE;
        requestQueue.add(new JsonObjectRequest(Request.Method.POST, url, data, generateVolleyResponseListener(listener), generateVolleyErrorListener(errorListener)));
    }

    public void remove(final JSONObject data, final String controller, final WebResponseListener listener, final WebErrorListener errorListener) {
        String url = CONTROLLERS_ROOT + CONTROLLER_REMOVE + controller + CONTROLLER_TYPE;
        requestQueue.add(new JsonObjectRequest(Request.Method.POST, url, data, generateVolleyResponseListener(listener), generateVolleyErrorListener(errorListener)));
    }

    private Response.ErrorListener generateVolleyErrorListener(final WebErrorListener wel) {
        return new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {
                try {
                    wel.onError(new JSONObject(new String(error.networkResponse.data, "utf-8")).getJSONObject("error"));
                } catch (Exception e) {
                    e.printStackTrace();
                    //TODO: Handle errors not generated by the conroller?
                }
            }
        };
    }

    private Response.Listener<JSONObject> generateVolleyResponseListener(final WebResponseListener rel) {
        return new Response.Listener<JSONObject>() {
            @Override
            public void onResponse(JSONObject response) {
                try {
                    rel.onResponse(response);
                } catch (Exception e) {
                    e.printStackTrace();
                    //TODO: Handle errors not generated by the conroller?
                }
            }
        };
    }

    private Map<String, String> createBasicAuthHeader(String username, String password) {
        Map<String, String> headerMap = new HashMap<>();
        String credentials = username + ":" + password;
        String base64EncodedCredentials = Base64.encodeToString(credentials.getBytes(), Base64.NO_WRAP);
        headerMap.put("Authorization", "Basic " + base64EncodedCredentials);
        return headerMap;
    }

}