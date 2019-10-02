var map, centerLat = 29.648612, centerLng = -82.343504 ;
var http ;
var output ;
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
		output = new Output("#output") ;
		$("#output-expand").click(function() {output.expand();}) ;
		$("#output-minimize").click(function() {output.minimize();}) ;
		$("#output-remove").click(function() {output.remove();}) ;
		
		map = new google.maps.Map(document.getElementById("map"), {
          center: {lat: centerLat, lng: centerLng},
		  clickableIcons: false,
          zoom: 18
        });
		google.maps.event.clearListeners(map, "dblclick") ;
		output.add("Google Maps API loaded and ready") ;
		output.timestamp() ;
		
		nodes = new Nodes(map) ;
	}
	
	function Output(containerID)
	{
		//member variables
		this.container ;
		this.recent ;
		this.content = new Array() ;
		
		//functions
		this.add = add ;
		this.push = push ;
		this.timestamp = timestamp ;
		this.remove = remove ;
		this.minimize = minimize ;
		this.expand = expand ;
		
		//initialization
		this.container = $(containerID) ;
		var output_expand = this.container.find("#output-expand");
		var output_minimize = this.container.find("#output-minimize");
		var output_remove = this.container.find("#output-remove");
		
		function add(text)
		{
			this.recent = "<span class=\"rainbow\">" + text + "</span>" ;
			var parse = $.parseHTML(this.recent) ;
			$(parse).html(function(i, html) {
			//$("#p-recent").append($.parseHTML(this.recent)).find("span").addClass("rainbow").html(function(i, html) {
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
			var str = "<span class=\"text-light timestamp\">&nbsp&nbsp- " + time.getHours() + ":" + time.getMinutes() + ":" + time.getSeconds() + "</span>";
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
		/*
		this.find = find ;
		this.select = select ;
		this.highlight = highlight ;
		this.cluster = cluster ;
		this.clearUser = clearUser ;
		this.clearCluster = clearCluster ;
		*/
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
				this.map.removeOverlay(this.nodes.pop().marker) ;
			this.length = 0 ;
		}
		/*
		function find(address)
		{
			for(var i = 0; i < this.nodes.length; i++)
			{
				if(this.nodes[i].address == address)
					return this.nodes[i];
			}
			return null;
		}
		
		function cluster(rMargin, cMargin)
		{
			for(var i = 0; i < this.nodes.length; i++)
				this.nodes[i].cluster(this.map, rMargin, cMargin) ;
		}
		
		function select(address)
		{
			if(this.polynode)
				this.map.removeOverlay(this.polynode) ;
			var node = this.find(address) ;
			if(node)
				this.polynode = node.select(this.map) ;
		}
		
		function highlight(address, severity)
		{
			var node = this.find(address) ;
			var polygons = node.highlight(this.map, severity) ;
			if(node)
				for(var i = 0; i < polygons.length; i++)
					this.polyuser.push(polygons[i]) ;
		}
		
		function clearUser() 
		{
			var x = this.polyuser.length ;
			for(var i = 0; i < x; i++)
				this.map.removeOverlay(this.polyuser.pop()) ;
		}
		
		function clearCluster()
		{
			var x = this.nodes.length ;
			for(var i = 0; i < x; i++)
				this.nodes[i].clear(this.map) ;
		}
		*/
	}
	
	function Node(place, latitude, longitude)
	{
		//member variables
		this.place = place ;
		this.latitude = latitude ;
		this.longitude = longitude ;
		this.marker ;
		//this.clusters = new Array() ;
		this.polygons = new Array() ;
		
		//functions
		this.clear = clear ;
		this.draw = draw ;
		this.select = select ;
		this.highlight = highlight ;
		/*
		this.add = add ;
		this.cluster = cluster ;
		*/
		
		function clear(map)
		{
			map.removeOverlay(this.marker) ;
			var y = this.polygons.length ;
			for(var j = 0; j < y; j++)
				map.removeOverlay(this.polygons.pop()) ;
		}
		function draw(map)
		{
			this.marker = new google.maps.Marker({
				position: new google.maps.LatLng(this.latitude, this.longitude),
				icon: "./image/icons8-raspberry-pi-16.png",
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
					(new Request('querynode.php', {'ap' : address}, $("#tabs-1"), false)).send() ;
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
			var poly = new GPolygon(points, '#0080FF', 2, 1, '#0080FF', 0) ;
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
				var poly = new GPolygon(points, '#FF0000', 1, 0, '#FF0000', alpha) ;
				map.addOverlay(poly) ;
				polys.push(poly) ;
			}
			return polys ;
		}
		/*
		function add(cluster)
		{
			this.clusters.push(cluster) ;
		}
		
		function cluster(map, rMargin, cMargin)
		{	
			for(var i = 0; i < this.polygons.length; i++)
				map.removeOverlay(this.polygons[i]) ;
			if(this.clusters.length > 0)
			{
				var degrees = 360 / this.clusters.length ;
				for(var i = 0; i < this.clusters.length; i++)
				{
					var points = new Array() ;
					var inc = 5 * (1 - (2 * rMargin)) ;
					var start = (i * degrees) + (rMargin * degrees) ;
					var end = ((1 + i) * degrees) - (rMargin * degrees) ;
					
					if(this.clusters.length == 1)
					{
						inc = 10 ;
						start = 0 ; 
						end = 360 ;
					}
					
					for(var j = start; j <= end; j += inc)
					{
						points.push(new GLatLng(
							this.latitude + ((1 - cMargin) * .00045 * Math.sin(j * Math.PI / 180)),
							this.longitude + ((1 - cMargin) * .00045 * Math.cos(j * Math.PI / 180))
						)) ;
					}
					for(var j = end; j >= start; j -= inc)
					{
						points.push(new GLatLng(
							this.latitude + (cMargin * .00045 * Math.sin(j * Math.PI / 180)),
							this.longitude + (cMargin * .00045 * Math.cos(j * Math.PI / 180))
						)) ;
					}
					points.push(points[0]) ;
					var p = new GPolygon(points, this.clusters[i].color, 1, 0, this.clusters[i].color, .75) ;
					map.addOverlay(p) ;
					this.polygons.push(p) ;
				}
			}
		}
		*/		
	}
	
	function Request(url, params, target, flushable)
	{		
		//member variables
		this.url ;
		this.params = '' ;
		this.target ;
		this.flushable ;
		
		//initialization
		this.url = url ; 
		for(var i in params)
			this.params += i + '=' + escape(params[i]) + '&' ; 
		this.params = this.params.substr(0, (this.params.length - 1)) ;
		this.target = target ;
		this.flushable = flushable ;
		
		//functions
		this.send = send ;
		
		function send()
		{
			http.open("POST", this.url, true) ;
			http.setRequestHeader("Content-type", "application/x-www-form-urlencoded") ;
			http.setRequestHeader("Content-length", this.params.length) ;
			http.setRequestHeader("Connection", "close") ;
			http.onreadystatechange = function() {
					if((http.readyState == 3 && flushable) || (http.readyState == 4)) 
					{
						target.html(http.responseText) ;
						var scripts = target.getElementsByTagName('script') ;
						for (var i = 0; i < scripts.length; i++)
							eval(scripts[i].innerHTML) ;
					}
			} ;
			http.send(this.params) ;
		}
	}