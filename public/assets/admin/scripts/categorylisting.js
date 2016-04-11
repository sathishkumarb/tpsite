var oTable, oSettingsusers, oHeader;
var listUserType ='S';  
//$(window).load(function() {                
$(document).ready(function() {                	
        
 oSettingsusers = $('#datatablesuser').dataTable( {
		"bDestroy": true,
                 "bRetrieve": true,
		"iDisplayLength": 10,
		"aLengthMenu": [[10, 25, 50, 10000], [10, 25, 50, "All"]],
		"bPaginate": true,
		"bStateSave": false,
		"pagingType": "full_numbers",
		"sDom": '<lfr>t<"F"ip>',
		"aoColumns": [{ "bSortable": false }, { "bSortable": true },{ "bSortable": false }],
		"bProcessing": true,
		"bServerSide": true,
                "bLengthChange": true,                                        
                //"sPaginationType": "bootstrap",
                "bSortable": true,                
                "oLanguage": { "sEmptyTable": "No matching results found." },
		"fnRowCallback": function( nRow ){
                        $('#datatablesusers_filter input').attr("placeholder", "Search from here");										
                },						        		
        "sAjaxSource": FULL_URL_PATH+"/admin/category/categorylistingdata"
	});	
} );