/*
Copyright (c) 2019 Cisco and/or its affiliates.

This software is licensed to you under the terms of the Cisco Sample
Code License, Version 1.1 (the "License"). You may obtain a copy of the
License at

			   https://developer.cisco.com/docs/licenses

All use of the material herein must be in accordance with the terms of
the License. All rights not expressly granted by the License are
reserved. Unless required by applicable law or agreed to separately in
writing, software distributed under the License is distributed on an "AS
IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
or implied.
*/


$(document).ready(function() {
	
	feather.replace()
	
	$('[data-toggle="popover"]').popover()
	
	$.ajax({
		url: "ajax/getmodule.php",
		
		data: {
			module: "dashboard"
		},
		type: "POST",
		dataType: "html",
		success: function (data) {
			$('#mainContent').html(data);
		},
		error: function (xhr, status) {
			//$('#mainContent').html(data);
		},
		complete: function (xhr, status) {
			//$('#mainContent').slideDown('slow')
		}
	})
	
	$(".sideNav").click(function(event) {
		$("#currentMenuItem").remove();
		$("a.sideNav.active").removeClass("active");
		$(this).addClass("active");
		$(this).append("<span id=\"currentMenuItem\" class=\"sr-only\">(current)</span>");
		
		$.ajax({
			url: "ajax/getmodule.php",
			
			data: {
				module: $(this).attr('module')
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				$('#mainContent').html(data);
			},
			error: function (xhr, status) {
				$('#mainContent').html("<h6 class=\"text-center\"><span class=\"text-danger\">Error Loading Selection:</span>  Verify the installation/configuration and/or contact your system administrator!</h6>");
			},
			complete: function (xhr, status) {
				//$('#showresults').slideDown('slow')
			}
		});
		
		event.preventDefault();
	});
	
	$("#signOut").click(function(event) {
		$.ajax({
			url: "/logoff.php",
			
			data: {
				logoff: true
			},
			type: "POST",
			dataType: "html",
			success: function (data) {
				window.location = "/";
			}
		});
		
		event.preventDefault();
	});
	
});	

function formFieldValidation(){
	var validationFailure;

	validationFailure = false;
	minValidateFailure = false;
	
	//Form Validation Object Loop
	$('.form-validation').each(function() {
		if($(this).attr('validation-state') == 'required'){
			if($(this).attr('validation-minimum-length') || $(this).attr('validation-maximum-length')){
				if($(this).attr('validation-minimum-length')){
					//Check Field Min Length
					if($(this).val().length < $(this).attr('validation-minimum-length')){
						$(this).removeClass('is-valid');
						$(this).addClass('is-invalid');
						validationFailure = true;
						minValidateFailure = true;
					}else{
						$(this).removeClass('is-invalid');
						$(this).addClass('is-valid');
					}
				}

				if($(this).attr('validation-maximum-length')){
					//Check Field Max Length
					if($(this).attr('validation-maximum-length') && !minValidateFailure){
						if($(this).val().length > $(this).attr('validation-maximum-length')){
							$(this).removeClass('is-valid');
							$(this).addClass('is-invalid');
							validationFailure = true;
						}else{
							$(this).removeClass('is-invalid');
							$(this).addClass('is-valid');
						}
					}
				}
			}else{
				//Check Field not Empty
				if($(this).val() == ''){
					$(this).removeClass('is-valid');
					$(this).addClass('is-invalid');
					validationFailure = true;
				}else{
					$(this).removeClass('is-invalid');
					$(this).addClass('is-valid');
				}
			}
		}else if($(this).attr('validation-state') == 'special'){
			
			//Check Field not Empty
			if($(this).val() == ''){
				$(this).removeClass('is-valid');
				$(this).addClass('is-invalid');
				validationFailure = true;
			}else if($(this).val() == 'Random'){
				$(this).removeClass('is-invalid');
				$(this).addClass('is-valid');
			}else{
				$(this).removeClass('is-invalid');
				$(this).addClass('is-valid');
			}
		}else if($(this).attr('validation-state') == 'notempty'){
			//Check Field not Empty
			if($(this).val() == ''){
				$(this).removeClass('is-valid');
				$(this).addClass('is-invalid');
				validationFailure = true;
			}else{
				$(this).removeClass('is-invalid');
				$(this).addClass('is-valid');
			}
		}else{
			$(this).removeClass('is-invalid');
			$(this).addClass('is-valid');
		}
	});
	
	return validationFailure;
}