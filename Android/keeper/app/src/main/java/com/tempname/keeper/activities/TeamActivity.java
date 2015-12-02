package com.tempname.keeper.activities;

import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.view.View;

import com.tempname.keeper.R;

public class TeamActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_team);

        //TODO: implement a clickable list of teams
    }

    public void CreateNewTeam(View view) {
        Intent intent = new Intent(getApplicationContext(), NewTeamActivity.class); //Application Context and Activity
        startActivityForResult(intent, 0);
    }


}
