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
	

	$authList = "";
	
	$authorizationTemplatesNames = $ipskISEDB->getAuthorizationTemplates();
	
	if($authorizationTemplatesNames){
		while($row = $authorizationTemplatesNames->fetch_assoc()) {
			$authList .= "<option value=\"".$row['id']."\">".$row['authzPolicyName']."</option>\n";
		}
	}

	$pageData['GroupTypeOptions'] = '<option value="0">Internal</option><option value="3">Cisco ISE "Endpoint Group"</option>';

$htmlbody = <<<HTML
<!-- Modal -->
<div class="modal fade" id="viewepggroup" tabindex="-1" role="dialog" aria-labelledby="viewepggroupModal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLongTitle">Add Endpoint Grouping</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="container-fluid">
					<div class="form-group input-group-sm font-weight-bold">
						<label class="font-weight-bold" for="epGroupType">Endpoint Group Type:</label>
						<div class="form-group input-group-sm font-weight-bold">
							<select name="endpointGroupType" id="endpointGroupType" class="form-control mt-2 mb-3 shadow">{$pageData['GroupTypeOptions']}</select>
						</div>
					</div>
				</div>
				<label class="font-weight-bold" for="epGroupName">iPSK Endpoint Group Name:</label>
				<div id="epGroupNameDiv" class="form-group input-group-sm font-weight-bold">
					<input type="text" class="form-control shadow form-validation" validation-state="required" id="epGroupName">
				</div>
				<div id="epGroupSelectNameDiv" class="form-group input-group-sm font-weight-bold">
					<select id="epGroupNameSel" class="form-control mt-2 mb-3 shadow">
						<option>nothing to see</option>
					</select>
				</div>
				<label class="font-weight-bold" for="epGroupDescription">Description:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<input type="text" class="form-control shadow" id="epGroupDescription">
				</div>
				<div class="form-row">
					<div class="col">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input checkbox-update" base-value="1" value="0" id="notificationPermission">
							<label class="custom-control-label" for="notificationPermission">Email Notifications</label>
						</div>
					</div>
				</div>	
				<label class="font-weight-bold" for="authzTemplate">Authorization Template:</label>
				<div class="form-group input-group-sm font-weight-bold">
					<select id="authzTemplate" class="form-control mt-2 mb-3 shadow">
						$authList
					</select>
				</div>
			</div>
			<div class="modal-footer">
				<a id="create" href="#" module="epgroup" sub-module="create" role="button" class="btn btn-primary shadow" data-dismiss="modal">Create</a>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<script>
	var failure;
	
	$("#viewepggroup").modal();

	$(function() {	
		feather.replace()
	});
	
	var myitter = 0;
	$("#endpointGroupType").change(function() {
		if (myitter % 2 == 0){
			$("#epGroupNameDiv").hide();
			$("#epGroupSelectNameDiv").show();
			var dropdown = $('#epGroupNameSel');
			dropdown.empty();
			dropdown.append('<option selected="true" disabled>Choose State/Province</option>');
			dropdown.prop('selectedIndex', 0);
			$.ajax({
				url: "ajax/getdata.php",

				data: {
					'data-command': 'getdata',
					'data-set': 'iseEpGroups',
				},
				type: "GET",
				dataType: "html",
				success: function (data) {
					dropdown.append($('<option></option>').attr('value', data).text(data));
				},
				error: function (xhr, status) {
					dropdown.append($('<option></option>').attr('value', "unable to fetch ISE groups").text("unable to fetch ISE groups"));
				},
				complete: function (xhr, status) {
					//$('#showresults').slideDown('slow')
				}
			});
		} 
		else 
		{
			$("#epGroupNameDiv").show();
			$("#epGroupSelectNameDiv").hide();
		}
	
		myitter += 1;

	});

	$("#showpassword").on('click', function(event) {
		event.preventDefault();
		if($("#presharedKey").attr('type') == "text"){
			$("#presharedKey").attr('type', 'password');
			$("#passwordfeather").attr('data-feather','eye');
			feather.replace();
		}else if($("#presharedKey").attr('type') == "password"){
			$("#presharedKey").attr('type', 'text');
			$("#passwordfeather").attr('data-feather','eye-off');
			feather.replace();
		}
	});
	
	$("#create").click(function(){
		event.preventDefault();
		
		failure = formFieldValidation();
		
		if(failure){
			return false;
		}
		
		$("#viewepggroup").modal({show: false});
		$('.modal-backdrop').remove();
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module'),
				'sub-module': $(this).attr('sub-module'),
				epGroupName: $("#epGroupName").val(),
				epGroupDescription: $("#epGroupDescription").val(),
				authzTemplate: $("#authzTemplate").children("option:selected").val(),
				notificationPermission: $("#notificationPermission").val()
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#popupcontent').html(data);
			},
			error: function (xhr, status) {
				$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			},
			complete: function (xhr, status) {
				//$('#showresults').slideDown('slow')
			}
		});
	});
	
	$(".checkbox-update").change(function(){
		if($(this).prop('checked')){
			$(this).attr('value', $(this).attr('base-value'));		
		}else{
			$(this).attr('value', '0');
		}
		
	});
</script>
HTML;

print $htmlbody;
?>