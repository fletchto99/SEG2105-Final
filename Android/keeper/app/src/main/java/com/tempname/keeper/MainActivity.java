package com.tempname.keeper;

import android.content.Context;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.view.View;
import android.widget.TextView;

import com.tempname.keeper.data.Data;
import com.tempname.keeper.data.WebErrorListener;
import com.tempname.keeper.util.Notification;

import org.json.JSONException;
import org.json.JSONObject;

import static com.android.volley.Response.Listener;

public class MainActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        Data.createInstance(this);
    }

    public void getText(View view) {

        final TextView text = (TextView) findViewById(R.id.txtDisplay);
        final Context self = this;
        Data.getInstance().authenticate("matt", "tsest", null, new WebErrorListener() {
            @Override
            public void onError(JSONObject error) {
                Notification.displayError(self, "Aww she don't work");
            }
        });

        JSONObject data = new JSONObject();
        try {
            data.put("Jersey_Number", "12");

            //The endpoint which we are targeting
            String controller = "player-number";

            //How to handle an error when the endpoint generates on
            WebErrorListener errorListener2 = new WebErrorListener() {
                @Override
                public void onError(JSONObject error) {
                    text.setText(error.toString());
                }
            };

            Listener<JSONObject> listener2= new Listener<JSONObject>() {

                public void onResponse(JSONObject response) {
                    text.setText(response.toString());
                }
            };

            Data.getInstance().update(data, controller, listener2, errorListener2);
        } catch (JSONException e) {
            e.printStackTrace();
        }


//        JSONObject data = new JSONObject();
//        try {
//
//            //TODO: Replace with form data instead of hardcoded values
//            data.put("Username", "Example3");
//            data.put("Password", "Example3");
//            data.put("First_Name", "Matt");
//            data.put("Last_Name", "Langlois");
//
//            //The endpoint which we are targeting
//            String controller = "create-user";
//
//            //How to handle an error when the endpoint generates on
//            WebErrorListener errorListener = new WebErrorListener() {
//                @Override
//                public void onError(JSONObject error) {
//                    text.setText(error.toString());
//                }
//            };
//
//            Listener<JSONObject> listener= new Listener<JSONObject>() {
//
//                public void onResponse(JSONObject response) {
//                    text.setText(response.toString());
//                }
//            };
//
//            Data.getInstance(this).update(data, controller, listener, errorListener);
//        } catch (JSONException e) {
//            e.printStackTrace();
//        }


    }


}