var map, centerLat = 29.648612, centerLng = -82.343504 ;
var http ;
var output ;
var testmenu ;
var nodes ;

if(navigator.appName == "Microsoft Internet Explorer")
{
	http = new ActiveXObject("Microsoft.XMLHTTP") ;
}
else
{
	http = new XMLHttpRequest() ;
}
	
function initialize() 
{
	output = new Output("output") ;
	$("#output-expand").click(function() {output.expand();}) ;
	$("#output-minimize").click(function() {output.minimize();}) ;
	$("#output-remove").click(function() {output.remove();}) ;
	
	map = new google.maps.Map(document.getElementById("map"), {
	  center: {lat: centerLat, lng: centerLng},
	  draggableCursor: "pointer",
	  clickableIcons: false,
	  zoom: 18
	});
	google.maps.event.clearListeners(map, "dblclick") ;
	output.add("Google Maps API loaded and ready") ;
	output.timestamp() ;
	
	testmenu = new TestMenu("expTest", "devTest", "rssTest") ;
	nodes = new Nodes(map) ;
}

function Output(containerID)
{
	//member variables
	this.container = $("#" + containerID);
	this.recent;
	this.content = new Array();
	
	//initialization
	var output_expand = this.container.find("#output-expand");
	output_expand.hover( function() { $(this).css("background","#dcdcdc").find("img").toggleClass("d-none"); },
						function() { $(this).css("background","#17202a").find("img").toggleClass("d-none"); })
			.mousedown( function() { $(this).css("background","#bd2130").find("img").toggleClass("d-none"); })
			.mouseup( function() { $(this).css("background","#dcdcdc").find("img").toggleClass("d-none"); });
	var output_minimize = this.container.find("#output-minimize");
	output_minimize.hover( function() { $(this).css("background","#dcdcdc").find("img").toggleClass("d-none"); },
							function() { $(this).css("background","#17202a").find("img").toggleClass("d-none"); })
			.mousedown( function() { $(this).css("background","#bd2130").find("img").toggleClass("d-none"); })
			.mouseup( function() { $(this).css("background","#dcdcdc").find("img").toggleClass("d-none"); });
	var output_remove = this.container.find("#output-remove");
	output_remove.hover( function() { $(this).css("background","#dcdcdc").find("img").toggleClass("d-none"); },
						function() { $(this).css("background","#17202a").find("img").toggleClass("d-none"); })
			.mousedown( function() { $(this).css("background","#bd2130").find("img").toggleClass("d-none"); })
			.mouseup( function() { $(this).css("background","#dcdcdc").find("img").toggleClass("d-none"); });
	
	//functions
	this.add = add ;
	this.push = push ;
	this.timestamp = timestamp ;
	this.remove = remove ;
	this.minimize = minimize ;
	this.expand = expand ;
	
	function add(text)
	{
		this.recent = "<span class=\"rainbow\">" + text + "</span>" ;
		var parse = $.parseHTML(this.recent) ;
		$(parse).html(function(i, html) {
			var chars = $.trim(html).split("");
			var word = "<span>";
			for(i = 0; i < chars.length; i++)
				word += chars[i] + "</span><span style=\"animation-delay: " +.02*i+ "s\">";
			word += "</span>";
			return word;
		}) ;
		$("#p-recent").append(parse) ;
	}
	
	function push()
	{
		$("#p-recent").html("") ;
		$("#p-history").prepend("<p style=\"display: block\">" + this.recent + "</p>") ;
	}
	
	function timestamp()
	{
		var time = new Date() ;
		var str = "<span class=\"text-light timestamp\">&nbsp&nbsp-" + time.getHours() + ":" + time.getMinutes() + ":" + time.getSeconds() + "</span>";
		$("#p-recent").append(str) ;
		this.recent += str ;
	}
	
	function remove()
	{
		this.container.addClass("d-none") ;
	}
	
	function minimize()
	{
		this.container.css("top", "calc(100% - 3.5rem)") ;
		output_expand.toggleClass("d-none") ;
		output_minimize.toggleClass("d-none") ;
	}
	
	function expand()
	{
		this.container.css("top", "50%") ;
		output_expand.toggleClass("d-none") ;
		output_minimize.toggleClass("d-none") ;
	}
}

