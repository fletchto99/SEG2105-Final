package com.tempname.keeper;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.view.View;

import com.github.franmontiel.PersistentCookieStore;
import com.tempname.keeper.activities.CreateAccountActivity;
import com.tempname.keeper.activities.LoginActivity;
import com.tempname.keeper.activities.MainScreenActivity;
import com.tempname.keeper.data.Data;
import com.tempname.keeper.data.WebErrorListener;
import com.tempname.keeper.data.WebResponseListener;
import com.tempname.keeper.util.Notification;

import org.json.JSONException;
import org.json.JSONObject;

import java.net.CookieHandler;
import java.net.CookieManager;
import java.net.CookiePolicy;

public class MainActivity extends AppCompatActivity {

    private final int FINISH_ACTIVITY = 1;

    private final Activity self = this;

    @Override
    protected void onCreate(Bundle savedInstanceState) {

        if (!Data.isReady()) {
            //Load any saved sessions
            final CookieManager manager = new CookieManager(new PersistentCookieStore(getApplicationContext()), CookiePolicy.ACCEPT_ALL);
            CookieHandler.setDefault(manager);

            //Init the data singleton
            Data.createInstance(this);
        }

        //Display the view
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        final CookieManager manager = (CookieManager)CookieHandler.getDefault();

        if (getIntent().getBooleanExtra("logout", false)) {
            manager.getCookieStore().removeAll();
        }

        if (manager.getCookieStore().getCookies().size() > 0) {
            Notification.displayNotification(this, "Attempting to log in...");
            Data.getInstance().checkForAuthentication(
                    new WebResponseListener() {
                        @Override
                        public void onResponse(JSONObject response) throws JSONException {
                            Intent intent = new Intent(self, MainScreenActivity.class);
                            intent.putExtra("AccountData", response.toString());
                            self.startActivity(intent);
                            self.finish();
                        }
                    }, new WebErrorListener() {
                        @Override
                        public void onError(JSONObject error) throws JSONException {
                            Notification.displayError(self, error.getString("message"));
                            Data.getInstance().logout();
                            manager.getCookieStore().removeAll();
                            findViewById(R.id.buttonLogin).setVisibility(View.VISIBLE);
                            findViewById(R.id.buttonCreateAccount).setVisibility(View.VISIBLE);
                        }
                    });
        } else {
            findViewById(R.id.buttonLogin).setVisibility(View.VISIBLE);
            findViewById(R.id.buttonCreateAccount).setVisibility(View.VISIBLE);
        }

    }

    public void login(View view) {
        startActivityForResult(new Intent(this, LoginActivity.class), FINISH_ACTIVITY);
    }


    public void createAccount(View view) {
        startActivityForResult(new Intent(this, CreateAccountActivity.class), FINISH_ACTIVITY);
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode == FINISH_ACTIVITY) {
            if (resultCode == RESULT_OK) {
                this.finish();
            }
        }
    }
}