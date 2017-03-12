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

        var that      = this;
        this.interval = setInterval(function (e) {
            that.sendData();
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
            console.log(response);
            that.waiting_for_response = false;
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

       $.ajax({
            type : "POST",
            url : this.url,
            success : success,
            error : error,
            data : data
        });

        this.waiting_for_response = true;
    }
}

class MessagesHandler {
    constructor (dataHandler) {

        this.text_area           = document.getElementById('textarea');
        this.messages_list       = document.getElementById('messages_list');
        this.send_message_button = document.getElementById('send_message');
        this.del_messages_button = document.getElementById('delete_messages');

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

        this.eventListeners['add_message_to_send']  =  function (e) {
            that.addMessageToSend.apply(that);
        }
        this.eventListeners['body_scroll']          =  function (e) {
            if (e.target.body.scrollTop < 1) {
                that.loadOldMessages.apply(that);
            }
        }
        this.eventListeners['select_message']       =  function (e) {
            let li = e.target.closest('li');
            if (!li)
                return;
            that.selectMessage.apply(that, [li]);
        }
        this.eventListeners['delete_messages']      =  function (e) {
            that.deleteMessages.apply(that);
        }

        this.del_messages_button  .addEventListener('click',   this.eventListeners['delete_messages']);
        this.send_message_button  .addEventListener('click',   this.eventListeners['add_message_to_send']);
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
        function callback_sm (result) {
            that.is_sending_m = false;

            if (!result.messages_for_send)
                return;

            for( var i = 0; i < result.messages_for_send.length; i++){
                let is_sending_message = that.messages_list.querySelectorAll("li[data-id='" + result.messages_for_send[i].pseudo_id + "']")[0];
                console.log(is_sending_message);


                if (result.messages_for_send[i].success){
                    that.messages_list.removeChild(is_sending_message);

                    let message = createElementsByHTML(result.messages_for_send[i].message)[0];

                    if (message.getAttribute('data-user_id') != that.messages_list.lastElementChild.getAttribute('data-user_id')){
                        let user_block = document.createElement('h5');
                        user_block.classList.add('message-author');
                        user_block.classList.add('message-outgoing');
                        user_block.innerHTML = users[message.getAttribute('data-user_id')];
                        message.insertBefore(user_block, message.firstElementChild);
                    }

                    that.messages_list.appendChild(message);
                }

                that.text_area.value = '';
                DialogHandler.goToTheDialogBottom();

                that.messages_for_send = [];
            }
        }

        if (this.is_sending_m)
            return;

        var that = this;

        let data = {
            "messages_for_send" : this.messages_for_send
        }

        this.dataHandler.addData(data);
        this.dataHandler.addCallback(callback_sm, false);
        this.is_sending_m = true;
    }

    addMessageToSend () {
        let text = this.text_area.value;
        console.log(text);
        if (text == "")
            return;

        var message   = this.createMessage('Sending...', 1);
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
        function callback_lo(result){
            function appendMessages (list, messages_html) {
                let messages = [];
                for (let i = messages_html.length -1 ; i >= 0; i--){
                    messages.push(createElementsByHTML(messages_html[i])[0]);
                }

                for (let i =  messages.length-1; i >=0 ; i--){
                    if (i === messages.length-1){
                        let user_block = document.createElement('h5');
                        user_block.classList.add('message-author');
                        if (messages[i].classList.contains('message-outgoing')){
                            user_block.classList.add('message-outgoing');
                        } else {
                            user_block.classList.add('message-incoming');
                        }

                        user_block.innerHTML = users[messages[i].getAttribute('data-user_id')];
                        messages[i].insertBefore(user_block, messages[i].firstElementChild);
                    } else {
                        if (messages[i].getAttribute('data-user_id') != messages[i+1].getAttribute('data-user_id')){
                            let user_block = document.createElement('h5');
                            user_block.classList.add('message-author');
                            if (messages[i].firstElementChild.classList.contains('message-outgoing')){
                                user_block.classList.add('message-outgoing');
                            } else {
                                user_block.classList.add('message-incoming');
                            }

                            user_block.innerHTML = users[messages[i].getAttribute('data-user_id')];
                            messages[i].insertBefore(user_block, messages[i].firstElementChild);
                        }
                    }
                }

                for (let i = 0; i < messages.length; i++)
                    list.insertBefore(messages[i], list.firstElementChild);
            }

            that.is_loading_old = false;

            if (typeof result.load_old_messages == "undefined")
                return;

            if (result.load_old_messages.length == 0) {
                document.removeEventListener('scroll', that.eventListeners['body_scroll']);
                that.messages_list.innerHTML = "<h5 class='text-warning text-center'><b>начало диалога</b></h5>" + that.messages_list.innerHTML;
            }

            let scrollBottom = document.body.scrollHeight - document.body.scrollTop;

            appendMessages(that.messages_list, result.load_old_messages);

            document.body.scrollTop = document.body.scrollHeight - scrollBottom;
        }

        if (this.is_loading_old)
            return;

        var that = this;
        let firstMessage = this.messages_list.firstElementChild;

        if (!firstMessage)
            return;

        let data = {
            load_old_messages : {
                "first_message-id" : firstMessage.getAttribute('data-id'),
            }
        };

        this.dataHandler.addData(data);
        this.dataHandler.addCallback(callback_lo, false);

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

                if (messages_list[i].getElementsByTagName('div')[0].classList.contains('message-outgoing'))
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
                let message = createElementsByHTML(result.load_new_messages[i])[0];

                if (message.getAttribute('data-user_id') != that.messages_list.lastElementChild.getAttribute('data-user_id')){
                    let user_block = document.createElement('h5');
                    user_block.classList.add('message-author');
                    user_block.classList.add('message-incoming');
                    user_block.innerHTML = users[message.getAttribute('data-user_id')];
                    message.insertBefore(user_block, message.firstElementChild);
                }

                that.messages_list.appendChild(message);
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


    resetMonitoredMessages(){
        // TODO rewrite to incoming_messages, outgoing_messages
        this.monitored_messages = {my_messages : [], messages : []};
    }

    createMessage (text, type = 0) {
        let list_node = document.createElement('li');
        let messageDiv = document.createElement('div');
        messageDiv.classList.add('message');

        switch(type){
            case 0 : messageDiv.classList.add('message-incoming');
                break;
            case 1 : messageDiv.classList.add('message-outgoing');
                break;
            case 2 : messageDiv.classList.add('message-error');
                break;
            default : messageDiv.classList.add('message-info');
        }

        messageDiv.innerHTML = text;
        list_node.appendChild(messageDiv);

        return list_node;
    }

    selectMessage (li) {
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


        let div1 = document.getElementById('dialog_header_1');
        let div2 = document.getElementById('dialog_header_2');
        let div3 = document.getElementById('delete_messages');

        if (Object.keys(this.selected_messages).length > 0){
            div1.style.display = 'none';
            div2.style.display = 'block';
            div3.innerHTML = "<center><a class='btn-sm btn-warning'>" + "Delete " + Object.keys(this.selected_messages).length + " messages" + "</a></center>";
        } else {
            div3.innerHTML = "";
            div1.style.display = 'block';
            div2.style.display =  'none';
        }


        //console.log(this.selected_messages);
        //console.log(Object.keys(this.selected_messages).length);
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