function Nodes(map)
{
	//member variables
	this.map = map ;
	this.nodes = new Array() ;
	this.length = 0 ;
	this.polynode = null ;
	this.polyuser = new Array() ;
	
	//functions
	this.add = add ;
	this.clear = clear ;

	function add(node)
	{
		this.nodes.push(node) ;
		node.draw(this.map) ;
		this.length++ ;
	}
	
	function clear()
	{
		var x = this.nodes.length ;
		for(var i = 0; i < x; i++)
		{
			var temp = this.nodes.pop();
			temp.clear();
			temp = null;
		}
		this.length = 0 ;
	}
}

function Node(place, latitude, longitude)
{
	//member variables
	this.place = place ;
	this.latitude = latitude ;
	this.longitude = longitude ;
	this.marker ;
	this.polygons = new Array() ;
	
	//functions
	this.clear = clear ;
	this.draw = draw ;
	this.select = select ;
	this.highlight = highlight ;
	
	function clear()
	{
		this.marker.setMap(null);
		this.marker = null;
	}
	function draw(map)
	{
		this.marker = new google.maps.Marker({
			position: new google.maps.LatLng(this.latitude, this.longitude),
			icon: "./img/icons8-raspberry-pi-16.png",
			map: map
		});
		/*
		GEvent.addListener(this.marker, "mouseover", 
			function()
			{
				$("#tooltip").hide() ;
				var offset = map.fromLatLngToContainerPixel(center) ;
				var offsetMenu = document.getElementById("map_menu").offsetWidth ;
				$("#tooltip")
					.css({ top:offset.y - 40, left:offset.x + offsetMenu + 55})
					.html(building)
					.show() ;
			}
		) ;
		
		GEvent.addListener(this.marker, "mouseout", 
			function()
			{
				$("#tooltip").hide() ;
			}
		) ;
		
		GEvent.addListener(this.marker, "click", 
			function()
			{
				(new Request("querynode.php", {"ap" : address}, $("#tabs-1"), false)).send() ;
			}
		) ;
		*/
	}
	function select(map)
	{
		var points = new Array() ;
		for(var i = 0; i < 360; i += 15)
		{
			points.push(new GLatLng(
				this.latitude + (.00045 * Math.sin(i * Math.PI / 180)),
				this.longitude + (.00045 * Math.cos(i * Math.PI / 180))
				)
			) ;
		}
		points.push(points[0]) ;
		var poly = new GPolygon(points, "#0080FF", 2, 1, "#0080FF", 0) ;
		map.addOverlay(poly) ;
		return poly ;
	}
	function highlight(map, severity)
	{
		var polys = new Array() ;
		for(var i = 0; i < severity; i++)
		{					
			var radius = (1 - i / severity) * .00045 ;
			var alpha = .15 + .1 * severity ;
			
			var points = new Array() ;
			for(var j = 0; j < 360; j += 15)
			{
				points.push(new GLatLng(
					this.latitude + (radius * Math.sin(j * Math.PI / 180)),
					this.longitude + (radius * Math.cos(j * Math.PI / 180))
					)
				) ;
			}
			points.push(points[0]) ;
			var poly = new GPolygon(points, "#FF0000", 1, 0, "#FF0000", alpha) ;
			map.addOverlay(poly) ;
			polys.push(poly) ;
		}
		return polys ;
	}	
}

