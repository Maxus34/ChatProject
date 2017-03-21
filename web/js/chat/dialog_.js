"use strict";

var createElementsByHTML = (function(){
    var div = document.createElement("div");
    return function ( html ) {
        var res = [];
        div.innerHTML = html;
        while ( div.firstChild ) {
            res[ res.length ] = div.removeChild( div.firstChild );
        }
        return res;
    };
})();


class JsonDataHandler {
    constructor (url, interval, dialog_id){
        this.url      = url;
        this.interval = interval;

        this.data                 = {};
        this.dialog_id            = dialog_id;
        this.waiting_for_response = false;

        this.disposable_callbacks = [];
        this.permanent_callbacks  = [];
        this.error_callbacks      = [];

        var that      = this;
        this.interval = setInterval(function (e) {
            //that.sendData();
        }, interval);
    }

    addData (data) {
        this.data = Object.assign(this.data, data);
    }

    addCallback (callback, permanent = false){
        if (permanent){
            this.permanent_callbacks.push(callback);

        } else {
            this.disposable_callbacks.push(callback);
        }
    }

    addErrorCallback(callback){
        this.error_callbacks.push(callback);
    }

    sendData () {
        function success (response) {
            that.waiting_for_response = false;

            try {
                var result = JSON.parse(response);
            } catch (e) {
                console.log("SendData Error");
                console.log(response);
                return;
            }


            for (let i in that.disposable_callbacks) {
                that.disposable_callbacks[i](result);
            }

            for (let i in that.permanent_callbacks) {
                that.permanent_callbacks[i](result);
            }

            that.disposable_callbacks = [];
            that.data = {};
        }

        function error (response) {
            console.log("Error");
            console.log(response);
            that.waiting_for_response = false;

            for (var i in that.error_callbacks){
                that.error_callbacks[i](response);
            }

            that.error_callbacks = [];
            that.disposable_callbacks = [];
            that.data = {};
        }

        if (this.waiting_for_response){
            console.log("waiting for response...");
            return;
        }


        this.data = Object.assign(this.data, {
            "dialog" : {
                "dialog-id" : this.dialog_id
            }
        });

        let data = {
            "json_string" : JSON.stringify(this.data)
        };

        var that = this;

        try{
            $.ajax({
                type : "POST",
                url : this.url,
                success : success,
                error : error,
                data : data
            });
        } catch (e) {
            console.log(e);
        }


        this.waiting_for_response = true;
    }
}

class FileHandler {
    constructor () {
        this.input          = document.createElement('input');
        this.input.type     = 'file';

        this.files_list     = document.getElementById('files-list');
        this.addImageButton = document.getElementById('upload-file');

        this.param = document.querySelectorAll('meta[name=csrf-param]')[0].getAttribute('content');
        this.token = document.querySelectorAll('meta[name=csrf-token]')[0].getAttribute('content');

        this.files          = [];
        this.fileReader     = new FileReader();

        this.isLoading      = false;

        this.addEventListeners();
        this.defineFileIcons();
    }

    addEventListeners(){
        this.addImageButton.onclick = (e) => {
            e.preventDefault(e);
            this.input.click();
        }

        this.input.onchange = (e) => {
            console.log(e.target.files);
            if (e.target.files.length > 0){
                this.handleFile(e.target.files[0]);
            }
        }

        this.files_list.onclick = (e) => {

        }
    }

    handleFile(file){
        function sendFile(file_obj){
            let file = file_obj['file'];

            let xhr = new XMLHttpRequest();

            let formData = new FormData();
            formData.append(that.param, that.token);
            formData.append('file', file, file.name);

            xhr.open("POST", 'ajax/upload-file', true);

            xhr.upload.onloadstart =  (e) => {
                file_obj.li.appendChild(file_obj.icon);
                file_obj.li.appendChild(file_obj.div);
                file_obj.li.appendChild(file_obj.progress);

                file_obj.progress.setAttribute('max', e.total);

                that.files_list.appendChild(file_obj.li);
                file_obj.isLoading = true;
            };

            xhr.upload.onloadend = (e) => {
                file_obj.progress.setAttribute('value', e.total);
                file_obj.isLoading = false;
            };

            xhr.upload.onprogress = (e) => {
                file_obj.progress.setAttribute('value', e.loaded);
            };

            xhr.onload = xhr.onerror = (e) => {
                if (e.target.status === 200){

                    let result = JSON.parse(e.target.responseText);
                    if (result.error){
                        file_obj.div.classList.add('text-danger');
                        //file_obj.progress.style.backgroundColor = "#f00";
                        file_obj.div.innerHTML += " -error";
                        file_obj.error = true;

                    } else {
                        file_obj.id = result.file.id;
                        file_obj.div.classList.add('text-success');
                       // file_obj.progress.classList.add('success');
                    }

                } else {
                    file_obj.div.classList.add('text-danger');
                    file_obj.div.innerHTML += " -error";
                    //file_obj.progress.style.backgroundColor = "#f00";
                    file_obj.error = true;
                }

            }

            xhr.send(formData);
        }

        var that     = this;

        let file_obj = {
            file      : file,
            icon      : this.getFileIcon(file),
            li        : document.createElement('li'),
            div       : document.createElement('div'),
            progress  : document.createElement('progress'),

            error     : false,
            isLoading : false
        };

        file_obj.div.innerHTML = file.name;

        this.files.push(file_obj);
        sendFile(file_obj);
    }

