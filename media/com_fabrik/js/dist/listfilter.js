/*! Fabrik */
define(["jquery","fab/fabrik","fab/advanced-search"],function(a,b,c){var d=new Class({Implements:[Events],options:{container:"",filters:[],type:"list",id:"",ref:"",advancedSearch:{controller:"list"}},initialize:function(d){var e,f=this;this.filters={},this.options=a.extend(this.options,d),this.advancedSearch=!1,this.container=a("#"+this.options.container),this.filterContainer=this.container.find(".fabrikFilterContainer"),this.filtersInHeadings=this.container.find(".listfilter");var g=this.container.find(".toggleFilters");if(g.on("click",function(a){a.stopPropagation(),f.filterContainer.toggle(),f.filtersInHeadings.toggle()}),g.length>0&&(this.filterContainer.hide(),this.filtersInHeadings.toggle()),0!==this.container.length){this.getList();var h=this.container.find(".clearFilters");h.off(),h.on("click",function(b){b.preventDefault(),f.container.find(".fabrik_filter").each(function(b,c){f.clearAFilter(a(c))}),f.clearPlugins(),f.submitClearForm()}),e=this.container.find(".advanced-search-link"),e.on("click",function(d){d.preventDefault();var e,g=a(d.target);"A"!==g.prop("tagName")&&(g=g.closest("a"));var h=g.prop("href");h+="&listref="+f.options.ref,e={id:"advanced-search-win"+f.options.ref,modalId:"advanced-filter",title:Joomla.JText._("COM_FABRIK_ADVANCED_SEARCH"),loadMethod:"xhr",evalScripts:!0,contentURL:h,width:710,height:340,y:f.options.popwiny,onContentLoaded:function(){var a=b.blocks["list_"+f.options.ref];void 0===a&&(a=b.blocks[f.options.container],f.options.advancedSearch.parentView=f.options.container),a.advancedSearch=new c(f.options.advancedSearch),this.fitToContent(!1)}},b.getWindow(e)}),a(".fabrik_filter.advancedSelect").on("change",{changeEvent:"change"},function(a){this.fireEvent(a.data.changeEvent,new Event.Mock(document.getElementById(this.id),a.data.changeEvent))}),this.watchClearOne()}},getList:function(){return this.list=b.blocks[this.options.type+"_"+this.options.ref],void 0===this.list&&(this.list=b.blocks[this.options.container]),this.list},addFilter:function(a,b){this.filters.hasOwnProperty(a)===!1&&(this.filters[a]=[]),this.filters[a].push(b)},onSubmit:function(){this.filters.date&&a.each(this.filters.date,function(a,b){b.onSubmit()}),this.showFilterState()},onUpdateData:function(){this.filters.date&&a.each(this.filters.date,function(a,b){b.onUpdateData()})},getFilterData:function(){var b={};return this.container.find(".fabrik_filter").each(function(){if("undefined"!=typeof a(this).prop("id")&&a(this).prop("id").test(/value$/)){var c=a(this).prop("id").match(/(\S+)value$/)[1];b[c]="SELECT"===a(this).prop("tagName")&&-1!==this.selectedIndex?a(this.options[this.selectedIndex]).text():a(this).val(),b[c+"_raw"]=a(this).val()}}),b},update:function(){a.each(this.filters,function(a,b){b.each(function(a){a.update()})})},clearAFilter:function(a){var b;(a.prop("name").contains("[value]")||a.prop("name").contains("fabrik_list_filter_all")||a.hasClass("autocomplete-trigger"))&&("SELECT"===a.prop("tagName")?(b=a.prop("multiple")?-1:0,a.prop("selectedIndex",b)):"checkbox"===a.prop("type")?a.prop("checked",!1):a.val(""))},clearPlugins:function(){var a=this.getList().plugins;null!==a&&a.each(function(a){a.clearFilter()})},submitClearForm:function(){var b="FORM"===this.container.prop("tagName")?this.container:this.container.find("form");a("<input />").attr({name:"resetfilters",value:1,type:"hidden"}).appendTo(b),"list"===this.options.type?this.list.submit("list.clearfilter"):this.container.find("form[name=filter]").submit()},watchClearOne:function(){var b=this;this.container.find("*[data-filter-clear]").on("click",function(c){c.stopPropagation();var d=c.event?c.event.currentTarget:c.currentTarget,e=a(d).data("filter-clear"),f=a('*[data-filter-name="'+e+'"]');f.each(function(c,d){b.clearAFilter(a(d))}),b.submitClearForm(),b.showFilterState()})},showFilterState:function(){var c,d,e,f=a(b.jLayouts["modal-state-label"]),g=this,h=!1,i=this.container.find("*[data-modal-state-display]");0!==i.length&&(i.empty(),a.each(this.options.filters,function(b,j){var k=g.container.find('*[data-filter-name="'+j.name+'"]');"SELECT"===k.prop("tagName")&&-1!==k[0].selectedIndex?(d=a(k[0].options[k[0].selectedIndex]).text(),e=k.val()):d=e=k.val(),""!==d&&""!==e&&(h=!0,c=f.clone(),c.find("*[data-filter-clear]").data("filter-clear",j.name),c.find("*[data-modal-state-label]").text(j.label),c.find("*[data-modal-state-value]").text(d),i.append(c))}),h?this.container.find("*[data-modal-state-container]").show():this.container.find("*[data-modal-state-container]").hide(),this.watchClearOne())}});return d});