var centerLat = 29.648612, centerLng = -82.343504;
var map, output, testmenu, nodes, devices, control;
	
function initialize() {
	output = new Output("output", "out-exp", "out-min", "out-rem");
	
	let style = [{"elementType": "geometry",
		"stylers": [{"color": "#1d2c4d"}]},
		{"elementType": "labels.text.fill",
		"stylers": [{"color": "#8ec3b9"}]},
		{"elementType": "labels.text.stroke",
		"stylers": [{"color": "#1a3646"}]},
		{"featureType": "administrative.country",
		"elementType": "geometry.stroke",
		"stylers": [{"color": "#4b6878"}]},
		{"featureType": "administrative.land_parcel",
		"elementType": "labels.text.fill",
		"stylers": [{"color": "#64779e"}]},
		{"featureType": "administrative.province",
		"elementType": "geometry.stroke",
		"stylers": [{"color": "#4b6878"}]},
		{"featureType": "landscape.man_made",
		"elementType": "geometry.stroke",
		"stylers": [{"color": "#334e87"}]},
		{"featureType": "landscape.natural",
		"elementType": "geometry",
		"stylers": [{"color": "#023e58"}]},
		{"featureType": "poi",
		"elementType": "geometry",
		"stylers": [{"color": "#283d6a"}]},
		{"featureType": "poi",
		"elementType": "labels.text.fill",
		"stylers": [{"color": "#6f9ba5"}]},
		{"featureType": "poi",
		"elementType": "labels.text.stroke",
		"stylers": [{"color": "#1d2c4d"}]},
		{"featureType": "poi.park",
		"elementType": "geometry.fill",
		"stylers": [{"color": "#023e58"}]},
		{"featureType": "poi.park",
		"elementType": "labels.text.fill",
		"stylers": [{"color": "#3C7680"}]},
		{"featureType": "road",
		"elementType": "geometry",
		"stylers": [{"color": "#304a7d"}]},
		{"featureType": "road",
		"elementType": "labels.text.fill",
		"stylers": [{"color": "#98a5be"}]},
		{"featureType": "road",
		"elementType": "labels.text.stroke",
		"stylers": [{"color": "#1d2c4d"}]},
		{"featureType": "road.highway",
		"elementType": "geometry",
		"stylers": [{"color": "#2c6675"}]},
		{"featureType": "road.highway",
		"elementType": "geometry.stroke",
		"stylers": [{"color": "#255763"}]},
		{"featureType": "road.highway",
		"elementType": "labels.text.fill",
		"stylers": [{"color": "#b0d5ce"}]},
		{"featureType": "road.highway",
		"elementType": "labels.text.stroke",
		"stylers": [{"color": "#023e58"}]},
		{"featureType": "transit",
		"stylers": [{"visibility": "off"}]},
		{"featureType": "transit",
		"elementType": "labels.text.fill",
		"stylers": [{"color": "#98a5be"}]},
		{"featureType": "transit",
		"elementType": "labels.text.stroke",
		"stylers": [{"color": "#1d2c4d"}]},
		{"featureType": "transit.line",
		"elementType": "geometry.fill",
		"stylers": [{"color": "#283d6a"}]},
		{"featureType": "transit.station",
		"elementType": "geometry",
		"stylers": [{"color": "#3a4762"}]},
		{"featureType": "water",
		"elementType": "geometry",
		"stylers": [{"color": "#0e1626"}]},
		{"featureType": "water",
		"elementType": "labels.text.fill",
		"stylers": [{"color": "#4e6d70"}]},
		{"featureType": "poi",
		"elementType": "labels.icon",
		"stylers": [{"visibility": "off"}]}];
		
	map = new google.maps.Map(document.getElementById("map"), {
	  center: {lat: centerLat, lng: centerLng},
	  draggableCursor: "pointer",
	  clickableIcons: false,
	  mapTypeControl: false,
	  streetViewControl: false,
	  styles: style,
	  zoom: 18
	});
	output.add("Google Maps API loaded and ready.");
	
	testmenu = new TestMenu("expTest", "devTest", "rssTest");
	nodes = {};
	devices = {};
	control = new ControlMenu("showExp", "showDev", "showRss", "showRed");
	cpanel = new ContentPanel();
	setTimeout(function() {cpanel.select("rssi");}, 1500);
	setTimeout(function() {cpanel.select("device");}, 1600);
	setTimeout(function() {cpanel.select("experiment");}, 1700);
	setTimeout(function() {cpanel.select("device");}, 1800);
	setTimeout(function() {cpanel.select("rssi");}, 1900);
	setTimeout(function() {cpanel.select("admin");}, 2000);
	setTimeout(function() {output.add("Content panel ready.");output.timestamp();}, 2100);
}