    defineFileIcons () {
        this.icons = {
            'image' : 'fa-file-image-o',
            'audio' : 'fa-file-audio-o',
            'text'  : 'fa-file-text-o',
            'file'  : 'fa-file-o',
            'video' : 'fa-film'
        }
    }

    getFileIcon(file){
        let fileIcon = document.createElement('i');
        fileIcon.classList.add('fa');


        switch(file.type.split('/')[0]){
            case 'image':
                fileIcon.classList.add(this.icons['image']);
                break;

            case 'audio':
                fileIcon.classList.add(this.icons['audio']);
                break;

            case 'text':
                fileIcon.classList.add(this.icons['text']);
                break;

            default:
                fileIcon.classList.add(this.icons['file']);
        }

        return fileIcon;
    }

    removeFile(){

    }
}

class MessagesHandler {
    constructor (dataHandler) {

        this.text_area           = document.getElementById('textarea');
        this.messages_list       = document.getElementById('messages_list');
        this.send_message_button = document.getElementById('send_message');
        this.del_messages_button = document.getElementById('delete_messages');
        this.reset_selected_mess = document.getElementById('reset_delete_messages');


        this.monitored_messages  = {outgoing_messages : [ ], incoming_messages : [ ]};
        this.selected_messages   = { };
        this.messages_for_send   = [ ];

        this.eventListeners      = { };

        this.dataHandler = dataHandler;

        this.addEventListeners();
        DialogHandler.goToTheDialogBottom();
    }

    addEventListeners () {
        let that = this;

        this.eventListeners['add_message_to_send']     =  function (e) {
            that.addMessageToSend.apply(that);
        }
        this.eventListeners['body_scroll']             =  function (e) {
            if (e.target.body.scrollTop < 1) {
                that.loadOldMessages.apply(that);
            }
        }
        this.eventListeners['select_message']          =  function (e) {
            let li = e.target.closest('li');
            if (!li)
                return;
            that.selectMessage.apply(that, [li]);
        }
        this.eventListeners['delete_messages']         =  function (e) {
            that.deleteMessages.apply(that);
        }
        this.eventListeners['reset_selected_messages'] =  function (e) {
            that.resetSelectedMessages.apply(that);
        }

        this.del_messages_button  .addEventListener('click',   this.eventListeners['delete_messages']);
        this.send_message_button  .addEventListener('click',   this.eventListeners['add_message_to_send']);
        this.reset_selected_mess  .addEventListener('click',   this.eventListeners['reset_selected_messages']);
        this.messages_list        .addEventListener('click',   this.eventListeners['select_message']);
        document                  .addEventListener('scroll',  this.eventListeners['body_scroll']);


        this.interval = setInterval(function (){
            that.sendMessages.apply(that);
            that.searchMessagesForSeen.apply(that);
            that.checkNewIncomingMessages.apply(that);
            that.handleSeenMessages.apply(that);
        }, 1100);
    }

