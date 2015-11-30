package com.tempname.keeper.wrappers;

import java.io.Serializable;

public class Account implements Serializable {

    private final String id;
    private final String role;
    private String firstName;
    private String lastName;
    private String jerseyNumber;
    private String avatar;
    private String teamID;

    public Account(String id, String firstName, String lastName, String jerseyNumber, String avatar, String teamID, String role) {
        this.id = id;
        this.firstName = firstName;
        this.lastName = lastName;
        this.jerseyNumber = jerseyNumber;
        this.avatar = avatar;
        this.teamID = teamID;
        this.role = role;
    }

    public String getID() {
        return id;
    }

    public String getFirsName() {
        return firstName;
    }

    public String getLastName() {
        return lastName;
    }

    public String getJerseyNumber() {
        return jerseyNumber;
    }

    public String getAvatar() {
        return avatar;
    }

    public String getTeam() {
        return teamID;
    }

    public String getRole() {
        return role;
    }

    public void setFirstName(String firstName) {
        this.firstName = firstName;
    }

    public void setLastName(String lastName) {
        this.lastName = lastName;
    }

    public void setJerseyNumber(String jerseyNumber) {
        this.jerseyNumber = jerseyNumber;
    }

}
