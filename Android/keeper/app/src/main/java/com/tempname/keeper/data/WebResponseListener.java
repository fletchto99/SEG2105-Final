package com.tempname.keeper.data;

import org.json.JSONException;
import org.json.JSONObject;

public interface WebResponseListener {

    void onResponse(JSONObject error) throws JSONException;
}
