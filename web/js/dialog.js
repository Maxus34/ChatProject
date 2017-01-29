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

class DialogHandler {

    constructor () {
        this.dialogBlock = document.getElementById('dialog_block');
        if (this.dialogBlock == undefined) {
            return;
        }

        this.messagesList       = document.getElementById('messages_list');
        this.textArea           = document.getElementById('textarea');
        this.typingDiv          = document.getElementById('typing');
        this.sendMessageButton  = document.getElementById('send_message');
        this.dialogId           = this.sendMessageButton.getAttribute('data-dialog_id');

        this.eventListeners  = {};
        this.isLoading       = false;
        this.isTyping        = false;

        this.goToTheDialogBottom();
        this.addEventListeners();
    }

    addEventListeners () {
        let that = this;

        this.eventListeners['sendMessageButton'] =  function (e) {
            that.sendMessage.apply(that);
        }
        this.eventListeners['dialogBlock']       =  function (e) {
            if (e.target.scrollTop < 1) {
                that.loadOldMessages.apply(that);
            }
        }
        this.eventListeners['textArea']          =  function (e) {
            that.isTyping = true;
        }

        this.sendMessageButton .addEventListener('click',  this.eventListeners['sendMessageButton']);
        this.dialogBlock       .addEventListener('scroll', this.eventListeners['dialogBlock']);
        this.textArea          .addEventListener('keydown', this.eventListeners['textArea']);

        this.queryInterval = setInterval(function (e) {
            that.loadNews.apply(that);
        }, 1000);
        this.checkInterval = setInterval(function (e) {
            that.resetIsTyping.apply(that);
        }, 2000);
    }

    sendJsonByAjax (data, success, error, type = "POST") {
        return $.ajax({
            type : type,
            url  : "/chat/ajax",
            success : success,
            error   : error,
            data : data
        });
       /* return new Promise(function (success, error) {
            let xhr = new XMLHttpRequest();
            let formData = new FormData();
            formData.append('json_string', data);
            formData.append('_csrf', $('meta[name="csrf-token"]').attr("content"))
            xhr.open("POST", url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

            xhr.onload = function () {
                if (xhr.status === 200) {
                    success(xhr.responseText);
                } else {
                    error(xhr.statusText);
                }
            };

            xhr.onerror = function () {
                error(xhr.statusText);
            }

            xhr.send(formData);
        }); */
    }

    createMessage (message, from = 1) {
        let messageDiv = document.createElement('div');
        messageDiv.classList.add('message');
        if (from == 1) {
            messageDiv.classList.add('message-to');
        } else if (from == 0) {
            messageDiv.classList.add('message-from');
        } else {
            messageDiv.classList.add('message-error');
        }
        messageDiv.innerHTML = message;
        return messageDiv;
    }

    sendMessage () {
        function success (res) {
            try{
                var response = JSON.parse(res);
            } catch (e) {
                console.log(e);
                console.log(res);
                return;
            }

            that.messagesList.removeChild(list_node);
            that.messagesList.innerHTML += response.message;

            that.textArea.value = '';
            that.goToTheDialogBottom();
        }

        function error (res) {
            list_node.firstElementChild.innerHTML = "<h5 class='text-danger'>" + "Error: '" + res.statusText + "'</h5>";
            console.log(res);
        }

        var that = this;
        let text = this.textArea.value;
        if (text == ""){
            return;
        }
        let message = this.createMessage('<b>Sending...</b>');
        let list_node = document.createElement('li');
        list_node.append(message);

        this.messagesList.appendChild(list_node);
        this.goToTheDialogBottom();

        let data = JSON.stringify({
            "dialog" : {
                "dialog-id" : this.dialogId,
            },
            "send_message" : {
                "content"   : text
            },
        });

        this.sendJsonByAjax({"json_string" : data}, success, error, "POST")
        /* .catch(function (e) {
         console.log(e);
         }); */
    }

