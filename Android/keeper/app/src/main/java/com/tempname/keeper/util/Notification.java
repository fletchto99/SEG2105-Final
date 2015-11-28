package com.tempname.keeper.util;

import android.content.Context;
import android.view.View;
import android.widget.Toast;

public class Notification {

    public static Toast displayError(Context context, String text) {
        Toast message = Toast.makeText(context, text, Toast.LENGTH_SHORT);
        View view = message.getView();
        view.setBackgroundColor(0x7FFF0000);
        message.setView(view);
        message.show();
        return message;
    }

    public static Toast displaySuccessMessage(Context context, String text) {
        Toast message = Toast.makeText(context, text, Toast.LENGTH_SHORT);
        View view = message.getView();
        view.setBackgroundColor(0x7F00FF00);
        message.setView(view);
        message.show();
        return message;
    }

    public static Toast displayNotification(Context context, String text) {
        Toast message = Toast.makeText(context, text, Toast.LENGTH_SHORT);
        View view = message.getView();
        view.setBackgroundColor(0x7F000000);
        message.setView(view);
        message.show();
        return message;
    }

}