    sendMessages() {
        function callback_sendMessages_success (result) {
            that.is_sending_m = false;

            if (!result.messages_for_send)
                return;

            for( var i = 0; i < result.messages_for_send.length; i++){
                let is_sending_message = that.messages_list.querySelectorAll("li[data-id='" + result.messages_for_send[i].pseudo_id + "']")[0];

                if (result.messages_for_send[i].success){
                    that.messages_list.removeChild(is_sending_message);

                    that.messages_list.innerHTML += result.messages_for_send[i].message;
                }

                that.text_area.value = '';
                DialogHandler.goToTheDialogBottom();

                that.messages_for_send = [];
            }
        }

        function callback_sendMessages_error(result){
            let messages = data.messages_for_send;
            console.log("Send Messages Error");

            for (var i in messages){
                let is_sending_message = that.messages_list.querySelectorAll("li[data-id='" + messages[i].pseudo_id + "']")[0];
                console.log(is_sending_message);
                is_sending_message.innerHTML = "Error. Please try later";
            }

        }

        if (this.is_sending_m)
            return;

        var that = this;
        var data = {
            "messages_for_send" : this.messages_for_send
        }

        this.dataHandler.addData(data);
        this.dataHandler.addCallback(callback_sendMessages_success, false);
        this.dataHandler.addErrorCallback(callback_sendMessages_error)
        this.is_sending_m = true;
    }

    addMessageToSend () {
        let text = this.text_area.value;
        console.log(text);
        if (text == "")
            return;

        var message   = this.createMessage('<i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i><span class="sr-only">Loading...</span>Sending...', 1);
        var pseudo_id = "@" + Math.round(Math.random() * 10000);
            message.setAttribute('data-id', pseudo_id);
        this.messages_list.appendChild(message);
        DialogHandler.goToTheDialogBottom();

        this.messages_for_send.push({
            text      : text,
            pseudo_id : pseudo_id
        });
    }

    loadOldMessages () {
        function callback_load_old(result){
            that.is_loading_old = false;

            if (typeof result.load_old_messages == "undefined")
                return;

            that.messages_list.removeChild(isLoadingMessage);

            if (result.load_old_messages.length == 0) {
                document.removeEventListener('scroll', that.eventListeners['body_scroll']);
                that.messages_list.innerHTML = "<h5 class='text-warning text-center'><b>начало диалога</b></h5>" + that.messages_list.innerHTML;
            }

            let scrollBottom = document.body.scrollHeight - document.body.scrollTop;
            for (var i = result.load_old_messages.length - 1; i >= 0; i--){
                that.messages_list.insertBefore(createElementsByHTML(result.load_old_messages[i])[0], that.messages_list.firstElementChild);
            }

            document.body.scrollTop = document.body.scrollHeight - scrollBottom;
        }

        if (this.is_loading_old)
            return;

        var that = this;
        let firstMessage = this.messages_list.firstElementChild;

        var isLoadingMessage = document.createElement("li");
            isLoadingMessage.innerHTML = '<center><i class="fa fa-refresh fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span></center>';
        this.messages_list.insertBefore(isLoadingMessage, this.messages_list.firstElementChild);




        if (!firstMessage)
            return;

        let data = {
            load_old_messages : {
                "first_message-id" : firstMessage.getAttribute('data-id'),
            }
        };

        this.dataHandler.addData(data);
        this.dataHandler.addCallback(callback_load_old, false);

        this.is_loading_old = true;
    }

    handleSeenMessages(){
        function  setMessagesSeen(messages){
            if (messages.length == 0)
                return;

            let need_to_change = [];
            let selector = "";
            for (var i =0; i < messages.length; i++){
                selector += 'li[data-id="'+messages[i]+'"]';
                if (i < messages.length-1)
                    selector += ",";
            }
            need_to_change = that.messages_list.querySelectorAll(selector);
            for (var i = 0; i < need_to_change.length; i++){
                need_to_change[i].dataset.new = "0";
                need_to_change[i].getElementsByTagName('div')[0].classList.remove('message-new');
            }
        }

        function callback_sn (result) {
            that.is_loading_seen = false;

            if (result.seen_messages != undefined)
                setMessagesSeen(result.seen_messages);

            if (result.check_is_seen != undefined)
                setMessagesSeen(result.check_is_seen);

            that.resetMonitoredMessages.apply(that);
        }

        if (this.is_loading_seen)
            return;

        var that = this;
        var data = {
            "seen_messages" : {
                messages : this.monitored_messages.incoming_messages,
            },
            "check_is_seen" : {
                messages : this.monitored_messages.outgoing_messages,
            }
        }

        this.dataHandler.addData(data);
        this.dataHandler.addCallback(callback_sn, false);

        this.is_loading_seen = true;
    }

