package com.tempname.keeper.activities;

import android.os.Bundle;
import android.app.Activity;
import android.view.View;
import android.content.Intent;
import android.widget.EditText;
import android.widget.RadioButton;

import com.tempname.keeper.R;
import com.tempname.keeper.data.Data;
import com.tempname.keeper.data.WebErrorListener;
import com.tempname.keeper.data.WebResponseListener;
import com.tempname.keeper.util.Notification;

import org.json.JSONException;
import org.json.JSONObject;

public class CreateNewTournamentActivity extends Activity {

    private final Activity self = this;
    private int tournamentType = 0;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_create_new_tournament);
    }

    public void returnToPrevious(View view){
        Intent returnIntent = new Intent();
        setResult(RESULT_OK, returnIntent);
        finish();
    }

    public void createNewTournament(View view){
        JSONObject data = new JSONObject();
        try {

            //TODO: Replace with form data instead of hardcoded values
            data.put("Tournament_Name", ((EditText) findViewById(R.id.TournamentName)).getText().toString());
            data.put("Tournament_Type", tournamentType);

            Data.getInstance().update(data, "create-tournament",
                    new WebResponseListener() {
                        @Override
                        public void onResponse(JSONObject response) {
                            Notification.displaySuccessMessage(self, "Tournament created successfully!");
                            self.startActivity(new Intent(self, TournamentsActivity.class));
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
            Notification.displayError(this, "There was an error creating the tournament. Please try again, if the issue persists please contact the developer.");
        }
    }

    public void selectType(View view){
        boolean checked = ((RadioButton)view).isChecked();
        switch (view.getId()){
            case R.id.RoundRobin:
                if (checked){
                    tournamentType = 1;
                }
                break;
            case R.id.Knockout:
                if (checked){
                    tournamentType = 0;
                }
                break;
            case R.id.Combination:
                if (checked){
                    tournamentType = 2;
                }
                break;
        }
    }
}
