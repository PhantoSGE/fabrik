/*! Fabrik */
var FbElement=my.Class({options:{element:null,defaultVal:"",value:"",label:"",editable:!1,isJoin:!1,joinId:0,inRepeatGroup:!1},constructor:function(a,b){var c=this;if(this.plugin="",b.element=a,this.strElement=a,this.loadEvents=[],this.events={},this.options=$.extend(this.options,b),$("#"+this.options.element+"_chzn")){var d=this.getChangeEvent();jQuery("#"+this.options.element).on("change",{changeEvent:d},function(a){$("#"+c.id).trigger(a.data.changeEvent,new jQuery.Event(a.data.changeEvent))})}return this.setElement()},destroy:function(){},setElement:function(){return $("#"+this.options.element)?(this.element=$("#"+this.options.element),this.setorigId(),!0):!1},get:function(a){return"value"===a?this.getValue():void 0},getFormElementsKey:function(a){return this.baseElementId=a,a},attachedToForm:function(){this.setElement(),this.alertImage=$(document.createElement("i")).addClass(this.form.options.images.alert),this.successImage=$(document.createElement("i")).addClass("icon-checkmark").css({color:"green"})},fireEvents:function(a){var b=this;this.hasSubElements()?this._getSubElements().each(function(b){Array.from(a).each(function(a){b.trigger(a)})}):Array.from(a).each(function(a){b.element&&b.element.trigger(a)})},find:function(){return 0===this.element.length&&(this.element=$("#"+this.options.element)),this.element},_getSubElements:function(){var a=this.find();return 0===a.length?!1:(this.subElements=a.find(".fabrikinput"),this.subElements)},hasSubElements:function(){return this._getSubElements(),$.isArray(this.subElements)&&this.subElements.length>0?!0:!1},unclonableProperties:function(){return["form"]},cloneUpdateIds:function(a){this.element=$("#"+a),this.options.element=a},runLoadEvent:function(js,delay){delay=delay?delay:0,"function"==typeof js?js.delay(delay):0===delay?eval(js):function(){console.log("delayed calling runLoadEvent for "+delay),eval(js)}.bind(this).delay(delay)},removeCustomEvents:function(){},renewEvents:function(){var a=this;jQuery.each(this.events,function(b,c){a.element.off(b),c.each(function(c){a.addNewEventAux(b,c)})})},addNewEventAux:function(action,js){this.element.on(action,function(e){"function"===typeOf(js)?js.delay(0,this,this):eval(js)}.bind(this))},addNewEvent:function(a,b){"load"===a?(this.loadEvents.push(b),this.runLoadEvent(b)):(this.element||(this.element=$("#"+this.strElement)),this.element&&(Object.keys(this.events).contains(a)||(this.events[a]=[]),this.events[a].push(b),this.addNewEventAux(a,b)))},addEvent:function(a,b){this.addNewEvent(a,b)},validate:function(){},addNewOption:function(a,b){var c,d,e=$("#"+this.options.element+"_additions"),f=e.val(),g={val:a,label:b},h="[";for(c=""!==f?JSON.decode(f):[],c.push(g),d=0;d<c.length;d++)h+=JSON.encode(c[d])+",";h=h.substring(0,h.length-1)+"]",e.val(h)},getLabel:function(){return this.options.label},setLabel:function(a){this.options.label=a;var b=this.getLabelElement();b.length>0&&(b[0].textContent=a)},update:function(a){this.find()&&(this.options.editable?this.element.val(a):this.element.html(a))},updateByLabel:function(a){this.update(a)},set:function(a){this.update(a)},getValue:function(){return this.element?this.options.editable?this.element.value:this.options.value:!1},reset:function(){this.resetEvents(),this.options.editable===!0&&this.update(this.options.defaultVal)},resetEvents:function(){this.loadEvents.each(function(a){this.runLoadEvent(a,100)}.bind(this))},clear:function(){this.update("")},onsubmit:function(a){a&&a(!0)},afterAjaxValidation:function(){},cloned:function(){var a=this;if(this.renewEvents(),this.element.hasClass("chzn-done")){this.element.removeClass("chzn-done"),this.element.addClass("chzn-select"),this.element.parent().find(".chzn-container").destroy(),$("#"+this.element.id).chosen();var b=this.getChangeEvent();$("#"+this.options.element).on("change",{changeEvent:b},function(b){$("#"+a.id).trigger(b.data.changeEvent,jQuery.Event(b.data.changeEvent))})}},decloned:function(){},getContainer:function(){return"null"===typeOf(this.element)?!1:this.element.closest(".fabrikElementContainer")},getErrorElement:function(){return this.getContainer().find(".fabrikErrorMessage")},getLabelElement:function(){return this.getContainer().find(".fabrikLabel")},getValidationFx:function(){return this.validationFX||(this.validationFX=new Fx.Morph(this.getErrorElement()[0],{duration:500,wait:!0})),this.validationFX},tips:function(){return Fabrik.tips.elements.filter(function(a){return a===this.getContainer()||a.parent()===this.getContainer()?!0:void 0}.bind(this))},addTipMsg:function(a,b){b=b?b:"error";var c,d=this.tips();if(0!==d.length&&(d=jQuery(d[0]),void 0===d.attr(b))){d.data("popover").show(),d.attr(b,a),c=d.data("popover").tip().find(".popover-content");var e=$("<div>");e.html(c.html());var f=$("<li>").addClass(b);f.html(a),$("<i>").addClass(this.form.options.images.alert).inject(f,"top"),e.find("ul").adopt(f),d.data("content",unescape(e.get("html"))),d.data("popover").setContent(),d.data("popover").options.content=e.get("html"),d.data("popover").hide()}},removeTipMsg:function(){var a,b=b?b:"error",c=this.tips();if(c=jQuery(c[0]),void 0!==c.attr(b)){c.data("popover").show(),a=c.data("popover").tip().find(".popover-content");var d=$(document.createElement("div")).html(a.html()),e=d.find("li.error");e&&e.destroy(),c.attr("data-content",d.get("html")),c.data("popover").setContent(),c.data("popover").options.content=d.get("html"),c.data("popover").hide(),c.removeAttr(b)}},setErrorMessage:function(a,b){var c,d=["fabrikValidating","fabrikError","fabrikSuccess"],e=this.getContainer();if(e===!1)return void console.log("Notice: couldn not set error msg for "+a+" no container class found");d.each(function(a){b===a?e.addClass(a):e.removeClass(a)});var f=this.getErrorElement();switch(f.each(function(a){a.empty()}),b){case"fabrikError":if(Fabrik.loader.stop(this.element),this.addTipMsg(a),e.removeClass("success").removeClass("info").addClass("error"),f.length>1)for(c=1;c<f.length;c++)f[c].html(a);break;case"fabrikSuccess":e.addClass("success").removeClass("info").removeClass("error"),Fabrik.loader.stop(this.element),this.removeTipMsg();break;case"fabrikValidating":e.removeClass("success").addClass("info").removeClass("error"),Fabrik.loader.start(this.element,a)}this.getErrorElement().removeClass("fabrikHide");var g=this.form;("fabrikError"===b||"fabrikSuccess"===b)&&g.updateMainError();var h=this.getValidationFx();switch(b){case"fabrikValidating":case"fabrikError":h.start({opacity:1});break;case"fabrikSuccess":h.start({opacity:1}).chain(function(){e.hasClass("fabrikSuccess")&&(e.removeClass("fabrikSuccess"),this.start.delay(700,this,{opacity:0,onComplete:function(){e.addClass("success").removeClass("error"),g.updateMainError(),d.each(function(a){e.removeClass(a)})}}))})}},setorigId:function(){if(this.options.inRepeatGroup){var a=this.options.element;this.origId=a.substring(0,a.length-1-this.options.repeatCounter.toString().length)}},decreaseName:function(a){var b=this.find(),c=this;return 0===b.length?!1:(this.hasSubElements()?this._getSubElements().each(function(b){this.name=c._decreaseName(b.name,a),this.id=c._decreaseId(b.id,a)}):void 0!==this.element.name&&(this.element.name=this._decreaseName(this.element.name,a)),void 0!==this.element.id&&(this.element.id=this._decreaseId(this.element.id,a)),this.element.id)},_decreaseId:function(a,b,c){var d=!1;c=c?c:!1,c!==!1&&a.contains(c)&&(a=a.replace(c,""),d=!0);var e=a.split("_"),f=e.getLast();if("null"===typeOf(parseInt(f,10)))return e.join("_");f>=1&&f>b&&f--,e.splice(e.length-1,1,f);var g=e.join("_");return d&&(g+=c),this.options.element=g,g},_decreaseName:function(a,b,c){c=c?c:!1;var d=!1;c!==!1&&a.contains(c)&&(a=a.replace(c,""),d=!0);var e=a.split("["),f=parseInt(e[1].replace("]",""),10);f>=1&&f>b&&f--,f+="]",e[1]=f;var g=e.join("[");return d&&(g+=c),g},getRepeatNum:function(){return this.options.inRepeatGroup===!1?!1:this.element.id.split("_").getLast()},getBlurEvent:function(){return"SELECT"===this.element.prop("tagName")?"change":"blur"},getChangeEvent:function(){return"change"},select:function(){},focus:function(){},hide:function(){var a=this.getContainer();a&&a.hide()},show:function(){var a=this.getContainer();a&&a.show()},toggle:function(){var a=this.getContainer();a&&a.toggle()},getCloneName:function(){return this.options.element},doTab:function(){(function(){this.redraw()}).bind(this).delay(500)},watchTab:function(){var a,b,c=this,d=this.element.closest(".tab-pane");d&&(a=$("a[href$=#"+d.id+"]"),b=a.closest("ul.nav"),b.on("click","a",function(a){c.doTab(a)}))},updateUsingRaw:function(){return!1}}),FbFileElement=my.Class(FbElement,{ajaxFolder:function(){var a=this;if(this.folderlist=[],0!==this.element.length){var b=this.element.closest(".fabrikElement");this.breadcrumbs=b.find(".breadcrumbs"),this.folderdiv=b.find(".folderselect"),this.folderdiv.slideUp(500,function(){}),this.hiddenField=b.find(".folderpath"),b.find(".toggle").addEvent("click",function(b){b.stopPropagation(),a.slider.toggle()}),this.watchAjaxFolderLinks()}},watchAjaxFolderLinks:function(){var a=this;this.folderdiv.find("a").on("click",function(b){a.browseFolders(b)}),this.breadcrumbs.find("a").on("click",function(b){a.useBreadcrumbs(b)})},browseFolders:function(a){var b=$(a.target).text();a.stopPropagation(),this.folderlist.push(b);var c=this.options.dir+this.folderlist.join(this.options.ds);this.addCrumb(b),this.doAjaxBrowse(c)},useBreadcrumbs:function(a){a.stopPropagation();var b=this,c=a.target.className;this.folderlist=[],this.breadcrumbs.find("a").each(function(d){d.className!==c&&b.folderlist.push(a.target.innerHTML)});var d=[this.breadcrumbs.find("a").shift().clone(),this.breadcrumbs.find("span").shift().clone()];this.breadcrumbs.empty(),this.breadcrumbs.adopt(d),this.folderlist.each(function(a){b.addCrumb(a)});var e=this.options.dir+this.folderlist.join(this.options.ds);this.doAjaxBrowse(e)},doAjaxBrowse:function(a){var b=this,c={dir:a,option:"com_fabrik",format:"raw",task:"plugin.pluginAjax",plugin:"fileupload",method:"ajax_getFolders",element_id:this.options.id};new $.ajax({url:"",data:c}).done(function(a){a=JSON.decode(a),this.folderdiv.empty(),a.each(function(a){$(document.createElement("li")).addClass("fileupload_folder").adopt($(document.createElement("a")).attr({href:"#"}).text(a)).inject(b.folderdiv)}),0===a.length?this.folderdiv.slideUp(500):this.folderdiv.slideDown(500),this.watchAjaxFolderLinks(),this.hiddenField.value="/"+this.folderlist.join("/")+"/",this.trigger("onBrowse")})},addCrumb:function(a){this.breadcrumbs.adopt($(document.createElement("a")).attr({href:"#","class":"crumb"+this.folderlist.length}).text(a),$(document.createElement("span")).text(" / "))}});