/**
 * Author: Thilina Hasantha
 */


/**
 * PaydayAdapter
 */

function PaydayAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
}

PaydayAdapter.inherits(AdapterBase);



PaydayAdapter.method('getDataMapping', function() {
    return [
        "id",
        "name"
    ];
});

PaydayAdapter.method('getHeaders', function() {
    return [
        { "sTitle": "ID" ,"bVisible":false},
        { "sTitle": "Select Pay Frequency"}
    ];
});

PaydayAdapter.method('getFormFields', function() {
    return [
        [ "name", {"label":"Name","type":"text","validation":""}]
    ];
});

/*
 PaydayAdapter.method('showActionButtons' , function() {
 return false;
 });
 */

PaydayAdapter.method('getAddNewLabel', function() {
    return "Run Payroll";
});

PaydayAdapter.method('createTable', function(elementId) {
    $("#payday_all").off();
    this.uber('createTable',elementId);
    $("#payday_all").off().on('click',function(){
        if($(this).is(':checked')){
            $('.paydayCheck').prop('checked', true);
        }else{
            $('.paydayCheck').prop('checked', false);
        }
    })
});

PaydayAdapter.method('getActionButtonsHtml', function(id,data) {
    var editButton = '<input type="checkbox" class="paydayCheck" id="payday__id_" name="payday__id_" value="checkbox_payday__id_"/>';

    var html = '<div style="width:120px;">_edit_</div>';
    html = html.replace('_edit_',editButton);

    html = html.replace(/_id_/g,id);
    html = html.replace(/_BASE_/g,this.baseUrl);
    return html;
});

PaydayAdapter.method('getActionButtonHeader', function() {
    return { "sTitle": '<input type="checkbox" id="payday_all" name="payday_all" value="checkbox_payday_all"/>', "sClass": "center" };
});




/**
 * PayrollAdapter
 */

function PayrollAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
}

PayrollAdapter.inherits(AdapterBase);



PayrollAdapter.method('getDataMapping', function() {
    return [
        "id",
        "name",
        "pay_period",
        "department",
        "date_start",
        "date_end",
        "status"

    ];
});

PayrollAdapter.method('getHeaders', function() {
    return [
        { "sTitle": "ID","bVisible":false },
        { "sTitle": "Name" },
        { "sTitle": "Pay Frequency"},
        { "sTitle": "Department"},
        { "sTitle": "Date Start"},
        { "sTitle": "Date End"},
        { "sTitle": "Status"}
    ];
});

PayrollAdapter.method('getFormFields', function() {
    return [
        [ "id", {"label":"ID","type":"hidden"}],
        [ "name", {"label":"Name","type":"text"}],
        [ "pay_period", {"label":"Pay Frequency","type":"select","remote-source":["PayFrequency","id","name"],"sort":"none"}],
        [ "deduction_group", {"label":"Calculation Group","type":"select","remote-source":["DeductionGroup","id","name"],"sort":"none"}],
        [ "payslipTemplate", {"label":"Payslip Template","type":"select","remote-source":["PayslipTemplate","id","name"]}],
        [ "department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"],"sort":"none"}],
        [ "date_start",  {"label":"Start Date","type":"date","validation":""}],
        [ "date_end",  {"label":"End Date","type":"date","validation":""}],
        //[ "column_template", {"label":"Report Column Template","type":"select","remote-source":["PayrollColumnTemplate","id","name"]}],
        [ "columns", {"label":"Payroll Columns","type":"select2multi","remote-source":["PayrollColumn","id","name"]}],
        [ "status", {"label":"Status","type":"select","source":[["Draft","Draft"],["Completed","Completed"]],"sort":"none"}]
    ];
});

PayrollAdapter.method('postRenderForm', function(object, $tempDomObj) {
    if(object != null && object != undefined && object.id != undefined && object.id != null){
        $tempDomObj.find("#pay_period").attr('disabled','disabled');
        $tempDomObj.find("#department").attr('disabled','disabled');
        //$tempDomObj.find("#date_start").attr('disabled','disabled');
        //$tempDomObj.find("#date_end").attr('disabled','disabled');
        //$tempDomObj.find("#column_template").attr('disabled','disabled');
    }


});