    deleteMessages () {
        function callback_dm (result) {
            that.is_loading_dm = false;

            if (!result.deleted_messages)
                return;

            let selector = "";
            for (var i =0; i < result.deleted_messages.length; i++){
                selector += 'li[data-id="' + result.deleted_messages[i] + '"]';
                if (i < result.deleted_messages.length - 1)
                    selector += ",";
            }

            let messages = that.messages_list.querySelectorAll(selector);

            for (var i = 0; i < messages.length; i++){
                that.messages_list.removeChild(messages[i]);
            }

            let div1 = document.getElementById('dialog_header_1');
            let div2 = document.getElementById('dialog_header_2');
            div1.style.display = 'block';
            div2.style.display = 'none';
        }

        if (this.is_loading_dm)
            return;

        if (Object.keys(this.selected_messages).length > 0) {
            var messages = [];
            for (var i in this.selected_messages) {
                messages.push(i);
            }
        } else {
            return;
        }

        var data = {
            "delete_messages" : {
                "messages" : messages
            }
        }

        this.dataHandler.addData(data);
        this.dataHandler.addCallback(callback_dm, false);

        var that = this;
        this.is_loading_dm = true;
    }

    searchMessagesForSeen () {
        let messages_list = this.messages_list.getElementsByTagName('li');
        let messages_array = [];
        let my_messages_array = [];

        for(var i = 0; i < messages_list.length; i++){
            if ( (messages_list[i].dataset.new === "1") ){

                if (messages_list[i].classList.contains('message-outgoing'))
                    my_messages_array.push(messages_list[i].dataset.id);
                else
                    messages_array.push(messages_list[i].dataset.id);
            }
        }

        this.monitored_messages =  {
            outgoing_messages : my_messages_array,
            incoming_messages : messages_array
        };
    }

    checkNewIncomingMessages(){
        function getLastMessageId(){
            let messages = that.messages_list.getElementsByClassName('message-incoming');
            let last_m_id = null;

            if (messages.length > 0){
                last_m_id = messages[messages.length - 1].parentNode.getAttribute('data-id');
            } else {
                messages = that.messages_list.getElementsByClassName('message-outgoing');
                if (messages.length > 0){
                    last_m_id = messages[messages.length-1].parentNode.getAttribute('data-id');
                }
            }

            return last_m_id;
        }

        function callback_cn(result) {
            that.is_loading_new = false;

            if ( ! result.load_new_messages
                || result.load_new_messages.length < 1)
                return;

            for (var i in result.load_new_messages){
                that.messages_list.appendChild( createElementsByHTML(result.load_new_messages[i])[0] );
            }

            DialogHandler.goToTheDialogBottom();
        }

        if (this.is_loading_new)
            return;

        var that = this;
        var last_message_id = getLastMessageId();

        var data = {
            "load_new_messages" : {
                "first_message-id" : last_message_id
            }
        }

        this.dataHandler.addData(data);
        this.dataHandler.addCallback(callback_cn, false);

        this.is_loading_new = true;
    }


    resetMonitoredMessages() {
        this.monitored_messages = {incoming_messages : [], outgoing_messages : []};
    }

    createMessage (text, type = 0) {
        let list_node = document.createElement('li');
        list_node.classList.add('message');

        switch(type){
            case 0 : list_node.classList.add('message-incoming');
                break;
            case 1 : list_node.classList.add('message-outgoing');
                break;
            case 2 : list_node.classList.add('message-error');
                break;
            default : list_node.classList.add('message-info');
        }

        list_node.innerHTML = text;

        return list_node;
    }

    selectMessage (li) {
        function showDialogHeader(number){
            switch (number){
                case 1:
                    dialog_header_1.style.display = 'block';
                    dialog_header_2.style.display =  'none';
                    break;


                case 2:
                    dialog_header_1.style.display = 'none';
                    dialog_header_2.style.display = 'block';
                    break;
            }
        }

        if (this.selected_messages == undefined)
            this.selected_messages = {};

        let id = li.getAttribute('data-id');
        if (!this.selected_messages[id]){
            this.selected_messages[id] = true;
            li.classList.add('message-selected');
        } else
        if (this.selected_messages[id] == true){
            delete this.selected_messages[id];
            li.classList.remove('message-selected');
        }


        let dialog_header_1 = document.getElementById('dialog_header_1');
        let dialog_header_2 = document.getElementById('dialog_header_2');
        let delete_button = document.getElementById('delete_messages');

        if (Object.keys(this.selected_messages).length > 0){
            showDialogHeader(2);
            delete_button.innerHTML = "Delete" + Object.keys(this.selected_messages).length + " messages.";
        } else {
            delete_button.innerHTML = "Nothing to delete.";
            showDialogHeader(1);
        }

    }

