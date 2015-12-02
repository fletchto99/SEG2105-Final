package com.tempname.keeper.activities;

import android.app.Activity;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.View;
import android.content.Intent;
import android.widget.EditText;
import android.widget.ImageView;


import com.tempname.keeper.R;
import com.tempname.keeper.data.Data;
import com.tempname.keeper.data.WebErrorListener;
import com.tempname.keeper.data.WebResponseListener;
import com.tempname.keeper.util.Notification;

import org.json.JSONException;
import org.json.JSONObject;

public class NewTeamActivity extends AppCompatActivity {

    private final Activity self = this;
    private String imageName;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_new_team);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        FloatingActionButton fab = (FloatingActionButton) findViewById(R.id.fab);
        fab.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Snackbar.make(view, "Replace with your own action", Snackbar.LENGTH_LONG)
                        .setAction("Action", null).show();
            }
        });
    }

    public void OnSetAvatarButton(View view) {
        Intent intent = new Intent(getApplicationContext(), EditDisplayPictureActivity.class); //Application Context and Activity
        startActivityForResult(intent, 0);
    }

    public void OnCancel(View view) {
        Intent intent = new Intent(getApplicationContext(), MainScreenActivity.class); //Application Context and Activity
        startActivityForResult(intent, 0);
    }

    public void createTeam(View view) {

        //call the create-team controller
        JSONObject data = new JSONObject();
        try {
            data.put("Team_Name", ((EditText) findViewById(R.id.teamName)).getText().toString());

            Data.getInstance().update(data, "create-team",
                    new WebResponseListener() {
                        @Override
                        public void onResponse(JSONObject response) {
                            Notification.displaySuccessMessage(self, "Team created successfully!");
                        }
                    }, new WebErrorListener() {
                        @Override
                        public void onError(JSONObject error) throws JSONException {
                            Notification.displayError(self, error.getString("message"));
                        }
                    });
        } catch (JSONException e) {
            Notification.displayError(self, "There was an error creating your team. Please try again, if the issue persists please contact the developer.");
        }

        //call the team-avatar controller
        //Q: TODO: check if this is right?
        JSONObject data2 = new JSONObject();
        try {
            data2.put("Team_ID", ((EditText) findViewById(R.id.teamName)).getText().toString());
            data2.put("Team_Avatar", imageName);

            Data.getInstance().update(data2, "team-avatar",
                    new WebResponseListener() {
                        @Override
                        public void onResponse(JSONObject response) {
                            Notification.displaySuccessMessage(self, "Avatar added successfully!");
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
            Notification.displayError(self, "There was an error in added your avatar. Please try again, if the issue persists please contact the developer.");
        }
    }


    //Q: Assuming that this method is called right after the display picture is chosen from the "EditDisplayPictureActivity"?
    //(code from lab)
    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (resultCode == RESULT_CANCELED) return;
        //Getting the Avatar Image we show to our users
        ImageView avatarImage = (ImageView) findViewById(R.id.displayPicture);
        //Figuring out the correct image
        String drawableName = "ic_menu_gallery";
        switch (data.getIntExtra("imgID", R.id.img1)) {
            case R.id.img1:
                drawableName = "ic_logo_01";
                break;
            case R.id.img2:
                drawableName = "ic_logo_02";
                break;
            case R.id.img3:
                drawableName = "ic_logo_03";
                break;
            case R.id.img4:
                drawableName = "ic_logo_04";
                break;
            case R.id.img5:
                drawableName = "ic_logo_05";
                break;
            case R.id.img6:
                drawableName = "ic_logo_00";
                break;
            default:
                drawableName = "ic_menu_gallery";
                break;
        }
        int resID = getResources().getIdentifier(drawableName, "drawable", getPackageName());
        avatarImage.setImageResource(resID);

        imageName = drawableName;
    }
}
