<script type="text/javascript" src="/modules/exportproducts/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="/modules/exportproducts/js/jquery-ui-1.7.2.custom.min.js"></script>
<script type="text/javascript" src="/modules/exportproducts/js/jquery.stylish-select.min.js"></script>
<script type="text/javascript" src="/modules/exportproducts/js/jquery.selectboxes.min.js"></script>

<link rel="stylesheet" href="/modules/exportproducts/css/exportproducts.css" type="text/css" media="screen" title="UI CSS" charset="utf-8">

<script type="text/javascript">
{literal}
$(document).ready(function() { 
			
	var $fields = $('#unselected-fields');
	var $selfields = $('#selected-fields');
	
	$("#message").hide(); 
	
	$("#unselected-fields, #selected-fields").sortable({
		connectWith: '.connectedSortable',
		placeholder: 'ui-state-highlight',
		cursor: 'move',
		update: function() {
			if($(this).attr("id") == "selected-fields") {
				var order = $(this).sortable("serialize") + '&action=updateRecordsListings';
				$.post("/modules/exportproducts/exportproducts-ajax.php", order);
			} else {
				var order = $(this).sortable("serialize") + '&action=clearRecordsListings';
				$.post("/modules/exportproducts/exportproducts-ajax.php", order);
			}
		}
	}).disableSelection();
	
	$('#loadset').sSelect();
	$('#deleteset').sSelect();
});
	
	function saveSet() {
		$('#message').hide();
		var post_data = $('#selected-fields').sortable("serialize") + '&action=saveSet&name=' + $("#savecurrent").val();
		$.post("/modules/exportproducts/exportproducts-ajax.php", post_data, function(data) {
			$("#deleteset").append($("<option />").val(data).text($("#savecurrent").val()));
			$("#loadset").append($("<option />").val(data).text($("#savecurrent").val()));
			$('#loadset').resetSS();
			$('#deleteset').resetSS();
			$('#message').html("Saved Successfully!").addClass("success").show();
		});
	}
	
	function pexport() {
		var data = "&" + $('#selected-fields').sortable("serialize");
		$("#export_data").val(data);
		$("#export_form").submit();
	}
	
	function loadSet() {
		$('#message').hide();
		var setid = $("#loadset").val();
		var action = "loadSet";
		$("#selected-fields").find("li").remove().end();
		$.getJSON("/modules/exportproducts/exportproducts-ajax.php", {action:action,ajax:true,setid:setid},
        	function(data){
        		
        	  $.each(data, function(i,item){
        	 	li = '<li id="export_' + item.id +'">' + item.field_name + '</li>';
           		$('#selected-fields').append(li);
         	 	});
         	 	$('#message').html("Loaded Successfully!").addClass("success").show();
        });		
	}
	
	function clearSelected() {
		var action = "action=clearSelected";
		$.post("/modules/exportproducts/exportproducts-ajax.php", action);
		$("#selected-fields").find("li").remove().end();
	}
	
	function deleteSet() {
		$('#message').hide();
		var setid = $("#deleteset").val();
		var action = "setid=" + setid + "&action=deleteSet";
		$.post("/modules/exportproducts/exportproducts-ajax.php", action, function(data){
			$("#deleteset").removeOption(data);
			$("#loadset").removeOption(data);
			$('#loadset').resetSS();
			$('#deleteset').resetSS();
			$('#message').html("Deleted Successfully!").addClass("success").show();
		});
	}

	function updateExportFields(action) {
		$("#unselected-fields").find("li").remove().end();
		var exportcat = $("#exportcat").val();
		$.getJSON("/modules/exportproducts/exportproducts-ajax.php", {action:action,ajax:true,exportcat:exportcat}, function(data) {
        	  $.each(data, function(i,item) {
        	 		li = '<li id="export_' + item.id +'">' + item.field_name + '</li>';	
           			$('#unselected-fields').append(li); 
         	 });
        });
	}
{/literal}
</script>
<div style="float: left" id="contentLeft">
	<h3>Available Fields</h3>
	<div>
	<span class="contenttop"></span>
	<ul id="unselected-fields" class="connectedSortable">
		{foreach from=$available_fields key=id item=name}
		<li id="export_{$name.id}">{$name.field_name}</li>
		{/foreach}
	</ul>
	<span class="contentbottom"></span>
	</div>
</div>

<div style="float: left" id="contentRight">
	<h3>Selected Fields</h3>
	<div>
	<span class="contenttop"></span>
	<ul id="selected-fields" class="connectedSortable">
		{foreach from=$current_fields key=id item=name}
		<li id="export_{$name.id}">{$name.field_name}</li>
		{/foreach}
	</ul>
	<span class="contentbottom"></span>
	</div>
</div> 
<div id="exportoptions">
	<h3>Export Options</h3>
	<div>
	<span class="optionstop"></span>
	<a id="clearselected" onclick="clearSelected();">Clear Selected</a>
	<br/>
	<div id="message"></div>
	<form action="{$currentIndex}" method="post" accept-charset="utf-8">
		<label for="savecurrent">Save a field Set</label><br/><br/>
		<input type="text" name="savecurrent" value="" id="savecurrent"><a class="export_btn" onclick="saveSet();" id="submitsaveset" >Save Set</a>
	</form>
	<br/>
	<form action="/modules/exportproducts/exportproducts-ajax.php" method="post" accept-charset="utf-8">
		<label for="loadset">Load a field set</label><br/><br/>
		<select name="loadset" id="loadset" size="1">
			<option value="">Select a field set to load</option>
			{foreach from=$sets key=id item=set}
				<option value="{$id}">{$set}</option>
			{/foreach}
		</select><a class="export_btn" onclick="loadSet();" id="loadfieldset" >Load Set</a>
	</form>
	<br/>
	<form action="/modules/exportproducts/exportproducts-ajax.php" method="post" accept-charset="utf-8">
		<label for="deleteset">Delete a field set:</label><br/><br/>
		<select name="deleteset" id="deleteset" size="1">
			<option value="">Select a field set to delete</option>
			{foreach from=$sets key=id item=set}
				<option value="{$id}">{$set}</option>
			{/foreach}
		</select><a class="export_btn" onclick="deleteSet();" id="deletefieldset">Delete Set</a>
	</form>
	<br/>
	<form id="export_form" action="{$currentIndex}" method="post" accept-charset="utf-8">
	<input type="hidden" name="export_data" value="" id="export_data">
	<input type="hidden" name="export" value="export" id="export">
	<p><a id="exportnow" class="export_btn" onclick="pexport();">Export Now</a></p>
	</form>
	<span class="optionsbottom"></span>
	</div>
</div>
<div style="clear: both"></div>