PayrollAdapter.method('process', function(id, status) {
    modJs = modJsList['tabPayrollData'];
    modJs.setCurrentPayroll(id);
    $("#Payroll").hide();
    $("#PayrollData").show();
    $("#PayrollDataButtons").show();

    if(status == 'Completed'){
        $(".completeBtnTable").hide();
        $(".saveBtnTable").hide();
    }else{
        $(".completeBtnTable").show();
        $(".saveBtnTable").show();
    }

    modJs.get([]);


});


PayrollAdapter.method('getActionButtonsHtml', function(id,data) {
    var editButton = '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>';
    var processButton = '<img class="tableActionButton" src="_BASE_images/run.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Process" onclick="modJs.process(_id_,\'_status_\');return false;"></img>';
    var deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
    var cloneButton = '<img class="tableActionButton" src="_BASE_images/clone.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Copy" onclick="modJs.copyRow(_id_);return false;"></img>';

    var html = '<div style="width:120px;">_edit__process__clone__delete_</div>';


    if(this.showAddNew){
        html = html.replace('_clone_',cloneButton);
    }else{
        html = html.replace('_clone_','');
    }

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

    /*
    if(data[6] != "Completed"){
        html = html.replace('_process_',processButton);
    }else{
        html = html.replace('_process_','');
    }
    */
    html = html.replace('_process_',processButton);


    html = html.replace(/_id_/g,id);
    html = html.replace(/_status_/g,data[6]);
    html = html.replace(/_BASE_/g,this.baseUrl);
    return html;
});

PayrollAdapter.method('get', function(callBackData) {
    $("#PayrollData").hide();
    $("#PayrollForm").hide();
    $("#PayrollDataButtons").hide();
    $("#Payroll").show();
    modJsList['tabPayrollData'].setCurrentPayroll(null);
    this.uber('get',callBackData);
});



/**
 * PayrollDataAdapter
 */

function PayrollDataAdapter(endPoint) {
    this.initAdapter(endPoint);
    this.cellDataUpdates = {};
    this.payrollId = null;
}

PayrollDataAdapter.inherits(TableEditAdapter);

PayrollDataAdapter.method('validateCellValue', function(element, evt, newValue) {
    modJs.addCellDataUpdate(element.data('colId'),element.data('rowId'),newValue);
    return true;
});

PayrollDataAdapter.method('setCurrentPayroll', function(val) {
    this.payrollId = val;
});


PayrollDataAdapter.method('addAdditionalRequestData' , function(type, req) {
    if(type == 'updateData'){
        req.payrollId = this.payrollId;
    }else if(type == 'updateAllData'){
        req.payrollId = this.payrollId;
    }else if(type == 'getAllData'){
        req.payrollId = this.payrollId;
    }

    return req;
});

PayrollDataAdapter.method('modifyCSVHeader', function(header) {
    header.unshift("");
    return header;
});

PayrollDataAdapter.method('getCSVData' , function() {
    var csv = "";

    for(var i=0;i<this.csvData.length;i++){
        csv += this.csvData[i].join(",");
        if(i < this.csvData.length -1){
            csv += "\r\n";
        }
    }

    return csv;
});

PayrollDataAdapter.method('downloadPayroll' , function() {
    var element = document.createElement('a');
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(this.getCSVData()));
    element.setAttribute('download', "payroll_"+this.payrollId+".csv");

    element.style.display = 'none';
    document.body.appendChild(element);

    element.click();

    document.body.removeChild(element);
});


/**
 * PayrollColumnAdapter
 */

function PayrollColumnAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
}

PayrollColumnAdapter.inherits(AdapterBase);

PayrollColumnAdapter.method('getDataMapping', function() {
    return [
        "id",
        "name",
        "colorder",
        "calculation_hook",
        "editable",
        "enabled"
    ];
});