function TestMenu(experiment, device, rssi)
{
	//member variables
	this.experiment = $("#" + experiment) ;
		var exp_mm_listen ;
		var exp_cl_listen ;
		var exp_marker ;
		var expActive = false;
		var expLoad = $("#expLoading").html() ;
		var expSub = $("#expSubmit");
	this.device = $("#" + device) ;
		var devActive = false;
		var devLoad = $("#devLoading").html() ;
		var devSub = $("#devSubmit");
	this.rssi = $("#" + rssi) ;
		var rssActive = false;
		var rssLoad = $("#rssLoading").html() ;
		var rssLoad2 = $("#rssLoading2").html() ;
		var rssSub = $("#rssSubmit");
	
	//initialization
		//Datepicker for Experiment
	$("#expDate").datepicker({
		format: "mm/dd/yyyy",
		container: $("body"),
		todayHighlight: true,
		autoclose: true,
	}) ;
	
		//Form Update on Select (Experiment)
	
	
		//Form Submit for Experiment
	this.experiment.on("submit", function(e) {
		e.preventDefault();
		var tmpLoading = $(this).find("#expLoading");
		var tmpScanner = tmpLoading.find("select");
		var tmpPlace = $(this).find("#expPlace");
		var tmpDate = $(this).find("#expDate");
		var tmpTime = $(this).find("#expTime");
		var tmpLat = $(this).find("#expLat");
		var tmpLng = $(this).find("#expLng");
		var formData = {
			"scanner": 	tmpScanner.val(),
			"place"	 :	tmpPlace.val(),
			"date" 	 :	tmpDate.val(),
			"time" 	 :	tmpTime.val(),
			"lat"	 :	tmpLat.val(),
			"lng"	 :	tmpLng.val()
		};
		
		var valid = true ;		
		if(!formData.scanner) {
			valid = false ;
			if(tmpScanner.length) {
				tmpScanner.addClass("is-invalid").removeClass("is-valid");
			} else {
				tmpLoading.find("span").removeClass("text-warning").addClass("text-danger") ;
			}
		} else {
			tmpScanner.addClass("is-valid").removeClass("is-invalid");
		}
		if(!formData.place) {
			valid = false ;
			tmpPlace.addClass("is-invalid").removeClass("is-valid");
		} else if(formData.place.length > 30) {
			valid = false ;
			tmpPlace.addClass("is-invalid").removeClass("is-valid");
		} else {
			tmpPlace.addClass("is-valid").removeClass("is-invalid");
		}
		var date_regex = /^(0[1-9]|1[0-2])\/(0[1-9]|1\d|2\d|3[01])\/(19|20)\d{2}$/ ;
		if(!formData.date) {
			valid = false ;
			tmpDate.addClass("is-invalid").removeClass("is-valid");
		} else if(!date_regex.test(formData.date)) {
			valid = false ;
			tmpDate.addClass("is-invalid").removeClass("is-valid");
		} else {
			tmpDate.addClass("is-valid").removeClass("is-invalid");
		}
		var time_regex = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/ ;
		if(!formData.time) {
			valid = false ;
			tmpTime.addClass("is-invalid").removeClass("is-valid");
		} else if(!time_regex.test(formData.time)) {
			valid = false ;
			tmpTime.addClass("is-invalid").removeClass("is-valid");
		} else {
			tmpTime.addClass("is-valid").removeClass("is-invalid");
		}
		if(!formData.lat) {
			valid = false ;
			tmpLat.addClass("is-invalid").removeClass("is-valid");
		} else if(formData.lat.length < 13) {
			valid = false ;
			tmpLat.addClass("is-invalid").removeClass("is-valid");
		} else {
			tmpLat.addClass("is-valid").removeClass("is-invalid");
		}
		if(!formData.lng) {
			valid = false ;
			tmpLng.addClass("is-invalid").removeClass("is-valid");
		} else if(formData.lat.length < 13) {
			valid = false ;
			tmpLng.addClass("is-invalid").removeClass("is-valid");
		} else {
			tmpLng.addClass("is-valid").removeClass("is-invalid");
		}
		if(!valid)
			return;
		
		$(this).find("input").attr("disabled", true);
		$(this).find("select").attr("disabled", true);
		expSub.addClass("btn-secondary").removeClass("btn-primary")
			.html("<span class=\"spinner-grow spinner-grow-sm\" role=\"status\"></span><span>Sending Data...</span>");
		output.push() ;
		output.add("Experiment info submitted.") ;
		$.ajax({
			url : $(this).attr("action"),
			type: "POST",
			data: formData,
			dataType: "script",
			error: function (jXHR, textStatus, errorThrown) {
				output.add("Ajax error: " + errorThrown + ".");
				output.timestamp();
				expSub.addClass("btn-warning").removeClass("btn-secondary").html("<b>Ajax Error :o</b>");
				resetExp();
			}
		});
	});
	
		//Form Submit for Device
	this.device.on("submit", function(e) {
		e.preventDefault();
		var tmpLoading = $(this).find("#devLoading");
		var tmpExperiment = tmpLoading.find("select");
		var tmpMac = $(this).find("#devMac");
		var tmpDate = $(this).find("#devDate");
		var tmpTime = $(this).find("#devTime");
		var formData = {
			"experiment" : 	tmpExperiment.val(),
			"mac"		 :	tmpMac.val().toUpperCase(),
			"date"		 :	tmpDate.val(),
			"time"		 :	tmpTime.val()
		};
		
		var valid = true ;		
		if(!formData.experiment) {
			valid = false ;
			if(tmpExperiment.length) {
				tmpExperiment.addClass("is-invalid").removeClass("is-valid");
			} else {
				tmpLoading.find("span").removeClass("text-warning").addClass("text-danger") ;
			}
		} else {
			tmpExperiment.addClass("is-valid").removeClass("is-invalid");
		}
		var mac_regex = /^([0-9]|[abcdef])([0-9]|[abcdef]):([0-9]|[abcdef])([0-9]|[abcdef]):([0-9]|[abcdef])([0-9]|[abcdef]):([0-9]|[abcdef])([0-9]|[abcdef]):([0-9]|[abcdef])([0-9]|[abcdef]):([0-9]|[abcdef])([0-9]|[abcdef])/i ;
		if(!formData.mac) {
			valid = false ;
			tmpMac.addClass("is-invalid").removeClass("is-valid");
		} else if(!mac_regex.test(formData.mac)) {
			valid = false ;
			tmpMac.addClass("is-invalid").removeClass("is-valid");
		} else {
			tmpMac.addClass("is-valid").removeClass("is-invalid");
			tmpMac.val(formData.mac);
		}
		var date_regex = /^(0[1-9]|1[0-2])\/(0[1-9]|1\d|2\d|3[01])\/(19|20)\d{2}$/ ;
		if(!formData.date) {
			valid = false ;
			tmpDate.addClass("is-invalid").removeClass("is-valid");
		} else if(!date_regex.test(formData.date)) {
			valid = false ;
			tmpDate.addClass("is-invalid").removeClass("is-valid");
		} else {
			tmpDate.addClass("is-valid").removeClass("is-invalid");
		}
		var time_regex = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/ ;
		if(!formData.time) {
			valid = false ;
			tmpTime.addClass("is-invalid").removeClass("is-valid");
		} else if(!time_regex.test(formData.time)) {
			valid = false ;
			tmpTime.addClass("is-invalid").removeClass("is-valid");
		} else {
			tmpTime.addClass("is-valid").removeClass("is-invalid");
		}
		if(!valid)
			return;
		
		$(this).find("input").attr("disabled", true);
		$(this).find("select").attr("disabled", true);
		devSub.addClass("btn-secondary").removeClass("btn-primary")
			.html("<span class=\"spinner-grow spinner-grow-sm\" role=\"status\"></span><span>Sending Data...</span>");
		output.push() ;
		output.add("Device info submitted.") ;
		$.ajax({
			url : $(this).attr("action"),
			type: "POST",
			data: formData,
			dataType: "script",
			error: function (jXHR, textStatus, errorThrown) {
				output.add("Ajax error: " + errorThrown + ".");
				output.timestamp();
				devSub.addClass("btn-warning").removeClass("btn-secondary").html("<b>Ajax Error :o</b>");
				resetDev();
			}
		});
	});
	
		//Form Submit for RSSI
	this.rssi.on("submit", function(e) {
		e.preventDefault();
		var tmpLoading = $(this).find("#rssLoading");
		var tmpExperiment = tmpLoading.find("select");
		var tmpLoading2 = $(this).find("#rssLoading2");
		var tmpDevice = tmpLoading2.find("select");
		var tmpRssi = $(this).find("#rssRssi");
		var tmpDate = $(this).find("#rssDate");
		var tmpTime = $(this).find("#rssTime");
		var formData = {
			"device"	: 	tmpDevice.val(),
			"rssi"		:	tmpRssi.val(),
			"date"		:	tmpDate.val(),
			"time"		:	tmpTime.val()
		};
		
		var valid = true ;		
		if(!formData.device) {
			valid = false ;
			if(tmpDevice.length) {
				tmpDevice.addClass("is-invalid").removeClass("is-valid");
				tmpExperiment.addClass("is-invalid").removeClass("is-valid");
			} else {
				tmpLoading2.find("span").removeClass("text-warning").addClass("text-danger") ;
			}
		} else {
			tmpDevice.addClass("is-valid").removeClass("is-invalid");
			tmpExperiment.addClass("is-valid").removeClass("is-invalid");
		}
		if(!formData.rssi) {
			valid = false ;
			tmpRssi.addClass("is-invalid").removeClass("is-valid");
		} else if(formData.rssi.length > 5) {
			valid = false ;
			tmpRssi.addClass("is-invalid").removeClass("is-valid");
		} else {
			tmpRssi.addClass("is-valid").removeClass("is-invalid");
		}
		var date_regex = /^(0[1-9]|1[0-2])\/(0[1-9]|1\d|2\d|3[01])\/(19|20)\d{2}$/ ;
		if(!formData.date) {
			valid = false ;
			tmpDate.addClass("is-invalid").removeClass("is-valid");
		} else if(!date_regex.test(formData.date)) {
			valid = false ;
			tmpDate.addClass("is-invalid").removeClass("is-valid");
		} else {
			tmpDate.addClass("is-valid").removeClass("is-invalid");
		}
		var time_regex = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/ ;
		if(!formData.time) {
			valid = false ;
			tmpTime.addClass("is-invalid").removeClass("is-valid");
		} else if(!time_regex.test(formData.time)) {
			valid = false ;
			tmpTime.addClass("is-invalid").removeClass("is-valid");
		} else {
			tmpTime.addClass("is-valid").removeClass("is-invalid");
		}
		if(!valid)
			return;
		
		$(this).find("input").attr("disabled", true);
		$(this).find("select").attr("disabled", true);
		rssSub.addClass("btn-secondary").removeClass("btn-primary")
			.html("<span class=\"spinner-grow spinner-grow-sm\" role=\"status\"></span><span>Sending Data...</span>");
		output.push() ;
		output.add("RSSI info submitted.") ;
		$.ajax({
			url : $(this).attr("action"),
			type: "POST",
			data: formData,
			dataType: "script",
			error: function (jXHR, textStatus, errorThrown) {
				output.add("Ajax error: " + errorThrown + ".");
				output.timestamp();
				rssSub.addClass("btn-warning").removeClass("btn-secondary").html("<b>Ajax Error :o</b>");
				resetRss();
			}
		});
	});
	
		//Buttons to open Form Collapse for each entry form
	$("#btn-collapseOne").click( function() {
		if(!expActive)
			openExp() ;
		else
			closeExp() ;
	});
	$("#btn-collapseTwo").click( function() {
		if(!devActive)
			openDev() ;
		else
			closeDev() ;
	});
	$("#btn-collapseThree").click( function() {
		if(!rssActive)
			openRss() ;
		else
			closeRss() ;
	});
	
	//functions
	this.openExp = openExp ;
	this.closeExp = closeExp ;
	this.resetExp = resetExp ;
	this.openDev = openDev ;
	this.closeDev = closeDev ;
	this.resetDev = resetDev ;
	this.openRss = openRss ;
	this.closeRss = closeRss ;
	this.resetRss = resetRss ;
	
	function openExp() {
		var tmpLat = $("#expLat") ;
		var tmpLng = $("#expLng") ;
		expActive = true ;
		if(devActive)
			closeDev() ;
		if(rssActive)
			closeRss() ;
		exp_mm_listen = google.maps.event.addListener(map, "mousemove", function(event) {
			tmpLat.val(event.latLng.lat().toFixed(10)) ; 
			tmpLng.val(event.latLng.lng().toFixed(10)) ;
		}) ;
		exp_cl_listen = google.maps.event.addListener(map, "click", function(event) {
			if(!exp_mm_listen)
			{
				exp_mm_listen = google.maps.event.addListener(map, "mousemove", function(event) {
					tmpLat.val(event.latLng.lat().toFixed(10)) ; 
					tmpLng.val(event.latLng.lng().toFixed(10)) ;
				}) ;
				exp_marker.setMap(null) ;
				exp_marker = null ;
				tmpLat.val(event.latLng.lat().toFixed(10)) ; 
				tmpLng.val(event.latLng.lng().toFixed(10)) ;
			} else {
				google.maps.event.clearListeners(map, "mousemove") ;
				exp_mm_listen = null ;
				exp_marker = new google.maps.Marker({
					position:	event.latLng,
					icon:		"./img/baseline_where_to_vote_black_36dp.png",
					draggable:	false,
					map:		map
				}) ;
				tmpLat.val(event.latLng.lat().toFixed(10)) ; 
				tmpLng.val(event.latLng.lng().toFixed(10)) ;
			}
		}) ;
		output.push() ;
		output.add("Experiment test - loading Scanner entries.") ;
		
		var formData = {
			"table" :	"scanner"
		} ;
		
		$.ajax({
			url : "./php/test_get_parent.php",
			type: "POST",
			data: formData,
			dataType: "html",
			success: function(data) {
				$("#expLoading").html(data);
			},
			error: function (jXHR, textStatus, errorThrown) {
				alert(errorThrown);
			}
		});
	}
	function closeExp() {
		expActive = false ;
		google.maps.event.clearListeners(map, "mousemove") ;
		google.maps.event.clearListeners(map, "click") ;
		if(exp_marker)
			exp_marker.setMap(null) ;
		exp_mm_listen = null ;
		exp_cl_listen = null ;
		exp_marker = null ;
		output.push() ;
		output.add("Experiment test closed.") ;
		output.timestamp() ;
		resetExp() ;
		setTimeout(function() { $("#expLoading").html(expLoad); }, 250);
	}
	function resetExp() {
		setTimeout(function() { 
			$("#expTest").find("input").attr("disabled", false).removeClass("is-valid is-invalid");
			$("#expTest").find("select").attr("disabled", false).removeClass("is-valid is-invalid");
			$("#expSubmit").addClass("btn-primary").removeClass("btn-secondary btn-success btn-warning btn-danger").html("Submit Entry");
		}, 1500);
	}
	function openDev() {
		devActive = true ;
		if(expActive)
			closeExp() ;
		if(rssActive)
			closeRss() ;
		output.push() ;
		output.add("Dev - loading experiment data.") ;
		
		var formData = {
			"table" :	"experiment"
		} ;
		
		$.ajax({
			url : "./php/test_get_parent.php",
			type: "POST",
			data: formData,
			dataType: "html",
			success: function(data) {
				$("#devLoading").html(data);
				$("#devLoading").find("select").on("change", function(e) {
					output.push() ;
					output.add("Dev - loading experiment date.") ;
					
					var formData = {
						"table" :	"experiment",
						"id"	:	$(this).val()
					} ;
					
					$.ajax({
						url : "./php/test_get_parent.php",
						type: "POST",
						data: formData,
						dataType: "html",
						success: function(data) {
							$("#devLoading").append(data);
						},
						error: function (jXHR, textStatus, errorThrown) {
							alert(errorThrown);
						}
					});
				});
			},
			error: function (jXHR, textStatus, errorThrown) {
				alert(errorThrown);
			}
		});
	}
	function closeDev() {
		devActive = false ;
		output.push() ;
		output.add("Device test closed.") ;
		output.timestamp() ;
		resetDev() ;
		setTimeout(function() { $("#devLoading").html(devLoad); }, 250);
	}
	function resetDev() {
		setTimeout(function() { 
			$("#devTest").find("input").attr("disabled", false).removeClass("is-valid is-invalid");
			$("#devTest").find("select").attr("disabled", false).removeClass("is-valid is-invalid");
			$("#devSubmit").addClass("btn-primary").removeClass("btn-secondary btn-success btn-warning btn-danger").html("Submit Entry");
		}, 1500);
	}
	function openRss() {
		rssActive = true ;
		if(expActive)
			closeExp() ;
		if(devActive)
			closeDev() ;
		output.push() ;
		output.add("Rss - loading Experiment entries.") ;
		
		var formData = {
			"table" :	"experiment"
		} ;
		
		$.ajax({
			url : "./php/test_get_parent.php",
			type: "POST",
			data: formData,
			dataType: "html",
			success: function(data) {
				var temp = $("#rssLoading");
				temp.html(data);
				$("#rssLoading2").find("span").addClass("text-warning").removeClass("text-danger") ;
				temp.find("select").on("change", function(e) {
					output.push() ;
					output.add("Rss - loading device data.") ;
					
					var formData = {
						"table" :	"device",
						"id"	:	$(this).val()
					} ;
					
					$.ajax({
						url : "./php/test_get_parent.php",
						type: "POST",
						data: formData,
						dataType: "html",
						success: function(data) {
							$("#rssLoading2").html(data);
							$("#rssLoading2").find("select").on("change", function(e) {
								output.push() ;
								output.add("Rss - loading device date.") ;
								
								var formData2 = {
									"table" :	"device",
									"pick"	:	"asdf",
									"id"	:	$(this).val()
								} ;
								
								$.ajax({
									url : "./php/test_get_parent.php",
									type: "POST",
									data: formData2,
									dataType: "html",
									success: function(data) {
										$("#rssLoading2").append(data);
									},
									error: function (jXHR, textStatus, errorThrown) {
										alert(errorThrown);
									}
								});
							});
						},
						error: function (jXHR, textStatus, errorThrown) {
							alert(errorThrown);
						}
					});
				});
			},
			error: function (jXHR, textStatus, errorThrown) {
				alert(errorThrown);
			}
		});
	}
	function closeRss() {
		rssActive = false ;
		output.push() ;
		output.add("RSSI test closed.") ;
		output.timestamp() ;
		setTimeout(function() { $("#rssLoading").html(rssLoad); }, 250);
		setTimeout(function() { $("#rssLoading2").html(rssLoad2); }, 250);
	}
	function resetRss() {
		setTimeout(function() { 
			$("#rssTest").find("input").attr("disabled", false).removeClass("is-valid is-invalid");
			$("#rssTest").find("select").attr("disabled", false).removeClass("is-valid is-invalid");
			$("#rssSubmit").addClass("btn-primary").removeClass("btn-secondary btn-success btn-warning btn-danger").html("Submit Entry");
		}, 1500);
	}
}