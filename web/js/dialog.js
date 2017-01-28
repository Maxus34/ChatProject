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

class Dialog {

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
        }, 5000);
    }

    sendAjax (url, data, success, error, type = "POST") {
        return $.ajax({
            type : type,
            url  : url,
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
                return;
            }

            that.messagesList.removeChild(list_node);
            that.messagesList.innerHTML += response.message;

            that.textArea.value = '';
            that.goToTheDialogBottom();
        }

        function error (res) {
            list_node.firstElementChild.innerHTML = "<h5 class='text-danger'>" + "Error: '" + res.statusText + "'</h5>";
        }

        var that = this;
        let text = this.textArea.value;
        let message = this.createMessage('<b>Sending...</b>');
        let list_node = document.createElement('li');
        list_node.append(message);

        this.messagesList.appendChild(list_node);
        this.goToTheDialogBottom();

        let data = JSON.stringify({
            "send_message" : {
                "dialog-id" : this.dialogId,
                "content"   : text
            },
        });

        this.sendAjax("ajax", {"json_string" : data}, success, error, "POST")
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

            that.typingDiv.innerHTML = "";
            let separator = (response.typing.length > 1) ? ", " : " ";

            for (var i in response.typing){
                that.typingDiv.innerHTML += response.typing[i] + separator;
            }

            if (response.typing.length > 0)
                that.typingDiv.innerHTML += " is typing...";

            if (response.typing.length > 1)
                that.typingDiv.innerHTML += " are typing...";


        }

        function  success (result) {
            try{
                var response = JSON.parse(result);
                console.log(response);
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
            "load_new_messages" : {
                "last_message_id" : last_m_id,
                "dialog-id"       : this.dialogId
            },
            "check_is_typing" : {
                "dialog-id"       : this.dialogId
            },
            "set_typing" : {
                "dialog-id"  : this.dialogId,
                "is_typing" : this.isTyping
            }
        });

        this.sendAjax("ajax", {"json_string" : data}, success, error, "POST")
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
            'load_old_messages': {
                'dialog-id': dialogId,
                'first_message-id': firstMessageId,
            },
        });

       this.sendAjax('ajax', {"json_string" : data}, success, error, "POST")
           /* .catch(function (e) {
                console.log(e);
            }); */
       this.isLoading = true;
    }

    goToTheDialogBottom () {
        this.dialogBlock.scrollTop = this.dialogBlock.scrollHeight;
    }

    resetIsTyping () {
        this.isTyping = false;
        this.typingDiv.innerHTML = '';
    }
}

var dialog = new Dialog();