class MapTooltip extends google.maps.OverlayView {
	constructor(position, place, tooltip) {
		super();
		this.position = position;
		this.place = place;
		this.tooltip = $("#"+tooltip).clone().removeAttr("id").removeClass("d-none");
	}
	onAdd() {
		this.getPanes().markerLayer.appendChild(this.tooltip[0]);
	}
	draw() {
		let marker = this.getProjection().fromLatLngToDivPixel(this.position);
		this.tooltip.find(".tooltip-inner").html(this.place);
		this.tooltip.css({ top:marker.y - this.tooltip.height() - 20, left:marker.x - (this.tooltip.width() / 2) }).addClass("show");
	}
	onRemove() {
		this.tooltip.removeClass("show");
		this.tooltip.remove();
	}
}

class Output {
	constructor(container, expand, minimize, remove) {
		this.cont = $("#" + container);
		this.exp = $("#" + expand);
		this.min = $("#" + minimize);
		this.rem = $("#" + remove);
		this.recent = "";
		
		EventFactory.hoverOutput(this.exp);
		EventFactory.hoverOutput(this.min);
		EventFactory.hoverOutput(this.rem);
		this.exp.click(this.expand);
		this.min.click(this.minimize);
		this.rem.click(this.remove);
	}
	add(msg) {
		let html = "<span class=\"rainbow\">" + msg + "</span>";
		this.recent += html;
		let parse = $.parseHTML(html);
		$(parse).html(function(i, html) {
			let chars = $.trim(html).split("");
			let word = "<span>";
			for(i = 0; i < chars.length; i++)
				word += chars[i] + "</span><span style=\"animation-delay: " +.02*i+ "s\">";
			word += "</span>";
			return word;
		});
		$("#out-new p.text-left").append(parse);
	}
	push() {
		$("#out-new p.text-left").empty();
		$("#out-old p.text-success").prepend("<p style=\"display: block\">" + this.recent + "</p>");
		this.recent = "";
	}
	timestamp()	{
		let time = new Date();
		let hr = time.getHours();
		let min = time.getMinutes();
		let sec = time.getSeconds();
		if(hr < 10)
			hr = "0" + hr;
		if(min < 10)
			min = "0" + min;
		if(sec < 10)
			sec = "0" + sec;
		let str = "<span class=\"text-light timestamp\">&nbsp&nbsp-" + hr + ":" + min + ":" + sec + "</span>";
		$("#out-new p.text-left").append(str);
		this.recent += str;
	}
	remove() {
		$("#output").addClass("d-none");
	}
	minimize() {
		$("#output").css("top", "calc(100% - 3.5rem)");
		$("#out-exp").toggleClass("d-none");
		$("#out-min").toggleClass("d-none");
	}
	expand() {
		$("#output").css("top", "50%");
		$("#out-exp").toggleClass("d-none");
		$("#out-min").toggleClass("d-none");
	}
}

class Nodes {
	constructor() {
		this.nodes = new Array();
		this.length = 0;
	}
	add(node) {
		this.nodes.push(node);
		this.length++;
	}
	clear() {
		let x = this.nodes.length;
		for(var i = 0; i < x; i++)
			this.nodes.pop().clear();
		this.length = 0;
	}
	get(index) {
		if(this.length == 0 || index < 0 || index >= this.nodes.length)
			return null;
		else
			return this.nodes[index];
	}
}

class Node {
	constructor(map, id, place, latitude, longitude, params={}) {
		this.map = map;
		this.id = id;
		this.place = place;
		this.latitude = latitude;
		this.longitude = longitude;
		this.params = params;
		this.listener = null;
		let icon = {
			url: "./img/icons8-raspberry-pi-16.png",
			size: new google.maps.Size(16, 16),
			origin: new google.maps.Point(0, 0),
			anchor: new google.maps.Point(8, 7)
		};
		this.marker = new google.maps.Marker({
			position: new google.maps.LatLng(this.latitude, this.longitude),
			icon: icon
		});
		this.tooltip = new MapTooltip(this.marker.position, this.place, "tooltip");
		this.coverage = new google.maps.Circle({
					strokeColor: "#fbfbfb",
					strokeOpacity: 0.9,
					strokeWeight: 1,
					fillColor: "#fbfbfb",
					fillOpacity: 0.2,
					center: this.marker.position,
					radius: 10
		});
		let tool = this.tooltip;
		this.marker.addListener("mouseover", function() {
			tool.setMap(map);
		});
		this.marker.addListener("mouseout", function() {
			tool.setMap(null);
		});
	}
	