PayrollColumnAdapter.method('getHeaders', function() {
    return [
        { "sTitle": "ID" ,"bVisible":false},
        { "sTitle": "Name"},
        { "sTitle": "Column Order"},
        { "sTitle": "Calculation Method"},
        { "sTitle": "Editable"},
        { "sTitle": "Enabled"}
    ];
});

PayrollColumnAdapter.method('getFormFields', function() {

    var fucntionColumnList = [ "calculation_columns", {"label":"Calculation Columns","type":"datagroup",
        "form":[
            [ "name", {"label":"Name","type":"text","validation":""}],
            [ "column", {"label":"Column","type":"select2","remote-source":["PayrollColumn","id","name"]}]
        ],
        "html":'<div id="#_id_#" class="panel panel-default">#_delete_##_edit_#<div class="panel-body">#_renderFunction_#</div></div>',
        "validation":"none",
        "render":function(item){
            var output = "Variable:"+item.name;
            return output;

        }

    }];

    return [
        [ "id", {"label":"ID","type":"hidden"}],
        [ "name", {"label":"Name","type":"text","validation":""}],
        [ "calculation_hook", {"label":"Predefined Calculations","type":"select2","allow-null":true,"null-label":"None","remote-source":["CalculationHook","code","name"]}],
        [ "salary_components", {"label":"Salary Components","type":"select2multi","remote-source":["SalaryComponent","id","name"]}],
        [ "deductions", {"label":"Calculation Method","type":"select2multi","remote-source":["Deduction","id","name"]}],
        [ "add_columns", {"label":"Columns to Add","type":"select2multi","remote-source":["PayrollColumn","id","name"]}],
        [ "sub_columns", {"label":"Columns to Subtract","type":"select2multi","remote-source":["PayrollColumn","id","name"]}],
        [ "colorder", {"label":"Column Order","type":"text","validation":"number"}],
        [ "editable", {"label":"Editable","type":"select","source":[["Yes","Yes"],["No","No"]]}],
        [ "enabled", {"label":"Enabled","type":"select","source":[["Yes","Yes"],["No","No"]]}],
        [ "default_value", {"label":"Default Value","type":"text","validation":""}],
        fucntionColumnList,
        [ "calculation_function", {"label":"Function","type":"text","validation":"none"}]
    ];
});




/**
 * PayrollColumnTemplateAdapter
 */

function PayrollColumnTemplateAdapter(endPoint) {
    this.initAdapter(endPoint);
}

PayrollColumnTemplateAdapter.inherits(AdapterBase);

PayrollColumnTemplateAdapter.method('getDataMapping', function() {
    return [
        "id",
        "name"
    ];
});

PayrollColumnTemplateAdapter.method('getHeaders', function() {
    return [
        { "sTitle": "ID" ,"bVisible":true},
        { "sTitle": "Name"}
    ];
});

PayrollColumnTemplateAdapter.method('getFormFields', function() {
    return [
        [ "id", {"label":"ID","type":"hidden"}],
        [ "name", {"label":"Name","type":"text","validation":""}],
        [ "columns", {"label":"Payroll Columns","type":"select2multi","remote-source":["PayrollColumn","id","name"]}]
    ];
});


/*
 * PayrollEmployeeAdapter
 */

function PayrollEmployeeAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
}

PayrollEmployeeAdapter.inherits(AdapterBase);



PayrollEmployeeAdapter.method('getDataMapping', function() {
    return [
        "id",
        "employee",
        "pay_frequency",
        "deduction_group",
        "currency"
    ];
});

PayrollEmployeeAdapter.method('getHeaders', function() {
    return [
        { "sTitle": "ID" ,"bVisible":false},
        { "sTitle": "Employee" },
        { "sTitle": "Pay Frequency"},
        { "sTitle": "Calculation Group"},
        { "sTitle": "Currency"},
    ];
});

