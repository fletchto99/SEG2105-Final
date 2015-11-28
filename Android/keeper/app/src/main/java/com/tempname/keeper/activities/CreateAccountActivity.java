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

public class CreateAccountActivity extends Activity {

    private final Activity self = this;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_create_account);
    }

    public void createAccount(View view) {

        JSONObject data = new JSONObject();
        try {
            data.put("Username", ((EditText) findViewById(R.id.usernameInput)).getText().toString());
            data.put("Password", ((EditText) findViewById(R.id.passwordInput)).getText().toString());
            data.put("First_Name", ((EditText) findViewById(R.id.firstNameInput)).getText().toString());
            data.put("Last_Name", ((EditText) findViewById(R.id.lastNameInput)).getText().toString());

            Data.getInstance().update(data, "create-user",
                    new WebResponseListener() {
                        @Override
                        public void onResponse(JSONObject response) {
                            Notification.displaySuccessMessage(self, "Account created successfully!");
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
        } catch (JSONException e) {
            Notification.displayError(self, "There was an error creating your account. Please try again, if the issue persists please contact the developer.");
        }
    }
}


