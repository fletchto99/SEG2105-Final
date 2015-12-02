package com.tempname.keeper.activities;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.ListAdapter;
import android.widget.ListView;

import com.tempname.keeper.R;

public class TournamentsActivity extends Activity {

    private final Activity self = this;
    /**
     * ATTENTION: This was auto-generated to implement the App Indexing API.
     * See https://g.co/AppIndexing/AndroidStudio for more information.
     */
    //private GoogleApiClient client;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_tournaments);

        //fix this method to display a list of all tournaments
        String[] tournaments = {"tournamentA", "tournamentB"};
        ListAdapter adapter = new ArrayAdapter<String>(self, android.R.layout.simple_list_item_1, tournaments);
        ListView listview = (ListView)findViewById(R.id.TournamentList);
        listview.setAdapter(adapter);
        listview.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                Intent intent = new Intent(self, ViewTournamentActivity.class);
                startActivityForResult(intent, 0);
            }
        });

        /*Data.getInstance().get(null, "tournaments", new WebResponseListener() {
            @Override
            public void onResponse(JSONObject response) throws JSONException {
                //String tournaments = response.toString();
                //test
                String[] tournaments = {"tournamentA", "tournamentB"};
                ListAdapter adapter = new ArrayAdapter<String>(self, android.R.layout.simple_list_item_1, tournaments);
                ListView listview = (ListView)findViewById(R.id.TournamentList);
                listview.setAdapter(adapter);
                listview.setOnItemClickListener(new AdapterView.OnItemClickListener() {
                    @Override
                    public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                        Intent intent = new Intent(getApplicationContext(), ViewTournamentActivity.class);
                        startActivityForResult(intent, 0);
                    }
                });
            }
        }, new WebErrorListener() {
            @Override
            public void onError(JSONObject error) throws JSONException {

            }
        });

        // ATTENTION: This was auto-generated to implement the App Indexing API.
        // See https://g.co/AppIndexing/AndroidStudio for more information.
        client = new GoogleApiClient.Builder(this).addApi(AppIndex.API).build();*/
    }

    public void newTournament(View view) {
        Intent intent = new Intent(getApplicationContext(), CreateNewTournamentActivity.class);
        startActivityForResult(intent, 0);
    }

    public void returnToPrevious(View view) {
        Intent returnIntent = new Intent();
        setResult(RESULT_OK, returnIntent);
        finish();
    }

    /*@Override
    public void onStart() {
        super.onStart();

        // ATTENTION: This was auto-generated to implement the App Indexing API.
        // See https://g.co/AppIndexing/AndroidStudio for more information.
        client.connect();
        Action viewAction = Action.newAction(
                Action.TYPE_VIEW, // TODO: choose an action type.
                "Tournaments Page", // TODO: Define a title for the content shown.
                // TODO: If you have web page content that matches this app activity's content,
                // make sure this auto-generated web page URL is correct.
                // Otherwise, set the URL to null.
                Uri.parse("http://host/path"),
                // TODO: Make sure this auto-generated app deep link URI is correct.
                Uri.parse("android-app://com.tempname.keeper.activities/http/host/path")
        );
        AppIndex.AppIndexApi.start(client, viewAction);
    }

    @Override
    public void onStop() {
        super.onStop();

        // ATTENTION: This was auto-generated to implement the App Indexing API.
        // See https://g.co/AppIndexing/AndroidStudio for more information.
        Action viewAction = Action.newAction(
                Action.TYPE_VIEW, // TODO: choose an action type.
                "Tournaments Page", // TODO: Define a title for the content shown.
                // TODO: If you have web page content that matches this app activity's content,
                // make sure this auto-generated web page URL is correct.
                // Otherwise, set the URL to null.
                Uri.parse("http://host/path"),
                // TODO: Make sure this auto-generated app deep link URI is correct.
                Uri.parse("android-app://com.tempname.keeper.activities/http/host/path")
        );
        AppIndex.AppIndexApi.end(client, viewAction);
        client.disconnect();
    }*/
}
