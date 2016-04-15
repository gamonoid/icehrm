/**
 * Author: Thilina Hasantha
 */

/**
 * SalaryComponentTypeAdapter
 */

function SalaryComponentTypeAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
}

SalaryComponentTypeAdapter.inherits(AdapterBase);



SalaryComponentTypeAdapter.method('getDataMapping', function() {
    return [
        "id",
        "code",
        "name"
    ];
});

SalaryComponentTypeAdapter.method('getHeaders', function() {
    return [
        { "sTitle": "ID" ,"bVisible":false},
        { "sTitle": "Code" },
        { "sTitle": "Name"}
    ];
});

SalaryComponentTypeAdapter.method('getFormFields', function() {
    return [
        [ "id", {"label":"ID","type":"hidden"}],
        [ "code", {"label":"Code","type":"text","validation":""}],
        [ "name", {"label":"Name","type":"text","validation":""}]
    ];
});


/**
 * SalaryComponentAdapter
 */

function SalaryComponentAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
}

SalaryComponentAdapter.inherits(AdapterBase);



SalaryComponentAdapter.method('getDataMapping', function() {
    return [
        "id",
        "name",
        "componentType",
        "details"
    ];
});

SalaryComponentAdapter.method('getHeaders', function() {
    return [
        { "sTitle": "ID" ,"bVisible":false},
        { "sTitle": "Name" },
        { "sTitle": "Salary Component Type" },
        { "sTitle": "Details"}
    ];
});

SalaryComponentAdapter.method('getFormFields', function() {
    return [
        [ "id", {"label":"ID","type":"hidden"}],
        [ "name", {"label":"Name","type":"text","validation":""}],
        [ "componentType", {"label":"Salary Component Type","type":"select2","remote-source":["SalaryComponentType","id","name"]}],
        [ "details", {"label":"Details","type":"textarea","validation":"none"}]
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
            if(lowerCondition != 'No Lower Limit'){
                data.lowerLimit = 0;
            }
            if(upperCondition != 'No Upper Limit'){
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
DeductionAdapter.method('doCustomValidation', function(params) {
    if(params.type == "Fixed"){
        return null;
    }
    if(params.percentage_type == "On Component Type"){
        params.component = "NULL";
        if(params.componentType == "NULL"){
            return "Salary component type should be selected";
        }
    }else if(params.percentage_type == "On Component"){
        params.componentType = "NULL";
        if(params.component == "NULL"){
            return "Salary component should be selected";
        }
    }

    return null;
});
*/
/*
DeductionAdapter.method('postRenderForm', function(object, $tempDomObj) {

    $tempDomObj.find("#field_componentType").hide();
    $tempDomObj.find("#field_percentage_type").hide();
    $tempDomObj.find("#field_component").hide();


    $tempDomObj.find("#percentage_type").off().on('change',function(e){
        if(e.val == "On Component"){
            $("#componentType").hide();
            $("#component").show();
        }else{
            $("#componentType").show();
            $("#component").hide();
        }
    });

    $tempDomObj.find("#type").off().on('change',function(e){
        if(e.val == "Fixed"){
            $("#componentType").hide();
            $("#percentage_type").hide();
            $("#component").hide();
        }else{
            $("#percentage_type").show();
            if($("#percentage_type").select2('data').id == 'On Component'){
                $("#field_componentType").hide();
                $("#field_component").show();
            }else{
                $("#field_componentType").show();
                $("#field_component").hide();
            }

        }
    });
});
*/





/*
 * EmployeeSalaryAdapter
 */

function EmployeeSalaryAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
}

EmployeeSalaryAdapter.inherits(AdapterBase);



EmployeeSalaryAdapter.method('getDataMapping', function() {
    return [
        "id",
        "employee",
        "component",
        "amount",
        "details"
    ];
});

EmployeeSalaryAdapter.method('getHeaders', function() {
    return [
        { "sTitle": "ID" ,"bVisible":false},
        { "sTitle": "Employee" },
        { "sTitle": "Salary Component" },
        { "sTitle": "Amount"},
        { "sTitle": "Details"}
    ];
});

EmployeeSalaryAdapter.method('getFormFields', function() {
    return [
        [ "id", {"label":"ID","type":"hidden"}],
        [ "employee", {"label":"Employee","type":"select2","remote-source":["Employee","id","first_name+last_name"]}],
        [ "component", {"label":"Salary Component","type":"select2","remote-source":["SalaryComponent","id","name"]}],
        [ "amount", {"label":"Amount","type":"text","validation":"float"}],
        [ "details", {"label":"Details","type":"textarea","validation":"none"}]
    ];
});

EmployeeSalaryAdapter.method('getFilters', function() {
    return [
        [ "employee", {"label":"Employee","type":"select2","remote-source":["Employee","id","first_name+last_name"]}]

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





