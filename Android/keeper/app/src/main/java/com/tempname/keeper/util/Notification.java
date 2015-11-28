package com.tempname.keeper.util;

import android.content.Context;
import android.view.Gravity;
import android.view.View;
import android.widget.Toast;

public class Notification {

    public static void displayError(Context context, String text) {
        Toast message = Toast.makeText(context, text, Toast.LENGTH_LONG);
        message.setGravity(Gravity.TOP|Gravity.CENTER_HORIZONTAL, 0, 0);
        View view = message.getView();
        view.setBackgroundColor(0xAFFF0000);
        message.setView(view);
        message.show();
    }

    public static void displaySuccessMessage(Context context, String text) {
        Toast message = Toast.makeText(context, text, Toast.LENGTH_LONG);
        message.setGravity(Gravity.TOP|Gravity.CENTER_HORIZONTAL, 0, 0);
        View view = message.getView();
        view.setBackgroundColor(0xAF00FF00);
        message.setView(view);
        message.show();
    }

    public static void displayNotification(Context context, String text) {
        Toast message = Toast.makeText(context, text, Toast.LENGTH_LONG);
        message.setGravity(Gravity.TOP|Gravity.CENTER_HORIZONTAL, 0, 0);
        View view = message.getView();
        view.setBackgroundColor(0xAF000000);
        message.setView(view);
        message.show();
    }

}
