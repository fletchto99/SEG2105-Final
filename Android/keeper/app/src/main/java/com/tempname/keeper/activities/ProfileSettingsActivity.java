package com.tempname.keeper.activities;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;

import com.tempname.keeper.R;

public class ProfileSettingsActivity extends Activity {


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_profile_settings);

        ((EditText) this.findViewById(R.id.jerseyNumberInputProfileSettings)).setText(getIntent().getStringExtra("Jersey_Number"));
    }

    public void saveProfile(View view) {

        Intent returnIntent = new Intent();
        returnIntent.putExtra("Jersey_Number", ((EditText) this.findViewById(R.id.jerseyNumberInputProfileSettings)).getText().toString());
        setResult(RESULT_OK, returnIntent);
        finish();
    }
}
