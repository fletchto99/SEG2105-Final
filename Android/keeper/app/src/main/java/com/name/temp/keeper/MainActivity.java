package com.name.temp.keeper;

import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.TextView;
import com.name.temp.keeper.Data;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONObject;

public class MainActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
    }

    public void getText(View view) {
        final TextView text = (TextView) findViewById(R.id.txtDisplay);
        JSONObject tmp = new JSONObject();
        String controller = "Hi";
        Response.Listener listener = new Response.Listener() {
            @Override
            public void onResponse(Object response) {
                text.setText(response.toString());
            }
        };
        Response.ErrorListener errorListener = new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {
                text.setText("Its Dead Jim");
            }
        };

        Data.getInstance(this).get(tmp, controller, listener, errorListener);
        Data.getInstance(this).remove(tmp, controller, listener, errorListener);
        Data.getInstance(this).update(tmp, controller, listener, errorListener);
    }


}