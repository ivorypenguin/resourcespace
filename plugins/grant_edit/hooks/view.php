<?php

function HookGrant_editViewBeforepermissionscheck()
    {
    global $ref,$userref, $access;
    $grant_edit=sql_value("select resource value from grant_edit where resource='$ref' and user='$userref' and (expiry is null or expiry>=NOW())","");
    if($grant_edit!=""){$access=0;}
    return true;        
    }