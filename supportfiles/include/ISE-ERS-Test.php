<?php

/**
 *@license
 *Copyright (c) 2019 Cisco and/or its affiliates.
 *
 *This software is licensed to you under the terms of the Cisco Sample
 *Code License, Version 1.1 (the "License"). You may obtain a copy of the
 *License at
 *
 *			   https://developer.cisco.com/docs/licenses
 *
 *All use of the material herein must be in accordance with the terms of
 *the License. All rights not expressly granted by the License are
 *reserved. Unless required by applicable law or agreed to separately in
 *writing, software distributed under the License is distributed on an "AS
 *IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 *or implied.
 */

/**
 *@author	George Bekmezian (george.bekmezian@cvetech.com)
 *@contributor	
 */

    include("iPSKManagerFunctions.php");
    include("iPSKManagerDatabase.php");
    include("config.php");
    include("BaseRestClass.php");
    include("CiscoISE-ERS.php");

    $ipskISEDB = new iPSKManagerDatabase($dbHostname, $dbUsername, $dbPassword, $dbDatabase);

    $ipskISEDB->set_encryptionKey($encryptionKey);
    $encryptionKey = "";
 
    $ersCreds = $ipskISEDB->getISEERSSettings();
    print_r($ersCreds);
    if($ersCreds['enabled'])
    {

        if(!isset($ersCreds['verify-ssl-peer']))
        {
            $ersCreds['verify-ssl-peer'] = true;
        }

        $ipskISEERS = new CiscoISEERSRestAPI($ersCreds['ersHost'], $ersCreds['ersUsername'], $ersCreds['ersPassword'], $ersCreds['verify-ssl-peer'], $ipskISEDB);
        $ersCreds = "";

        $iseERSIntegrationAvailable = $ipskISEDB->getISEERSSettings()['enabled'];
        if($iseERSIntegrationAvailable)
        {
            $endpointIdentityGroups = $ipskISEERS->getEndPointIdentityGroups();

            if($endpointIdentityGroups)
            {
                $endpointIdentityGroupsArray = json_decode($endpointIdentityGroups,TRUE);
                $endpointIdentityGroupsArray = arraySortAlpha($endpointIdentityGroupsArray);
                $endpointIdentityGroups = json_encode($endpointIdentityGroupsArray);

                #print $endpointIdentityGroups;
                #print_r(json_decode($endpointIdentityGroups, true));
                $epIdGroups = json_decode($endpointIdentityGroups, true);
                $x = 0;
                #$y = 0;
                #$z = 0;
                foreach($epIdGroups["SearchResult"]["resources"] as $item)
                {
                    #print_r("Level x ".$x."\n");
                    print($item["name"] . "\n");
                    #$x++;
                }
            }
            #print("x: " . $x . " y: " . $y . " z: " . $z . "\n");
        }
    }else
    {
	    print "ersCreds not enabled?";
    }
    print("\nfetched endpoint groups: \n");
?>