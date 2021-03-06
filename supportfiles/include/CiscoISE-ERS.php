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
 *@author	Gary Oppel (gaoppel@cisco.com)
 *@author	Hosuk Won (howon@cisco.com)
 *@contributor	Drew Betz (anbetz@cisco.com)
 */
	
	class CiscoISEERSRestAPI extends BaseRESTCalls {
		
		private $ersRestContentType = "json";
		private $ersRestContentTypeHeader = array('Accept: application/json', 'Content-Type: application/json');
		
		function set_ersContentType($contentType) {
			//Set the Content Type for all ERS methods
			if($contentType == "json"){
				$this->ersRestContentType = "json";
				$ersRestContentTypeHeader = array('Accept: application/json', 'Content-Type: application/json');
				return true;
			}elseif($contentType == "xml"){
				$this->ersRestContentType = "xml";
				$ersRestContentTypeHeader = array('Accept: application/xml', 'Content-Type: application/xml');
				return true;
			}else{
				return false;
			}				
		}
		
		function get_ersContentType(){
			return $this->ersRestContentType;
		}		
		
		function getEndPointbyMacOld($macAddress){
						
			$uriPath = "/ers/config/endpoint?filter=mac.EQ.".$macAddress;
			
			$headerArray = $this->ersRestContentTypeHeader;
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				return $apiSession["body"];
			}else{
				return false;
			}
		}
		
		function getEndPointDetailsbyId($endpointId){
						
			$uriPath = "/ers/config/endpoint/".$endpointId;
			
			$headerArray = $this->ersRestContentTypeHeader;
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				return $apiSession["body"];
			}else{
				return false;
			}
		}
	
		function getEndPointGroupByName($endpointGroupName){
						
			$uriPath = "/ers/config/endpointgroup/name/".$endpointGroupName;

			$headerArray = $this->ersRestContentTypeHeader;
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				return $apiSession["body"];
			}else{
				return false;
			}
		}

		function getEndPointIdentityGroups($pageSize = null, $page = null){
			
			if($pageSize != null || $page != null){
			
				$uriPath = "/ers/config/endpointgroup?size=$pageSize&page=$page";
				
				$headerArray = $this->ersRestContentTypeHeader;
					
				$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);

				$apiSessionResult = json_decode($apiSession["body"], true);
				
				if($apiSession["http_code"] == 200){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("apiSessionResult"=>$apiSessionResult), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:SUCCESS[found_endpoint_groups];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return $apiSession['body'];
				}elseif($apiSession["http_code"] == 404){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups_404];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}else{
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}
			}else{
				$uriPath = "/ers/config/endpointgroup?size=50";
				
				$headerArray = $this->ersRestContentTypeHeader;
					
				$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);

				$apiSessionResult = json_decode($apiSession["body"], true);
				
				if(isset($apiSessionResult['SearchResult']['nextPage']['href'])){
					$multiplePages = true;
				}else{
					$multiplePages = false;
				}
				
				if($apiSession["http_code"] == 200){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("apiSessionResult"=>$apiSessionResult), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:SUCCESS[found_endpoint_groups];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					if($multiplePages == true){

						$currentResourceCount = 0;
						$iseEndpointGroupOutput['SearchResult']['total'] = $apiSessionResult['SearchResult']['total'];
						
						while($multiplePages){
							if(isset($apiSessionResult['SearchResult']['nextPage'])){
								$nextHref = substr($apiSessionResult['SearchResult']['nextPage']['href'],strpos($apiSessionResult['SearchResult']['nextPage']['href'],'/',8), strlen($apiSessionResult['SearchResult']['nextPage']['href']) - strpos($apiSessionResult['SearchResult']['nextPage']['href'],'/',8));
							}else{
								$nextHref = '';
							}
							
							foreach($apiSessionResult['SearchResult']['resources'] as $iseResource){
								$iseEndpointGroupOutput['SearchResult']['resources'][$currentResourceCount]['id'] = $iseResource['id'];
								$iseEndpointGroupOutput['SearchResult']['resources'][$currentResourceCount]['name'] = $iseResource['name'];
								$iseEndpointGroupOutput['SearchResult']['resources'][$currentResourceCount]['description'] = $iseResource['description'];
								$iseEndpointGroupOutput['SearchResult']['resources'][$currentResourceCount]['link'] = $iseResource['link'];
							
								$currentResourceCount++;
							}
								
							if($nextHref == ''){
								$multiplePages = false;
							}else{
								$headerArray = $this->ersRestContentTypeHeader;
								$apiSession = $this->restCall($nextHref, "GET", $headerArray, true);
								$apiSessionResult = json_decode($apiSession["body"], true);
								
								if($apiSession["http_code"] != 200){
									if($this->iPSKManagerClass){
										//LOG::Entry
										$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("apiSessionResult"=>$apiSessionResult), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
										$logMessage = "API-REQUEST:FAILURE[incorrect_next_page_href];";
										$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
									}
									
									$multiplePages = false;
									
									return false;
								}
							}
						}
						
						if($this->iPSKManagerClass){
							//LOG::Entry
							$logjson = json_encode($iseEndpointGroupOutput);
							$logData = $this->iPSKManagerClass->generateLogData(Array("iseEndpointGroupOutput"=>$iseEndpointGroupOutput), Array("iseEndpointGroupOutput"=>$logjson));
							$logMessage = "API-REQUEST:SUCCESS[pageinated_summary];";
							$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						}
						
						return json_encode($iseEndpointGroupOutput);
					}else{
						return $apiSession['body'];
					}
					
				}elseif($apiSession["http_code"] == 404){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups_404];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}else{
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}
			}
		}	

		function getEndPointGroupCountbyId($groupUuid){
						
			if($groupUuid != ''){
				$uriPath = "/ers/config/endpoint?filter=groupId.EQ.".$groupUuid;
				
				$headerArray = $this->ersRestContentTypeHeader;
					
				$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
				
				if($apiSession["http_code"] == 200){
					$tempApiSessionArray = json_decode($apiSession["body"],true);
					
					return $tempApiSessionArray['SearchResult']['total'];
				}else{
					return 0;
				}
			}else{
				return 0;
			}
		}

		function getEndPointsByEPGroup($groupUuid, $pageSize = null, $page = null){
			
			if($pageSize != null || $page != null){
				
				$uriPath = "/ers/config/endpoint?filter=groupId.EQ.".$groupUuid."&size=$pageSize&page=$page";
				
				$headerArray = $this->ersRestContentTypeHeader;
					
				$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);

				$apiSessionResult = json_decode($apiSession["body"], true);
				
				if($apiSession["http_code"] == 200){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("apiSessionResult"=>$apiSessionResult), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:SUCCESS[found_endpoint_groups];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return $apiSession['body'];
				}elseif($apiSession["http_code"] == 404){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups_404];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}else{
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}
			}else{
				$uriPath = "/ers/config/endpoint?size=50&filter=groupId.EQ.".$groupUuid."";
				
				$headerArray = $this->ersRestContentTypeHeader;
					
				$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);

				$apiSessionResult = json_decode($apiSession["body"], true);
				
				if(isset($apiSessionResult['SearchResult']['nextPage']['href'])){
					$multiplePages = true;
				}else{
					$multiplePages = false;
				}
				
				if($apiSession["http_code"] == 200){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("apiSessionResult"=>$apiSessionResult), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:SUCCESS[found_endpoints];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					if($multiplePages == true){

						$currentResourceCount = 0;
						$iseEndpointOutput['SearchResult']['total'] = $apiSessionResult['SearchResult']['total'];
						
						while($multiplePages){
							if(isset($apiSessionResult['SearchResult']['nextPage'])){
								$nextHref = substr($apiSessionResult['SearchResult']['nextPage']['href'],strpos($apiSessionResult['SearchResult']['nextPage']['href'],'/',8), strlen($apiSessionResult['SearchResult']['nextPage']['href']) - strpos($apiSessionResult['SearchResult']['nextPage']['href'],'/',8));
							}else{
								$nextHref = '';
							}
							
							foreach($apiSessionResult['SearchResult']['resources'] as $iseResource){
								$iseEndpointOutput['SearchResult']['resources'][$currentResourceCount]['id'] = $iseResource['id'];
								$iseEndpointOutput['SearchResult']['resources'][$currentResourceCount]['name'] = $iseResource['name'];
								$iseEndpointOutput['SearchResult']['resources'][$currentResourceCount]['description'] = (isset($iseResource['description'])) ? $iseResource['description'] : '';
								$iseEndpointOutput['SearchResult']['resources'][$currentResourceCount]['link'] = $iseResource['link'];
							
								$currentResourceCount++;
							}
								
							if($nextHref == ''){
								$multiplePages = false;
							}else{
								$headerArray = $this->ersRestContentTypeHeader;
								$apiSession = $this->restCall($nextHref, "GET", $headerArray, true);
								$apiSessionResult = json_decode($apiSession["body"], true);
								
								if($apiSession["http_code"] != 200){
									if($this->iPSKManagerClass){
										//LOG::Entry
										$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("apiSessionResult"=>$apiSessionResult), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
										$logMessage = "API-REQUEST:FAILURE[incorrect_next_page_href];";
										$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
									}
									
									$multiplePages = false;
									
									return false;
								}
							}
						}
						
						if($this->iPSKManagerClass){
							//LOG::Entry
							$logjson = json_encode($iseEndpointOutput);
							$logData = $this->iPSKManagerClass->generateLogData(Array("iseEndpointOutput"=>$iseEndpointOutput), Array("iseEndpointOutputArray"=>$logjson));
							$logMessage = "API-REQUEST:SUCCESS[pageinated_summary];";
							$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
						}
						
						return json_encode($iseEndpointOutput);
					}else{
						return $apiSession['body'];
					}
					
				}elseif($apiSession["http_code"] == 404){
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups_404];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}else{
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
						$logMessage = "API-REQUEST:FAILURE[failure_to_find_endpoint_groups];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}
			}
		}

		function check_ifAuthZProfileExists($name){
			
			$uriPath = "/ers/config/authorizationprofile/name/".$name;
			
			$headerArray = $this->ersRestContentTypeHeader;
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:SUCCESS[ise_authz_profile_found];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return true;
			}elseif($apiSession["http_code"] == 404){
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:FAILURE[ise_authz_profile_not_found];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}else{
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:FAILURE[ise_authz_profile_other_error];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}
		}
		
		function getAuthorizationProfile($name){
			
			$uriPath = "/ers/config/authorizationprofile/name/".$name;
			
			$headerArray = $this->ersRestContentTypeHeader;
				
			$apiSession = $this->restCall($uriPath, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:SUCCESS[ise_authz_profile_found];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return $apiSession["body"];
			}elseif($apiSession["http_code"] == 404){
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:FAILURE[failure_to_create_ise_authz_profile];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}else{
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:FAILURE[ise_authz_profile_not_found];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}
			
		}
		
		function createAuthorizationProfile($data){
			
			$uriPath = "/ers/config/authorizationprofile";
			
			$headerArray = $this->ersRestContentTypeHeader;
				
			$apiSession = $this->restCall($uriPath, "POST", $headerArray, true, $data);
			
			if($apiSession["http_code"] == 201){
				return true;
			}else{
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:FAILURE[create_ise_authz_profile_failure];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}
			
		}
		
		function updateEndPointDetailsbyId($endpointId, $data){
						
			$uriPath = "/ers/config/endpoint/".$endpointId;
			
			$headerArray = $this->ersRestContentTypeHeader;
				
			$apiSession = $this->restCall($uriPath, "PUT", $headerArray, true, $data);
			
			if($apiSession["http_code"] == 200){
				return true;
			}else{
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:FAILURE[failure_to_update_ise_endpoint_by_id];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}
		}

		function createEndPoint($macAddress, $fullName, $description, $email, $psk, $expirationDate, $createdBy){
			$uriPath = "/ers/config/endpoint";
			$endpointDetails = '{"ERSEndPoint": {
				"name": "name",
				"description": "'.$description.'",
				"mac": "'.$macAddress.'",
				"staticProfileAssignment": false,
				"staticGroupAssignment": false,
				"portalUser": "'.$createdBy.'",
				"customAttributes": {
					"customAttributes": {
					"psk": "'.$psk.'",
					"email": "'.$email.'"
				}}}}';

			$headerArray = $this->ersRestContentTypeHeader;
			$data = json_encode($endpointDetails);
			$apiSession = $this->restCall($uriPath, "POST", $headerArray, true, $endpointDetails);
			
			if($apiSession["http_code"] == 201){
				return true;
			}else{
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:FAILURE[create_ise_EndPoint_failure];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}
		}

		function getEndPointByMac($macAddress){
			$uriPath = "/ers/config/endpoint";
			$getQueryString = "?filter=mac.EQ.$macAddress";

			$headerArray = $this->ersRestContentTypeHeader;
			$apiSession = $this->restCall($uriPath.$getQueryString, "GET", $headerArray, true);
			
			if($apiSession["http_code"] == 200){
				return $apiSession["body"];
			}else{
				//LOG::Entry
				$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
				$logMessage = "API-REQUEST:FAILURE[get_endpoint_by_mac];MACAddress:$macAddress";
				$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			
				return false;
			}
		}

		function updateEndPointGroupAssociation($macAddress, $associationGroup){
			$uriPath = "/ers/config/endpoint/";
			$endpoint = $this->getEndPointByMac($macAddress);
			$endpointArray = json_decode($endpoint,true);
			$uriPath = $uriPath.$endpointArray["SearchResult"]["resources"][0]["id"];
			$group = $this->getEndPointGroupByName($associationGroup);
			//print_r("<html><br>Group response:".var_dump($group));
			$groupArray = json_decode($group,true);
			//print_r("<html><br>grouparray: ".var_dump($groupArray));
			$endpointDetails = '{"ERSEndPoint": {
				"groupId": "'.$groupArray["EndPointGroup"]["id"].'",
				"staticGroupAssignment": true
				}}';

			$headerArray = $this->ersRestContentTypeHeader;
			$data = json_encode($endpointDetails);
			$apiSession = $this->restCall($uriPath, "PUT", $headerArray, true, $endpointDetails);
			//LOG::Entry
			$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
			$logMessage = "API-REQUEST:DEBUG[update_ise_EndPoint_group_assignment];endpointDetails:".var_dump($data).";";
			$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			if($apiSession["http_code"] == 200){
				return true;
			}else{
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST:FAILURE[update_ise_EndPoint_Group_Association_failure];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}
		}

		function createCaptivePortalAuthzProfile($profileName, $profileDescription, $portalUrl){
			
			if(!$this->check_ifAuthZProfileExists($profileName)){
				$authzProfile = '{"AuthorizationProfile":{"name":"","description":"","advancedAttributes":[{"leftHandSideDictionaryAttribue":{"AdvancedAttributeValueType":"AdvancedDictionaryAttribute","dictionaryName":"Cisco","attributeName":"cisco-av-pair"},"rightHandSideAttribueValue":{"AdvancedAttributeValueType":"AttributeValue","value":""}}],"accessType":"ACCESS_ACCEPT","authzProfileType":"SWITCH","trackMovement":false,"serviceTemplate":false,"easywiredSessionCandidate":false,"voiceDomainPermission":false,"neat":false,"webAuth":false,"profileName":"Cisco"}}';
				
				//Convert JSON to Array
				$authzProfileArray = json_decode($authzProfile,TRUE);
				
				//Setup URL the Settings
				$redirectUrl = "url-redirect=".$portalUrl."&sessionId=SessionIdValue&client_mac=ClientMacValue";
				
				//Setup the Required Settings
				$authzProfileArray['AuthorizationProfile']['name'] = $profileName;
				$authzProfileArray['AuthorizationProfile']['description'] = $profileDescription;
				$authzProfileArray['AuthorizationProfile']['advancedAttributes'][0]['rightHandSideAttribueValue']['value'] = $redirectUrl;
				
				$authzJsonData = json_encode($authzProfileArray);
				
				if($this->createAuthorizationProfile($authzJsonData)){
					return true;
				}else{
					if($this->iPSKManagerClass){
						//LOG::Entry
						$logData = $this->iPSKManagerClass->generateLogData(Array("authzProfileArray"=>$authzProfileArray), Array("authzJsonData"=>$authzJsonData));
						$logMessage = "API-REQUEST:FAILURE[failure_to_create_ise_authz_profile];";
						$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
					}
					
					return false;
				}	
			}else{
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("authzProfileArray"=>$authzProfileArray), Array("authzJsonData"=>$authzJsonData));
					$logMessage = "API-REQUEST:FAILURE[authz_profile_ise_already_exists];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}
			
		}

		function DeleteEndPointByMac($macAddress){
			$uriPath = "/ers/config/endpoint/";
			$endpoint = $this->getEndPointByMac($macAddress);
			$endpointArray = json_decode($endpoint,true);
			$uriPath = $uriPath.$endpointArray["SearchResult"]["resources"][0]["id"];

			$headerArray = $this->ersRestContentTypeHeader;
			$apiSession = $this->restCall($uriPath, "DELETE", $headerArray, true, $endpointDetails);
			//LOG::Entry
			$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
			$logMessage = "API-REQUEST-DELETE:DEBUG[Delete_EndPoint];deleteURI:".$uriPath.";";
			$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
			if($apiSession["http_code"] == 204){
				return true;
			}else{
				if($this->iPSKManagerClass){
					//LOG::Entry
					$logData = $this->iPSKManagerClass->generateLogData(Array("apiSession"=>$apiSession), Array("headerArray"=>$headerArray), Array("uriPath"=>$uriPath));
					$logMessage = "API-REQUEST-DELETE:FAILURE[Delete_EndPoint];";
					$this->iPSKManagerClass->addLogEntry($logMessage, __FILE__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, $logData);
				}
				
				return false;
			}
		}
	}	
?>