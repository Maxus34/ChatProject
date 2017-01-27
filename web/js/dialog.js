"use strict";

var DiaLog = function () {
    let dialogBlock = document.getElementById('dialog_block');
    let dialogList = document.getElementById('messages_list');
    console.log(dialogList);
    let textArea = $('#textarea');

    if (dialogBlock == undefined) {
        return;
    }

    goToTheDialogBottom();

    $('#send_message').on('click', function (e) {
        let text = textArea.val();
        let user_id = $(this).data('user_id');
        let dialog_id = $(this).data('dialog_id');

        let message = createMessage('<b>Sending...</b>');
        let li = document.createElement('li');
        li.append(message);
        dialogList.appendChild(li);
        dialogBlock.scrollTop = dialogBlock.scrollHeight;

        $.ajax({
            url: "send-message",
            data: {
                dialog_id: dialog_id,
                content: text,
            },
            type: "POST",
            success: function (result) {
                console.log(result);
                dialogList.removeChild(li);
                dialogList.innerHTML += result;

                textArea.val('');
                goToTheDialogBottom();
            },

            error: function (err) {
                message.innerHTML = "<b>Error, please try later</b>";
                message.classList.add('message-error');
                goToTheDialogBottom();
            }
        });
    });

    $('#dialog_block').on('scroll', function DialogScrollHandler(e) {
        if (e.target.scrollTop < 1) {
            loadOldMessages();
        }
    });

    //vinterval = setTimeout(loadNewMessages, 5000);
    function createMessage(message, from = 1) {
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

    //Отображение нижней части диалога
    function goToTheDialogBottom() {
        dialogBlock.scrollTop = dialogBlock.scrollHeight;
    }

    function loadOldMessages() {
        if (loadOldMessages.isLoading == undefined) {
            loadOldMessages.isLoading = false;
            loadOldMessages.canLoadMore = true;
        }
        if (loadOldMessages.isLoading || !loadOldMessages.canLoadMore) {
            return 0;
        }

        loadOldMessages.isLoading = true;

        let firstMessage = dialogList.firstElementChild;
        let date = firstMessage.getAttribute('data-creation-date');
        let lst_m_id = firstMessage.getAttribute('data-id');
        let dialog_id = $('#send_message').data('dialog_id');

        $.ajax({
            url: "load-old-messages",
            data: {
                dialog_id: dialog_id,
                creation_date: date,
                last_message_id: lst_m_id
            },
            type: "POST",
            success: function (res) {
                if (res == "no_more") {
                    loadOldMessages.canLoadMore = false;
                    dialogList.innerHTML = "<h5 class='text-warning text-center'><b>начало диалога</b></h5>" + dialogList.innerHTML;
                    return;
                }
                let scrollBottom = dialogBlock.scrollHeight - dialogBlock.scrollTop;
                dialogList.innerHTML = res + dialogList.innerHTML;
                dialogBlock.scrollTop = dialogBlock.scrollHeight - scrollBottom;
                loadOldMessages.isLoading = false;
            },
            error: function (err) {
                console.log("loadOldMessages ERROR");
                loadOldMessages.isLoading = false;
            }
        });
    }

    function loadNewMessages() {
        let messagesFromUsersInDialog = dialogList.getElementsByClassName('message-from');
        let lastMessage = messagesFromUsersInDialog[messagesFromUsersInDialog.length - 1];
        let last_m_id = lastMessage.parentNode.getAttribute('data-id');
        let dialog_id = $('#send_message').data('dialog_id');

        console.log("id = " + last_m_id);

        $.ajax({
            url: "load-new-messages",
            data: {
                dialog_id: dialog_id,
                last_message_id: last_m_id,
            },
            type: "POST",
            success: function (res) {
                if (res == 'empty') {
                    return false;
                }
                dialogBlock.innerHTML += res;
                goToTheDialogBottom();
            },
            error: function (err) {
                console.log("loadNewMessages ERROR");
            }
        });
    }

};

class Dialog {
    constructor() {
        this.dialogBlock = document.getElementById('dialog_block');
        this.messagesList = document.getElementById('messages_list');
        this.textArea = document.getElementById('textares');
        this.sendMessageButton = document.getElementById('send_message');

        if (this.dialogBlock == undefined) {
            return;
        }

        this.goToTheDialogBottom();

        let that = this;
        this.sendMessageButton.addEventListener('click', function () {
            that.sendMessage.apply(that);
        });

        this.dialogBlock.addEventListener('scroll', function (e) {
            if (e.target.scrollTop < 1) {
                that.loadOldMessages.apply(that);
            }
        });

        //setTimeout;

        this.isLoading = false;
    }

    goToTheDialogBottom() {
        this.dialogBlock.scrollTop = this.dialogBlock.scrollHeight;
    }

    sendAjax(url, data, success, error, type = "POST") {
        $.ajax({
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

    createMessage(message, from = 1) {
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

    sendMessage() {

    }

    loadOldMessages() {
        function success(res) {
            let request = JSON.parse(res);

            if (request.old_messages === "") {
                that.dialogBlock.removeEventListener('scroll', that.loadNewMessages);
                that.messagesList.innerHTML = "<h5 class='text-warning text-center'><b>начало диалога</b></h5>" + that.messagesList.innerHTML;
            }
            console.log(request.old_messages);
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

        let this_obj = this;

        let data = JSON.stringify({
            'load_old_messages': {
                'dialog-id': dialogId,
                'first_message-id': firstMessageId,
            },
        });

        this.sendAjax('ajax', {"json_string" : data}, success, error, "POST");
           /* .catch(function (e) {
                console.log(e);
            }); */
    }

    loadNewMessages() {

    }
}

new Dialog();