    resetSelectedMessages () {
        var selector = "";
        let selected_messages = Object.keys(this.selected_messages);
        this.selected_messages = {};

        for (let i = 0; i < selected_messages.length; i++){
            selector += "li[data-id='" + selected_messages[i] + "']";

            if (i < selected_messages.length - 1){
                selector += ",";
            }
        }

        let messages = this.messages_list.querySelectorAll(selector);

        for (let i = 0; i < selected_messages.length; i++){
            messages[i].classList.remove('message-selected');
        }


        let dialog_header_1 = document.getElementById('dialog_header_1');
        let dialog_header_2 = document.getElementById('dialog_header_2');

        dialog_header_1.style.display = 'block';
        dialog_header_2.style.display =  'none';
    }
}

class DialogHandler {

    constructor () {
        this.dialogBlock = document.getElementById('dialog_block');
        if (this.dialogBlock == undefined) {
            return;
        }

        // activeUser - variable from view.php
        this.isActiveUser = activeUser || 0;

        this.text_area          = document.getElementById('textarea');
        this.dialogPropertiesLi = document.getElementById('dialog_properties');
        this.typingDiv          = document.getElementById('typing');


        this.dialogId           = document.getElementById('send_message').getAttribute('data-dialog_id');
        this.eventListeners     = {};
        this.isTyping           = false;

        this.dataHandler    = new JsonDataHandler('/chat/ajax', 1200, this.dialogId);
        this.messageHandler = new MessagesHandler(this.dataHandler);

        this.addEventListeners();
        DialogHandler.goToTheDialogBottom();

        var that = this;
        this.interval = setInterval(function () {
            that.handleIsTyping.apply(that);
        }, 1500);
    }

    addEventListeners () {
        let that = this;

        // Event listeners declaration
        this.eventListeners['dialogPropertiesLi'] =  function (e) {
            that.showDialogProperties.apply(that);
        }
        this.eventListeners['textArea']           =  function (e) {
            that.isTyping = true;
        }

        //Event listeners adding.
        this.text_area          .addEventListener('keydown', this.eventListeners['textArea']);
        this.dialogPropertiesLi .addEventListener('click',   this.eventListeners['dialogPropertiesLi']);

        this.interval = setInterval (function () {
            that.handleIsTyping.apply(that);
        }, 1000);
    }

    sendJsonByAjax (data, success, error, type = "POST") {
        return $.ajax({
            type : type,
            url  : "/chat/ajax",
            success : success,
            error   : error,
            data : data
        });
    }

    handleIsTyping() {
        function callback_is_t(result){
            that.is_loading_is_t = false;

            if (!result.typing)
                return;

            if (result.typing.length === 0){
                that.resetIsTyping.apply(that);
                return;
            }

            let typingText = "";
            let separator = (result.typing.length > 1) ? ", " : "";

            for (var i in result.typing){
                typingText += result.typing[i] + separator;
            }

            if (result.typing.length < 2)
                typingText += " is typing now...";
            else
                typingText += " are typing now...";

            if (that.typingDiv.innerHTML != typingText)
                that.typingDiv.innerHTML  = typingText;
        }

        if (this.is_loading_is_t)
            return;

        var that = this;

        let data = {
            "check_is_typing" : true,
            "set_is_typing" : {
                "is_typing" : this.isTyping
            },
        }

        this.dataHandler.addData(data);
        this.dataHandler.addCallback(callback_is_t, false);

        this.isTyping = false;
        this.is_loading_is_t = true;
    }

    showDialogProperties(){
        function callback_dp (result) {
            if (! result.form)
                return;

            $("#chat_modal .modal-body").html(result.form);
            $("#chat_modal").modal();
        }

        var that = this;
        var data = {
            'dialog_properties' : true,
        };

        this.dataHandler.addData(data);
        this.dataHandler.addCallback(callback_dp, false);
    }

    resetIsTyping () {
        this.isTyping = false;
        this.typingDiv.innerHTML = '';
    }

    static goToTheDialogBottom () {
        let newScrollTop = document.body.scrollHeight - document.body.clientHeight;
        document.body.scrollTop = newScrollTop;
    }

}


var dialog_h = new DialogHandler();

var file_h   = new FileHandler();