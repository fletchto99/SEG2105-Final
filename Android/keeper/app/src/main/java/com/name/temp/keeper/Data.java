package com.name.temp.keeper;

import android.content.Context;
import android.widget.TextView;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONObject;

/**
 * Created by Matt on 2015-11-05.
 */
public class Data{
    private static Data mInstance;
    private RequestQueue mRequestQueue;
    private static Context mCtx;
    private Response.Listener<JSONObject> mListener;
    private String mResponse;

    private Data(Context context){
        mCtx = context.getApplicationContext();
        mRequestQueue = getRequestQueue();
    }

    public static synchronized Data getInstance(Context context) {
        if (mInstance == null) {
            mInstance = new Data(context);
        }
        return mInstance;
    }

    public RequestQueue getRequestQueue() {
        if (mRequestQueue == null) {
            // getApplicationContext() is key, it keeps you from leaking the
            // Activity or BroadcastReceiver if someone passes one in.
            mRequestQueue = Volley.newRequestQueue(mCtx.getApplicationContext());
        }
        return mRequestQueue;
    }

    public void get(JSONObject data, String controller, Response.Listener listener, Response.ErrorListener errorListener){
        String url = "https://fletchto99.com/other/sites/school/seg2105/controllers/get/"+controller+".php";
        JsonObjectRequest request = new JsonObjectRequest(Request.Method.POST, url, data, listener,errorListener);
        mRequestQueue.add(request);
    }

    public void remove(JSONObject data, String controller, Response.Listener listener, Response.ErrorListener errorListener){
        String url = "https://fletchto99.com/other/sites/school/seg2105/controllers/remove/"+controller+".php";
        JsonObjectRequest request = new JsonObjectRequest(Request.Method.POST, url, data, listener,errorListener);
        mRequestQueue.add(request);
    }

    public void update(JSONObject data, String controller, Response.Listener listener, Response.ErrorListener errorListener){
        String url = "https://fletchto99.com/other/sites/school/seg2105/controllers/update/"+controller+".php";
        JsonObjectRequest request = new JsonObjectRequest(Request.Method.POST, url, data, listener,errorListener);
        mRequestQueue.add(request);
    }

}