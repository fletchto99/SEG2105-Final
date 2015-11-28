package com.tempname.keeper.data;

import org.json.JSONException;
import org.json.JSONObject;

public interface WebErrorListener {

    void onError(JSONObject error) throws JSONException;

}
