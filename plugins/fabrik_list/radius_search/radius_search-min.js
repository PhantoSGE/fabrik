function geoCode(){window.addEvent("domready",function(){var c=new google.maps.LatLng(Fabrik.radiusSearch.geocode_default_lat,Fabrik.radiusSearch.geocode_default_long);var a={zoom:4,mapTypeId:google.maps.MapTypeId.ROADMAP};Fabrik.radiusSearch=typeOf(Fabrik.radiusSearch)==="null"?{}:Fabrik.radiusSearch;var b=document.getElements(".radius_search_geocode_map");b.each(function(e){Fabrik.radiusSearch[e.id]=typeOf(Fabrik.radiusSearch[e.id])==="null"?{}:Fabrik.radiusSearch[e.id];Fabrik.radiusSearch[e.id].map=new google.maps.Map(e,a);var j=e.getParent(".radius_search_geocode");var i=j.getParent(".radius_search_options");var f=j.getElement("button");var g=j.getElement(".radius_search_geocode_field");var h=function(o){var m=g.value;var n=new google.maps.Geocoder();n.geocode({address:m},function(q,p){if(p===google.maps.GeocoderStatus.OK){var r=q[0].geometry.location;i.getElement("input[name^=radius_search_geocode_lat]").value=r.lat();i.getElement("input[name^=radius_search_geocode_lon]").value=r.lng();Fabrik.radiusSearch[e.id].map.setCenter(q[0].geometry.location);Fabrik.radiusSearch[e.id].marker.setPosition(q[0].geometry.location);document.id("radius_search_lat").value=""}else{alert("Geocode was not successful for the following reason: "+p)}})};if(typeOf(f)!=="null"){f.addEvent("click",function(m){m.stop();h()})}else{g.addEvent("keyup",function(m){h()})}var l=i.getElement("input[name=geo_code_def_zoom]").get("value").toInt();var k=i.getElement("input[name=geo_code_def_lat]").get("value").toFloat();var d=i.getElement("input[name=geo_code_def_lon]").get("value").toFloat();Fabrik.fireEvent("google.radiusmap.loaded",[e.id,l,k,d])})})}var FbListRadiusSearch=new Class({Extends:FbListPlugin,options:{geocode_default_lat:"0",geocode_default_long:"0",geocode_default_zoom:4,prefilter:true,prefilterDistance:1000,prefilterDone:false},geocoder:null,map:null,initialize:function(b){this.parent(b);Fabrik.radiusSearch={};var a="radius_search_geocode_map"+this.options.renderOrder;if(typeOf(Fabrik.radiusSearch[a])==="null"){Fabrik.radiusSearch[a]={}}Fabrik.radiusSearch[a].geocode_default_lat=this.options.geocode_default_lat;Fabrik.radiusSearch[a].geocode_default_long=this.options.geocode_default_long;Fabrik.radiusSearch[a].geocode_default_zoom=this.options.geocode_default_zoom;head.ready(function(){Fabrik.addEvent("google.radiusmap.loaded",function(e,f,g,h){var i=new google.maps.LatLng(g,h);if(Fabrik.radiusSearch[e].loaded){return}Fabrik.radiusSearch[e].loaded=true;Fabrik.radiusSearch[e].map.setCenter(i);Fabrik.radiusSearch[e].map.setZoom(f);Fabrik.radiusSearch[e].marker=new google.maps.Marker({map:Fabrik.radiusSearch[e].map,draggable:true,position:i});google.maps.event.addListener(Fabrik.radiusSearch[e].marker,"dragend",function(){var k=Fabrik.radiusSearch[e].marker.getPosition();var l=document.id(e).getParent(".radius_search_options");var j=l.getElement("input[name=radius_search_geocode_lat]");if(typeOf(j)!=="null"){j.value=k.lat();l.getElement("input[name=radius_search_geocode_lon]").value=k.lng()}});this.makeWin(e)}.bind(this));Fabrik.loadGoogleMap(true,"geoCode");this.listform=this.listform.getElement("#radius_search"+this.options.renderOrder);if(typeOf(this.options.value)==="null"){this.options.value=0}this.watchActivate();this.listform.getElements("input[name^=radius_search_type]").addEvent("click",function(f){this.toggleFields(f)}.bind(this));this.options.value=this.options.value.toInt();if(typeOf(this.listform)==="null"){return}var c=this.listform.getElement(".radius_search_distance");var d=this.listform.getElement(".slider_output");this.mySlide=new Slider(this.listform.getElement(".fabrikslider-line"),this.listform.getElement(".knob"),{onChange:function(e){c.value=e;d.set("text",e+this.options.unit)}.bind(this),steps:this.options.steps}).set(0);this.mySlide.set(this.options.value);c.value=this.options.value;d.set("text",this.options.value);if(this.options.myloc&&!this.options.prefilterDone){if(geo_position_js.init()){geo_position_js.getCurrentPosition(function(e){this.setGeoCenter(e)}.bind(this),function(f){this.geoCenterErr(f)}.bind(this),{enableHighAccuracy:true})}}}.bind(this))},makeWin:function(d){var f=document.id(d).getParent(".radus_search");var a=new Element("button.btn.button").set("text",Joomla.JText._("COM_FABRIK_SEARCH"));a.addEvent("click",function(b){b.stop();f.setStyles({position:"relative",left:0});this.win.open()}.bind(this));console.log(a);f.getParent().adopt(a);console.log(f);var e={id:"radius_search",title:Joomla.JText._("PLG_LIST_RADIUS_SEARCH"),loadMethod:"html",content:f,width:500,height:500,visible:false,onContentLoaded:function(){this.center()}};this.win=Fabrik.getWindow(e)},watchActivate:function(){var d=this.listform.getElement(".radius_search_options");this.listform.getElements("input[name^=radius_search_active]").addEvent("click",function(a){switch(a.target.get("value")){case"1":d.show();d.setStyles({position:"relative",left:"0"});break;case"0":d.hide();d.setStyles({position:"absolute",left:"-100000px"});break}}.bind(this));var b=this.listform.getElements("input[name^=radius_search_active]").filter(function(a){return a.checked===true});if(b.length>0&&b[0].get("value")==="0"){d.setStyles({position:"absolute",left:"-100000px"})}},setGeoCenter:function(a){this.geocenterpoint=a;this.geoCenter(a);this.prefilter()},prefilter:function(){if(this.options.prefilter){this.fx.slideIn();this.mySlide.set(this.options.prefilterDistance);this.listform.getElements("input[name^=radius_search_active]").filter(function(a){return a.get("value")==="1"}).getLast().checked=true;this.listform.getElements("input[value=mylocation]").checked=true;this.list.submit("filter")}},geoCenter:function(a){if(typeOf(a)==="null"){alert(Joomla.JText._("PLG_VIEW_RADIUS_NO_GEOLOCATION_AVAILABLE"))}else{this.listform.getElement("input[name=radius_search_lat]").value=a.coords.latitude.toFixed(2);this.listform.getElement("input[name=radius_search_lon]").value=a.coords.longitude.toFixed(2)}},geoCenterErr:function(a){fconsole("geo location error="+a.message)},toggleActive:function(a){},toggleFields:function(a){var b=a.target.getParent(".radius_search_options");switch(a.target.get("value")){case"latlon":b.getElement(".radius_search_place_container").hide();b.getElement(".radius_search_coords_container").show();b.getElement(".radius_search_geocode").setStyles({position:"absolute",left:"-100000px"});break;case"mylocation":b.getElement(".radius_search_place_container").hide();b.getElement(".radius_search_coords_container").hide();b.getElement(".radius_search_geocode").setStyles({position:"absolute",left:"-100000px"});this.setGeoCenter(this.geocenterpoint);break;case"place":b.getElement(".radius_search_place_container").show();b.getElement(".radius_search_coords_container").hide();b.getElement(".radius_search_geocode").setStyles({position:"absolute",left:"-100000px"});break;case"geocode":b.getElement(".radius_search_place_container").hide();b.getElement(".radius_search_coords_container").hide();b.getElement(".radius_search_geocode").setStyles({position:"relative",left:0});break}},clearFilter:function(){this.listform.getElements("input[name^=radius_search_active]").filter(function(a){return a.get("value")==="0"}).getLast().checked=true}});