	clear() {
		this.marker.setMap(null);
		this.tooltip.setMap(null);
		this.coverage.setMap(null);
	}
	draw(panable=true) {
		let m = this.marker;
		let c = this.coverage;
		if(!m.getMap()) {
			map.setZoom(18);
			c.setOptions({strokeOpacity: 0.8, fillOpacity: 0.3});
			if(panable) 
				setTimeout(function(){map.panTo(m.position);}, 500);
			setTimeout(function(){m.setMap(map);c.setMap(map);}, 1000);
		} else {
			setTimeout(function(){map.panTo(m.position);}, 100);
			setTimeout(function(){map.setZoom(20);}, 600);
			setTimeout(function(){c.setOptions({strokeOpacity: 0.7});}, 1000);
			setTimeout(function(){c.setOptions({strokeOpacity: 0.6});}, 1050);
			setTimeout(function(){c.setOptions({strokeOpacity: 0.5, fillOpacity: 0.2});}, 1100);
			setTimeout(function(){c.setOptions({strokeOpacity: 0.4});}, 1150);
			setTimeout(function(){c.setOptions({strokeOpacity: 0.3, fillOpacity: 0.1});}, 1200);
			setTimeout(function(){c.setOptions({strokeOpacity: 0.2});}, 1250);
			setTimeout(function(){c.setOptions({strokeOpacity: 0.1, fillOpacity: 0});}, 1300);
			setTimeout(function(){m.setMap(null);c.setMap(null);}, 1350);
		}
	}	
}