    loadNews () {
        function  checkNewMessages (response){
            if (response.new_messages === undefined)
                return;

            if (response.new_messages.length < 1)
                return;

            for (var i in response.new_messages){
                that.messagesList.appendChild( createElementsByHTML(response.new_messages[i])[0] );
            }
            that.goToTheDialogBottom();
        }
        function  checkIsTyping (response){
            if (response.typing === undefined)
                return;

            let typingText = "";

            let separator = (response.typing.length > 1) ? ", " : " ";

            for (var i in response.typing){
                typingText += response.typing[i] + separator;
            }

            if (response.typing.length > 0)
                typingText += " is typing...";

            if (response.typing.length > 1)
                typingText += " are typing...";

            that.typingDiv.innerHTML = typingText;
        }

        function  success (result) {
            try{
                var response = JSON.parse(result);
                // console.log(response);
            } catch (e) {
                console.log(result);
                console.log(e);
                return;
            }

            checkNewMessages(response);
            checkIsTyping(response);
        }
        function  error   (result) {
            console.log(result.status_text);
        }

        var that = this;
        let messagesFromUsersInDialog = this.messagesList.getElementsByClassName('message-from');
        let lastMessage = messagesFromUsersInDialog[messagesFromUsersInDialog.length - 1];
        let last_m_id = lastMessage.parentNode.getAttribute('data-id');

        let data = JSON.stringify({
            "dialog" : {
                "dialog-id" : this.dialogId
            },
            "load_new_messages" : {
                "last_message_id" : last_m_id,
            },
            "check_is_typing" : true,

            "set_is_typing" : {
                "is_typing" : this.isTyping
            }
        });

        this.sendJsonByAjax({"json_string" : data}, success, error, "POST")
        /* .catch(function (e) {
         console.log(e);
         }); */
    }

    loadOldMessages () {
        function success(res) {
            let request = JSON.parse(res);

            if (request.old_messages === "") {
                that.dialogBlock.removeEventListener('scroll', that.eventListeners['dialogBlock']);
                that.messagesList.innerHTML = "<h5 class='text-warning text-center'><b>начало диалога</b></h5>" + that.messagesList.innerHTML;
            }

            let scrollBottom = that.dialogBlock.scrollHeight - that.dialogBlock.scrollTop;
            that.messagesList.innerHTML = request.old_messages + that.messagesList.innerHTML;
            that.dialogBlock.scrollTop = that.dialogBlock.scrollHeight - scrollBottom;
            that.isLoading = false;
        }

        function error(res) {
            console.log(res);
            that.isLoading = false;
        }

        var that = this;
        let firstMessage = this.messagesList.firstElementChild;
        let firstMessageId = firstMessage.getAttribute('data-id');
        let dialogId = this.sendMessageButton.getAttribute('data-dialog_id');

        let data = JSON.stringify({
            "dialog" : {
                "dialog-id" : this.dialogId
            },
            'load_old_messages': {
                'first_message-id': firstMessageId,
            },
        });

       this.sendJsonByAjax({"json_string" : data}, success, error, "POST")
           /* .catch(function (e) {
                console.log(e);
            }); */
       this.isLoading = true;
    }

    handleNewMessages() {
        function scanNewMessages () {
            // возвращает object{my_messages:[...], messages:[...]}
            // my_messages - сообщения текущего пользователя, которые необходимо проверять на измененине is_new другими пользователими
            // messages - сообщения, которые необходимо отправить для обозначения is_new = 0;

            let messages_list = that.messagesList.getElementsByTagName('li');
            let messages_array = [];
            let my_messages_array = [];

            for(var i = 0; i < messages_list.length; i++){
                if ( (messages_list[i].dataset.new === "1") ){

                    if (messages_list[i].getElementsByTagName('div')[0].classList.contains('message-to'))
                        my_messages_array.push(messages_list[i].dataset.id);
                    else
                        messages_array.push(messages_list[i].dataset.id);
                }
            }

            return messages_array;
        }
        function setMessagesSeen(messages) {
            let need_to_change = [];
            let selector = "";
            for (var i =0; i < messages.length; i++){
               selector += 'li[data-id="'+messages[i]+'"]';
                if (i < messages.length-1)
                    selector += ",";
            }
            need_to_change = that.messagesList.querySelectorAll(selector);
            console.log(need_to_change);
            for (var i = 0; i < need_to_change.length; i++){
                need_to_change[i].dataset.new = "0";
                need_to_change[i].getElementsByTagName('div')[0].classList.remove('message-new');
            }
        }

        function success (result) {
            try{
                var response = JSON.parse(result);
                setMessagesSeen(response.seen_messages);

            } catch (e) {
                console.log(result);
                console.log(e);
                return;
            }
        }
        function error (result) {
            console.log(result.status_text);
        }

        var that = this;

        let new_messages = scanNewMessages();
        let my_new_messages = scanNewMessages();

        let data = JSON.stringify({
            "dialog" : {
                "dialog-id" : this.dialogId
            },
            "seen_messages" : {
                "messages" : new_messages,
            },
        });

        this.sendJsonByAjax({"json_string" : data}, success, error, "POST")
        /* .catch(function (e) {
         console.log(e);
         }); */
    }


    goToTheDialogBottom () {
        this.dialogBlock.scrollTop = this.dialogBlock.scrollHeight;
    }

    resetIsTyping () {
        this.isTyping = false;
        this.typingDiv.innerHTML = '';
    }
}

var dialog_h = new DialogHandler();