PayrollEmployeeAdapter.method('getFormFields', function() {
    return [
        [ "id", {"label":"ID","type":"hidden"}],
        [ "employee", {"label":"Employee","type":"select2","remote-source":["Employee","id","first_name+last_name"]}],
        [ "pay_frequency", {"label":"Pay Frequency","type":"select2","remote-source":["PayFrequency","id","name"]}],
        [ "currency", {"label":"Currency","type":"select2","remote-source":["CurrencyType","id","code"]}],
        [ "deduction_group", {"label":"Calculation Group","type":"select2","allow-null":true,"null-label":"None","remote-source":["DeductionGroup","id","name"]}],
        [ "deduction_exemptions", {"label":"Calculation Exemptions","type":"select2multi","remote-source":["Deduction","id","name"],"validation":"none"}],
        [ "deduction_allowed", {"label":"Calculations Assigned","type":"select2multi","remote-source":["Deduction","id","name"],"validation":"none"}]
    ];
});

PayrollEmployeeAdapter.method('getFilters', function() {
    return [
        [ "employee", {"label":"Employee","type":"select2","remote-source":["Employee","id","first_name+last_name"]}]
    ];
});


/**
 * DeductionAdapter
 */

function DeductionAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
}

DeductionAdapter.inherits(AdapterBase);



DeductionAdapter.method('getDataMapping', function() {
    return [
        "id",
        "name",
        "deduction_group"
    ];
});

DeductionAdapter.method('getHeaders', function() {
    return [
        { "sTitle": "ID" ,"bVisible":false},
        { "sTitle": "Name" },
        { "sTitle": "Calculation Group"}
    ];
});

DeductionAdapter.method('getFormFields', function() {

    var rangeAmounts = [ "rangeAmounts", {"label":"Calculation Process","type":"datagroup",
        "form":[
            [ "lowerCondition", {"label":"Lower Limit Condition","type":"select","source":[["No Lower Limit","No Lower Limit"],["gt","Greater than"],["gte","Greater than or Equal"]]}],
            [ "lowerLimit", {"label":"Lower Limit","type":"text","validation":"float"}],
            [ "upperCondition", {"label":"Upper Limit Condition","type":"select","source":[["No Upper Limit","No Upper Limit"],["lt","Less than"],["lte","Less than or Equal"]]}],
            [ "upperLimit", {"label":"Upper Limit","type":"text","validation":"float"}],
            [ "amount", {"label":"Value","type":"text","validation":""}]
        ],
        "html":'<div id="#_id_#" class="panel panel-default">#_delete_##_edit_#<div class="panel-body">#_renderFunction_#</div></div>',
        "validation":"none",
        "custom-validate-function":function (data){
            var res = {};
            res['valid'] = true;
            if(data.lowerCondition == 'No Lower Limit'){
                data.lowerLimit = 0;
            }
            if(data.upperCondition == 'No Upper Limit'){
                data.upperLimit = 0;
            }
            res['params'] = data;
            return res;
        },
        "render":function(item){
            var output = "";
            var getSymbol = function(text){
                var map = {};
                map['gt'] = '>';
                map['gte'] = '>=';
                map['lt'] = '<';
                map['lte'] = '<=';

                return map[text];
            }
            if(item.lowerCondition != "No Lower Limit"){
                output += item.lowerLimit + " " + getSymbol(item.lowerCondition) + " ";
            }

            if(item.upperCondition != "No Upper Limit"){
                output += " and ";
                output += getSymbol(item.upperCondition) + " " + item.upperLimit + " ";
            }
            if(output == ""){
                return  "Deduction is "+item.amount + " for all ranges";
            }else{
                return  "If salary component "+output+ " deduction is "+item.amount;
            }


            return output;

        }

    }];

    return [
        [ "id", {"label":"ID","type":"hidden"}],
        [ "name", {"label":"Name","type":"text","validation":""}],
        [ "componentType", {"label":"Salary Component Type","type":"select2multi","allow-null":true,"remote-source":["SalaryComponentType","id","name"]}],
        [ "component", {"label":"Salary Component","type":"select2multi","allow-null":true,"remote-source":["SalaryComponent","id","name"]}],
        [ "payrollColumn", {"label":"Payroll Report Column","type":"select2","allow-null":true,"remote-source":["PayrollColumn","id","name"]}],
        rangeAmounts,
        [ "deduction_group", {"label":"Calculation Group","type":"select2","allow-null":true,"null-label":"None","remote-source":["DeductionGroup","id","name"]}]

    ];
});





