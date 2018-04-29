/**
 * Author: Thilina Hasantha
 */


/**
 * UserReportAdapter
 */


function UserReportAdapter(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
	this._construct();
}

UserReportAdapter.inherits(ReportAdapter);

UserReportAdapter.method('renderForm', function(object) {
	var that = this;
	this.processFormFieldsWithObject(object);
	if(this.remoteFieldsExists){
		var cb = function(){
			that.renderFormNew(object);
		};
		this.initFieldMasterData(cb);
	}else{
		this.initFieldMasterData();
		that.renderFormNew(object);
	}

	this.currentReport = object;

});