function TestMenu(experiment, device, rssi) {
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
		autoclose: true
	}) ;
	
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
		var tmpAuth = $("#passkey");
		var formData = {
			"auth"   :  tmpAuth.val(),
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
		var tmpAuth = $("#passkey");
		var formData = {
			"auth"       :  tmpAuth.val(),
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
		var tmpAuth = $("#passkey");
		var formData = {
			"auth"  :  tmpAuth.val(),
			"device":  tmpDevice.val(),
			"rssi"	:  tmpRssi.val(),
			"date"	:  tmpDate.val(),
			"time"	:  tmpTime.val()
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
				output.add("Unknown error");
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
		output.add("Device Test - loading Experiment data.") ;
		
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
					output.add("Device Test - loading Experiment date.") ;
					
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
							output.add("Unknown error");
						}
					});
				});
			},
			error: function (jXHR, textStatus, errorThrown) {
				output.add("Unknown error");
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
		output.add("RSSI Test - loading Experiment entries.") ;
		
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
					output.add("RSSI Test - loading Device data.") ;
					
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
								output.add("RSSI Test - loading Device date.") ;
								
								var formData2 = {
									"table" :	"device",
									"pick"	:	1,
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
										output.add("Unknown error");
									}
								});
							});
						},
						error: function (jXHR, textStatus, errorThrown) {
							output.add("Unknown error");
						}
					});
				});
			},
			error: function (jXHR, textStatus, errorThrown) {
				output.add("Unknown error");
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

class ControlMenu {
	constructor(experiment, device, rssi, restart) {
		this._exp = $("#" + experiment);
		this._dev = $("#" + device);
		this._rss = $("#" + rssi);
		this._red = $("#" + restart);
		
		EventFactory.hoverControl(this._exp);
		EventFactory.hoverControl(this._dev);
		EventFactory.hoverControl(this._rss);
		EventFactory.hoverRed(this._red);
		EventFactory.clickControl(this._exp, $("#collapseExp"));
		EventFactory.clickControl(this._dev, $("#collapseDev"));
		EventFactory.clickControl(this._rss, $("#collapseRss"));
		EventFactory.clickRed(this._red);
							
		this._exp.click(function(e) {
			if($(this).find("span").length < 1) {
				output.push();
				output.add("Loading Scan Locations:");
				var formData = { "all" : 1 };
				$.ajax({
					url : "./php/exp_test.php",
					type: "POST",
					data: formData,
					dataType: "html",
					success: function (data) {
						$("#collapseExp div").html(data);
					},
					error: function (jXHR, textStatus, errorThrown) {
						output.add("Unknown error");
					}
				});
			}
		});
	}
}

class ContentPanel {
	constructor() {
		this.links = $("#infoTabs li a");
		this.panels = $("#infoDivs .tab-pane");
	}
	
	select(tab) {
		this.links.removeClass("active");
		this.panels.removeClass("show active");
		if(tab == "experiment") {
			$("#expTab").addClass("active");
			$("#experiment").addClass("show active");
		}
		if(tab == "device") {
			$("#devTab").addClass("active");
			$("#device").addClass("show active");
		}
		if(tab == "rssi") {
			$("#rssTab").addClass("active");
			$("#rssi").addClass("show active");
		}
		if(tab == "admin") {
			$("#admTab").addClass("active");
			$("#admin").addClass("show active");
		}
	}
	
	add(tab, params={}) {
		if(tab == "experiment") {
			let html = "<li class=\"list-group-item\" style=\"padding: 0 !important;\"><div class=\"card bg-dark\">"+
						"<div class=\"card-body\" style=\"padding: 0.5rem\">"+
						"<h5 class=\"card-title bg-light text-dark\" style=\"margin-bottom: 0.5rem; padding: 0.25rem; border-radius: 0.25rem\">" +(params.name ? params.name : "Missing Title")+ "</h5>"+
						"<p class=\"card-text text-left text-light\" style=\"margin-bottom: 0; font-weight: bold\">Total devices: </p>"+
						"<p class=\"card-text text-right text-light\" style=\"margin-bottom: 0.25rem\">" +(params.devs ? params.devs : "N/A")+ "</p>"+
						"<p class=\"card-text text-left text-light\" style=\"margin-bottom: 0; font-weight: bold\">Total samples: </p>"+
						"<p class=\"card-text text-right text-light\" style=\"margin-bottom: 0.25rem\">" +(params.samps ? params.samps : "N/A")+ "</p>"+
						"<p class=\"card-text text-left text-light\" style=\"margin-bottom: 0; font-weight: bold\">Total time: </p>"+
						"<p class=\"card-text text-right text-light\" style=\"margin-bottom: 0.25rem\">" +(params.time ? params.time : "N/A")+ "</p>"+
						"<p class=\"card-text text-left text-light\" style=\"margin-bottom: 0; font-weight: bold\">Avg. Samples per Second: </p>"+
						"<p class=\"card-text text-right text-light\" style=\"margin-bottom: 0.25rem\">" +(params.sps ? params.sps : "N/A")+ "</p>"+
						"<p class=\"card-text text-left text-light\" style=\"margin-bottom: 0; font-weight: bold\">RSSI Range: </p>"+
						"<p class=\"card-text text-right text-light\" style=\"margin-bottom: 0.25rem\">" +((params.low && params.high) ? (params.low+" to "+params.high) : "N/A")+ "</p>"+
						"<p class=\"card-text text-left text-light\" style=\"margin-bottom: 0; font-weight: bold\">RSSI Mean / Median: </p>"+
						"<p class=\"card-text text-right text-light\" style=\"margin-bottom: 0.25rem\">" +((params.mean && params.median) ? (params.mean+" / "+params.median) : "N/A")+ "</p>"+
						"</div></div></li>";
			let parse = $.parseHTML(html);
			$("#experiment ul").prepend(parse);
		}
		if(tab == "device") {
			let html = "<li class=\"list-group-item\" style=\"padding: 0 !important;\"><div class=\"card bg-dark\">"+
						"<div class=\"card-body\" style=\"padding: 0.5rem\">"+
						"<h5 class=\"card-title bg-light text-dark\" style=\"margin-bottom: 0.5rem; padding: 0.25rem; border-radius: 0.25rem\">" +(params.mac ? params.mac : "Missing MAC")+ "</h5>"+
						"<p class=\"card-text text-left text-light\" style=\"margin-bottom: 0; font-weight: bold\">Total samples: </p>"+
						"<p class=\"card-text text-right text-light\" style=\"margin-bottom: 0.25rem\">" +(params.samps ? params.samps : "N/A")+ "</p>"+
						"<p class=\"card-text text-left text-light\" style=\"margin-bottom: 0; font-weight: bold\">Total time: </p>"+
						"<p class=\"card-text text-right text-light\" style=\"margin-bottom: 0.25rem\">" +(params.time ? params.time : "N/A")+ "</p>"+
						"<p class=\"card-text text-left text-light\" style=\"margin-bottom: 0; font-weight: bold\">Avg. Samples per Minute: </p>"+
						"<p class=\"card-text text-right text-light\" style=\"margin-bottom: 0.25rem\">" +(params.spm ? params.spm : "N/A")+ "</p>"+
						"<p class=\"card-text text-left text-light\" style=\"margin-bottom: 0; font-weight: bold\">RSSI Range: </p>"+
						"<p class=\"card-text text-right text-light\" style=\"margin-bottom: 0.25rem\">" +((params.low && params.high) ? (params.low+" to "+params.high) : "N/A")+ "</p>"+
						"<p class=\"card-text text-left text-light\" style=\"margin-bottom: 0; font-weight: bold\">RSSI Mean / Median: </p>"+
						"<p class=\"card-text text-right text-light\" style=\"margin-bottom: 0.25rem\">" +((params.mean && params.median) ? (params.mean+" / "+params.median) : "N/A")+ "</p>"+
						"</div></div></li>";
			let parse = $.parseHTML(html);
			$("#device ul").prepend(parse);
		}
		if(tab == "rssi") {
		}
	}
}