/*
 * DeductionGroupAdapter
 */

function DeductionGroupAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
}

DeductionGroupAdapter.inherits(AdapterBase);



DeductionGroupAdapter.method('getDataMapping', function() {
    return [
        "id",
        "name",
        "description"
    ];
});

DeductionGroupAdapter.method('getHeaders', function() {
    return [
        { "sTitle": "ID" ,"bVisible":false},
        { "sTitle": "Name" },
        { "sTitle": "Details" }
    ];
});

DeductionGroupAdapter.method('getFormFields', function() {
    return [
        [ "id", {"label":"ID","type":"hidden"}],
        [ "name", {"label":"Name","type":"text","validation":""}],
        [ "description", {"label":"Details","type":"textarea","validation":"none"}]
    ];
});


/*
 * PayslipTemplateAdapter
 */

function PayslipTemplateAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
}

PayslipTemplateAdapter.inherits(AdapterBase);



PayslipTemplateAdapter.method('getDataMapping', function() {
    return [
        "id",
        "name"
    ];
});

PayslipTemplateAdapter.method('getHeaders', function() {
    return [
        { "sTitle": "ID" ,"bVisible":false},
        { "sTitle": "Name" }
    ];
});

PayslipTemplateAdapter.method('getFormFields', function() {

    var payslipFields = [ "data", {"label":"Payslip Fields","type":"datagroup",
        "form":[
            [ "type", {"label":"Type","type":"select","sort":"none","source":[["Payroll Column","Payroll Column"],["Text","Text"],["Company Name","Company Name"],["Company Logo","Company Logo"], ["Separators","Separators"]]}],
            [ "payrollColumn", {"label":"Payroll Column","type":"select2","sort":"none","allow-null":true,"null-label":"None","remote-source":["PayrollColumn","id","name"]}],

            [ "label", {"label":"Label","type":"text","validation":"none"}],
            [ "text", {"label":"Text","type":"textarea","validation":"none"}],
            [ "status", {"label":"Status","type":"select","sort":"none","source":[["Show","Show"],["Hide","Hide"]]}]
        ],

        //"html":'<div id="#_id_#" class="panel panel-default">#_delete_##_edit_#<div class="panel-body"><table class="table table-striped"><tr><td>Type</td><td>#_type_#</td></tr><tr><td>Label</td><td>#_label_#</td></tr><tr><td>Text</td><td>#_text_#</td></tr><tr><td>Font Size</td><td>#_fontSize_#</td></tr><tr><td>Font Style</td><td>#_fontStyle_#</td></tr><tr><td>Font Color</td><td>#_fontColor_#</td></tr><tr><td>Status</td><td>#_status_#</td></tr></table> </div></div>',
        "html":'<div id="#_id_#" class="panel panel-default">#_delete_##_edit_#<div class="panel-body">#_type_# #_label_# <br/> #_text_#</div></div>',
        "validation":"none",
        "custom-validate-function":function (data){
            var res = {};
            res['valid'] = true;
            if(data.type == 'Payroll Column'){
                if(data.payrollColumn == "NULL"){
                    res['valid'] = false;
                    res['message'] = "Please select payroll column";
                }else{
                    data.payrollColumn == "NULL";
                }
            }else if(data.type == 'Text'){
                if(data.text == ""){
                    res['valid'] = false;
                    res['message'] = "Text can not be empty";
                }
            }

            res['params'] = data;
            return res;
        }
    }];

    return [
        [ "id", {"label":"ID","type":"hidden"}],
        [ "name", {"label":"Name","type":"text","validation":""}],
        payslipFields
    ];
});






