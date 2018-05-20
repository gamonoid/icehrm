function ConversationsAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
    this.topLimit = 0;
    this.bottomLimit = 0;
    this.conversations = [];
    this.type = null;
    this.container = null;
    this.loadMoreButton = null;
    this.start = 0;
    this.pageSize = 6;
    this.currentPage = 1;
    this.hasMoreData = true;
    this.searchTerm = "";
    this.searchInput = null;
    this.timer = null;
    this.timeoutDelay = 0;
    this.topLimitUpdated = false;
}

ConversationsAdapter.inherits(AdapterBase);

ConversationsAdapter.method('getDataMapping', function() {
    return [];
});

ConversationsAdapter.method('getHeaders', function() {
    return [];
});

ConversationsAdapter.method('getFormFields', function() {
    return [];
});

ConversationsAdapter.method('addConversation', function() {
    var object = this.validateCreateConversation();

    if (!object) {
        return false;
    }
    object.type = this.type;
    object.clienttime = (new Date()).getTimezoneOffset();
    var reqJson = JSON.stringify(object);
    var callBackData = [];
    callBackData['callBackData'] = [];
    callBackData['callBackSuccess'] = 'addConversationSuccessCallBack';
    callBackData['callBackFail'] = 'addConversationFailCallBack';

    this.customAction('addConversation','modules=conversations',reqJson,callBackData);
});

ConversationsAdapter.method('clearInputs', function() {
    $('#contentMessage').data('simplemde').value('');
    $('#attachment').html(this.gt('Attach File'));
    $('#attachment_remove').hide();
    $('#attachment_download').hide();
});

ConversationsAdapter.method('uploadPostAttachment', function() {
    var rand = this.generateRandom(14);
    showUploadDialog('attachment_'+rand,'Upload Attachment','Conversation',1,'attachment','html','name','all');
});

ConversationsAdapter.method('setConversationType', function(type) {
    this.type = type;
});

ConversationsAdapter.method('addConversationSuccessCallBack', function(callBackData) {
    this.clearInputs();
    this.getConversations(this.topLimit, this.pageSize, true);
});

ConversationsAdapter.method('addConversationFailCallBack', function(callBackData) {

});

ConversationsAdapter.method('deleteConversation', function(id) {
    var object = {id: id};
    var reqJson = JSON.stringify(object);
    var callBackData = [];
    callBackData['callBackData'] = [];
    callBackData['callBackSuccess'] = 'deleteConversationSuccessCallBack';
    callBackData['callBackFail'] = 'deleteConversationFailCallBack';

    this.customAction('deleteConversation','modules=conversations',reqJson,callBackData);
});



ConversationsAdapter.method('deleteConversationSuccessCallBack', function(callBackData) {
    $('#obj_'+callBackData).fadeOut();
});

ConversationsAdapter.method('deleteConversationFailCallBack', function(callBackData) {

});

ConversationsAdapter.method('toggleConversationSize', function(id) {
    $('#obj_'+id).find('.conversation-body').toggleClass('conversation-small');
    if ($('#obj_'+id).find('.conversation-body').hasClass('conversation-small')) {
        $('#obj_'+id).find('.conversation-expand-label').html(this.gt('Show More'));
    } else {
        $('#obj_'+id).find('.conversation-expand-label').html(this.gt('Show Less'));
    }
});


ConversationsAdapter.method('getConversations', function(start, limit, addToTop) {
    var reqJson = JSON.stringify({
        start: start,
        limit: limit,
        top: addToTop,
        type: this.type
    });
    var callBackData = [addToTop];
    callBackData['callBackData'] = callBackData;
    callBackData['callBackSuccess'] = 'getConversationsSuccessCallBack';
    callBackData['callBackFail'] = 'getConversationsFailCallBack';
    this.showConversationLoader();
    this.customAction('getConversations','modules=conversations',reqJson,callBackData);
});



ConversationsAdapter.method('getConversationsSuccessCallBack', function(addToTop, serverData) {
    this.hideLoader();
    var data = [];
    if(!addToTop && serverData.length > this.pageSize){
        this.hasMoreData = true;
        serverData.pop();
        this.loadMoreButton.removeAttr('disabled');
        this.loadMoreButton.show();
    } else if (!addToTop) {
        this.hasMoreData = false;
        this.loadMoreButton.hide();
    }

    if (!addToTop) {
        this.scrollToElementBottom(this.container);
    }

    for(var i=0;i<serverData.length;i++){
        data.push(this.preProcessTableData(serverData[i]));
    }
    this.sourceData = serverData;
    this.topLimitUpdated = false;
    for(var i=0;i<data.length;i++){
        this.renderObject(data[i], addToTop);
        if (data[i].timeint < this.bottomLimit || this.bottomLimit === 0) {
            this.bottomLimit = data[i].timeint;
        }

        if (data[i].timeint > this.topLimit || this.topLimit === 0) {
            this.topLimit = data[i].timeint;
            this.topLimitUpdated = true;
        }
    }
    this.hideConversationLoader();
});

ConversationsAdapter.method('getConversationsFailCallBack', function(callBackData) {
    this.hideLoader();
    this.hideConversationLoader();
    if (this.timer !== null) {
        clearTimeout(this.timer);
        this.timer = null;
    }
});


