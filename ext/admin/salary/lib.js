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



