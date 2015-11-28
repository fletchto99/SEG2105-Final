package com.tempname.keeper.activities;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;

import com.tempname.keeper.R;
import com.tempname.keeper.data.Data;
import com.tempname.keeper.data.WebErrorListener;
import com.tempname.keeper.data.WebResponseListener;
import com.tempname.keeper.util.Notification;

import org.json.JSONException;
import org.json.JSONObject;

public class LoginActivity extends Activity {

    private final Activity self = this;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);
    }

    public void login(View view) {
        String username = ((EditText) findViewById(R.id.usernameInput)).getText().toString();
        String password = ((EditText) findViewById(R.id.passwordInput)).getText().toString();

        Data.getInstance().authenticate(username, password,
                new WebResponseListener() {
                    @Override
                    public void onResponse(JSONObject response) throws JSONException {
                        Notification.displaySuccessMessage(self, response.getString("success"));
                        self.startActivity(new Intent(self, MainScreenActivity.class));
                        self.setResult(RESULT_OK);
                        self.finish();
                    }
                }, new WebErrorListener() {
                    @Override
                    public void onError(JSONObject error) throws JSONException {
                        Notification.displayError(self, error.getString("message"));
                    }
                });
    }
}
