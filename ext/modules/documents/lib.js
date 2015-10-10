/*
This file is part of iCE Hrm.

iCE Hrm is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

iCE Hrm is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with iCE Hrm. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]  
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */

function EmployeeDocumentAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmployeeDocumentAdapter.inherits(AdapterBase);



EmployeeDocumentAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "document",
	        "details",
	        "date_added",
	        "status",
	        "attachment"
	];
});

EmployeeDocumentAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Document" },
			{ "sTitle": "Details" },
			{ "sTitle": "Date Added"},
			{ "sTitle": "Status"},
			{ "sTitle": "Attachment","bVisible":false}
	];
});

EmployeeDocumentAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "document", {"label":"Document","type":"select2","remote-source":["Document","id","name"]}],
	        //[ "date_added", {"label":"Date Added","type":"date","validation":""}],
	        [ "valid_until", {"label":"Valid Until","type":"date","validation":"none"}],
	        [ "status", {"label":"Status","type":"select","source":[["Active","Active"],["Inactive","Inactive"],["Draft","Draft"]]}],
	        [ "details", {"label":"Details","type":"textarea","validation":"none"}],
	        [ "attachment", {"label":"Attachment","type":"fileupload","validation":"none"}]
	];
});


EmployeeDocumentAdapter.method('getActionButtonsHtml', function(id,data) {	
	var downloadButton = '<img class="tableActionButton" src="_BASE_images/download.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Download Document" onclick="download(\'_attachment_\');return false;"></img>';
	var editButton = '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>';
	var deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
	var html = '<div style="width:80px;">_edit__download__delete_</div>';
	
	html = html.replace('_download_',downloadButton);
	
	if(this.showDelete){
		html = html.replace('_delete_',deleteButton);
	}else{
		html = html.replace('_delete_','');
	}
	
	if(this.showEdit){
		html = html.replace('_edit_',editButton);
	}else{
		html = html.replace('_edit_','');
	}
	
	html = html.replace(/_id_/g,id);
	html = html.replace(/_attachment_/g,data[5]);
	html = html.replace(/_BASE_/g,this.baseUrl);
	return html;
});
