package com.tempname.keeper.activities;

import android.content.Intent;
import android.os.Bundle;
import android.app.Activity;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.EditText;
import android.widget.ListAdapter;
import android.widget.ListView;

import com.tempname.keeper.R;
import com.tempname.keeper.data.Data;
import com.tempname.keeper.data.WebErrorListener;
import com.tempname.keeper.data.WebResponseListener;
import com.tempname.keeper.util.Notification;

import org.json.JSONException;
import org.json.JSONObject;

public class ViewTournamentActivity extends Activity {

    private final Activity self = this;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_view_tournament);
        //getActionBar().setDisplayHomeAsUpEnabled(true);

        //fix this method to display a list of all teams in tournament which when clicked on will go to the team page
        //where it can be edited, added, or deleted from tournament
        String[] teams = {"teamA", "teamB"};
        ListAdapter adapter = new ArrayAdapter<String>(self, android.R.layout.simple_list_item_1, teams);
        ListView listview = (ListView)findViewById(R.id.TeamList);
        listview.setAdapter(adapter);
        listview.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                //Intent intent = new Intent(self, ViewTeamActivity.class);
                //startActivityForResult(intent, 0);
            }
        });
    }

    public void startTournament(View view){
        JSONObject data = new JSONObject();
        try {
            //TODO: Replace with form data instead of hardcoded values
            //needs to be corrected
            data.put("Tournament_Name", ((EditText) findViewById(R.id.TournamentName)).getText().toString());
            data.put("Tournament_Type", 0);

            Data.getInstance().update(data, "begin-tournament",
                    new WebResponseListener() {
                        @Override
                        public void onResponse(JSONObject response) {
                            Notification.displaySuccessMessage(self, "Tournament started successfully!");
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
            Notification.displayError(this, "There was an error starting the tournament. Please try again, if the issue persists please contact the developer.");
        }
    }

    public void returnToPrevious(View view){
        Intent returnIntent = new Intent();
        setResult(RESULT_OK, returnIntent);
        finish();
    }

}
