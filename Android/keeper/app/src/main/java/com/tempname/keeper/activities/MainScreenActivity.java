package com.tempname.keeper.activities;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.support.design.widget.NavigationView;
import android.support.v4.view.GravityCompat;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.ActionBarDrawerToggle;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.TextView;

import com.tempname.keeper.MainActivity;
import com.tempname.keeper.R;
import com.tempname.keeper.data.Data;
import com.tempname.keeper.data.WebErrorListener;
import com.tempname.keeper.data.WebResponseListener;
import com.tempname.keeper.util.Notification;
import com.tempname.keeper.wrappers.Account;

import org.json.JSONException;
import org.json.JSONObject;

public class MainScreenActivity extends AppCompatActivity implements NavigationView.OnNavigationItemSelectedListener {

    private Account person = null;

    private static final int REQUEST_UPDATE_PROFILE = 1;

    private Activity self = this;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        String info = this.getIntent().getStringExtra("AccountData");
        if (info != null) {
            try {
                JSONObject data = new JSONObject(info);
                person = new Account(data.getString("Person_ID"), data.getString("First_Name"), data.getString("Last_Name"), data.getString("Jersey_Number"), data.getString("Person_Avatar"), data.getString("Team_ID"), data.getString("Role_Name"));
            } catch (JSONException e) {
                loginError();
            }
        } else {
            loginError();
        }

        if (!this.isFinishing() && person != null) {
            Notification.displaySuccessMessage(this, getString(R.string.successfully_loggedin));
            setContentView(R.layout.activity_main_screen);
            Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
            setSupportActionBar(toolbar);

            DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
            ActionBarDrawerToggle toggle = new ActionBarDrawerToggle(
                    this, drawer, toolbar, R.string.navigation_drawer_open, R.string.navigation_drawer_close);
            drawer.setDrawerListener(toggle);
            toggle.syncState();

            NavigationView navigationView = (NavigationView) findViewById(R.id.nav_view);
            navigationView.setNavigationItemSelectedListener(this);

        } else {
            loginError();
        }

    }

    private void loginError() {
        Notification.displayError(this, "Error logging in, please try again!");
        Intent welcomeScreen = new Intent(this, MainActivity.class);
        welcomeScreen.putExtra("logout", true);
        startActivity(welcomeScreen);
        this.finish();
    }

    private void updateJerseyNumberHeader() {
        ((TextView) findViewById(R.id.jerseyNumberText)).setText(String.format("Number %s", person.getJerseyNumber()));
    }

    @Override
    public void onBackPressed() {
        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        if (drawer.isDrawerOpen(GravityCompat.START)) {
            drawer.closeDrawer(GravityCompat.START);
        } else {
            super.onBackPressed();
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        ((TextView) findViewById(R.id.personNameText)).setText(String.format("%s %s", person.getFirsName(), person.getLastName()));

        if (person.getRole().equalsIgnoreCase("Player")) {
            updateJerseyNumberHeader();
        }

        return true;
    }

    @Override
    public boolean onNavigationItemSelected(MenuItem item) {
        // Handle navigation view item clicks here.
        int id = item.getItemId();

        if (id == R.id.nav_logout) {
            Intent welcomeScreen = new Intent(this, MainActivity.class);
            welcomeScreen.putExtra("logout", true);
            startActivity(welcomeScreen);
            this.finish();
        }

        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        drawer.closeDrawer(GravityCompat.START);
        return true;
    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode == REQUEST_UPDATE_PROFILE) {
            if (resultCode == RESULT_OK) {
                String newNumber = data.getStringExtra("Jersey_Number");
                if (!person.getJerseyNumber().equals(newNumber) && person.getRole().equalsIgnoreCase("Player")) {
                    person.setJerseyNumber(newNumber);
                    updateJerseyNumberHeader();
                    JSONObject newNumberData = new JSONObject();
                    try {
                        newNumberData.put("Jersey_Number", newNumber);
                        Data.getInstance().update(newNumberData, "player-number", null,
                                new WebErrorListener() {
                                    @Override
                                    public void onError(JSONObject error) throws JSONException {
                                        Notification.displayError(self, error.getString("message"));
                                    }
                                });
                    } catch (JSONException e) {
                        Notification.displayError(this, "There was an error syncing your jersey number with the cloud!");
                    }

                }
            }
        }
    }

    public void modifyAccount(View view) {
        Intent settings = new Intent(this, ProfileSettingsActivity.class);
        settings.putExtra("Jersey_Number", person.getJerseyNumber());
        settings.putExtra("Person_Avatar", person.getAvatar());
        startActivityForResult(settings, REQUEST_UPDATE_PROFILE);
    }
}