ConversationsAdapter.method('getObjectHTML', function(object) {
    var t = this.getCustomTemplate(this.getTemplateName());
    t = t.replace(new RegExp('#_id_#', 'g'), object.id);
    t = t.replace(new RegExp('#_message_#', 'g'), object.message);
    t = t.replace(new RegExp('#_employeeName_#', 'g'), object.employeeName);
    t = t.replace(new RegExp('#_employeeImage_#', 'g'), object.employeeImage);
    t = t.replace(new RegExp('#_date_#', 'g'), object.date);

    if (object.attachment !== '' && object.attachment !== null &&  object.attachment !== undefined) {
        var at = this.getCustomTemplate('attachment.html');
        at = at.replace(new RegExp('#_attachment_#', 'g'), object.attachment);
        at = at.replace(new RegExp('#_icon_#', 'g'), this.getIconByFileType(object.file.type));
        at = at.replace(new RegExp('#_color_#', 'g'), this.getColorByFileType(object.file.type));
        at = at.replace(new RegExp('#_name_#', 'g'), object.file.name);
        at = at.replace(new RegExp('#_size_#', 'g'), object.file.size_text);
        t = t.replace(new RegExp('#_attachment_#', 'g'), at);
    } else {
        t = t.replace(new RegExp('#_attachment_#', 'g'), '');
    }

    return t;

});

ConversationsAdapter.method('setPageSize', function(pageSize) {
    this.pageSize = pageSize;
});

ConversationsAdapter.method('addDomEvents', function(object) {

});

ConversationsAdapter.method('getTemplateName', function() {
    return 'conversation.html';
});

ConversationsAdapter.method('renderObject', function(object, addToTop) {

    var objDom = this.getObjectDom(object.id);

    var html = this.getObjectHTML(object);
    var domObj = $(html);


    if (objDom  !== undefined && objDom !== null) {
        objDom.replace(domObj);
    } else if (addToTop) {
        this.container.prepend(domObj);
        $('#obj_'+object.id).css('background-color', '#FFF8DC');
        $('#obj_'+object.id).fadeIn('slow');
        $('#obj_'+object.id).animate({backgroundColor: '#FFF'}, 'slow');
    } else {
        this.container.append(domObj);
        $('#obj_'+object.id).fadeIn('slow');
    }

    if (domObj.find('.conversation-body').prop('scrollHeight') > 290) {
        domObj.find('.conversation-expand').show();
    }

    if (object.actionDelete === 1) {
        domObj.find('.delete-button').show();
    }

    this.addDomEvents(domObj);
});

ConversationsAdapter.method('setContainer', function(container) {
    this.container = container;
});

ConversationsAdapter.method('setLoadMoreButton', function(loadMoreButton) {
    var that = this;
    this.loadMoreButton = loadMoreButton;
    this.loadMoreButton.off().on('click',function(){
            that.loadMoreButton.attr('disabled','disabled');
            that.loadMore([]);
        }
    );
});

ConversationsAdapter.method('showLoadError', function(msg) {
    $("#"+this.getTableName()+"_error").html(msg);
    $("#"+this.getTableName()+"_error").show();
});

ConversationsAdapter.method('hideLoadError', function() {
    $("#"+this.getTableName()+"_error").hide();
});

ConversationsAdapter.method('setSearchBox', function(searchInput) {
    var that = this;
    this.searchInput = searchInput;
    this.searchInput.off();
    this.searchInput.keydown(function(event){
        var val = $(this).val();
        if ( event.which == 13 ) {
            event.preventDefault();
            that.search([]);
        }else if(( event.which == 8 ||  event.which == 46) && val.length == 1 && that.searchTerm != ''){
            that.search([]);
        }
    });
});

ConversationsAdapter.method('getObjectDom', function(id) {
    obj =  this.container.find("#obj_"+id);
    if(obj.length){
        return obj;
    }
    return null;
});

ConversationsAdapter.method('loadMore', function(callBackData) {
    if(!this.hasMoreData){
        return;
    }
    this.currentPage++;
    this.get(callBackData, true);
});

ConversationsAdapter.method('get', function(callBackData, loadMore) {
    var that = this;

    this.hideLoadError();

    if(!loadMore){
        this.currentPage = 1;
        if(this.container != null){
            this.container.html('');
        }
        this.hasMoreData = true;
        this.tableData = [];
    }

    this.start = (this.currentPage === 1) ? 0 : this.bottomLimit;

    this.container = $("#"+this.getTableName()).find('.objectList');

    that.showLoader();

    this.getConversations(this.start, this.pageSize, false);

    if (this.timer === null && that.getTimeout() > 0) {
        this.timeoutDelay = 0;
        this.timer = setTimeout(function tick() {
            that.getConversations(that.topLimit, that.pageSize, true);
            that.timeoutDelay += that.getTimeout();
            if (that.topLimitUpdated) {
                that.timeoutDelay = 0;
            }
            that.timer = setTimeout(tick, that.getTimeout() + that.timeoutDelay);
        }, that.getTimeout() + that.timeoutDelay);
    }

});

ConversationsAdapter.method('getTimeout', function() {
    return 0;
});

ConversationsAdapter.method('getTimeoutUpper', function() {
    return 0;
});

ConversationsAdapter.method('showConversationLoader', function() {
    //Do nothing
});

ConversationsAdapter.method('hideConversationLoader', function() {
    //Do nothing
});

ConversationsAdapter.method('search', function(callBackData) {
    this.searchTerm = $("#"+this.getTableName()+"_search").val();

    this.get(callBackData);

});
