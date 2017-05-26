"use strict";

var createElementsByHTML = (function () {
    var div = document.createElement("div");
    return function (html) {
        var res = [];
        div.innerHTML = html;
        while (div.firstChild) {
            res[res.length] = div.removeChild(div.firstChild);
        }
        return res;
    };
})();


class JsonDataHandler {
    constructor(url, interval, dialog_id) {
        this.url = url;
        this.interval = interval;

        this.dataToSend = {};
        this.dialog_id = dialog_id;
        this.waiting_for_response = false;

        this.disposable_callbacks = [];
        this.permanent_callbacks = [];
        this.error_callbacks = [];

        this.param = document.querySelectorAll('meta[name=csrf-param]')[0].getAttribute('content');
        this.token = document.querySelectorAll('meta[name=csrf-token]')[0].getAttribute('content');

        this.interval = setInterval((e) => {
            this.sendData();
        }, interval);
    }

    addData(data, key) {
        //this.data = Object.assign(this.data, data);
        this.dataToSend[key] = data;
    }

    addCallback(callback, permanent = false) {
        if (permanent) {
            this.permanent_callbacks.push(callback);

        } else {
            this.disposable_callbacks.push(callback);
        }
    }

    addErrorCallback(callback) {
        this.error_callbacks.push(callback);
    }

    sendData() {
        function success(response) {
            this.waiting_for_response = false;

            try {
                var result = JSON.parse(response);
            } catch (e) {
                console.log("SendData Error");
                console.log(response);
                return;
            }


            for (let i in this.disposable_callbacks) {
                this.disposable_callbacks[i](result);
            }

            for (let i in this.permanent_callbacks) {
                this.permanent_callbacks[i](result);
            }

            this.disposable_callbacks = [];
            this.data = {};
        }

        function error(response) {
            console.log("Error");
            console.log(response);
            this.waiting_for_response = false;

            for (var i in this.error_callbacks) {
                this.error_callbacks[i](response);
            }

            this.error_callbacks = [];
            this.disposable_callbacks = [];
            this.data = {};
        }

        if (this.waiting_for_response) {
            console.log("waiting for response...");
            return;
        }


        this.dataToSend["dialog"] = {
            "dialog-id": this.dialog_id
        };


        let xhr = new XMLHttpRequest();
        let formData = new FormData();

        formData.append(this.param, this.token);
        formData.append("json_string", JSON.stringify(this.dataToSend));

        xhr.open("POST", this.url, true);

        xhr.upload.onloadstart = (e) => {
          this.waiting_for_response = true;
        };

        xhr.upload.onloadend = (e) => {
            this.waiting_for_response = false;
        };

        xhr.onload = xhr.onerror = (e) => {
            if (e.target.status === 200){
                success.call(this, e.target.responseText);
            } else {
                error.call(this, e.target.responseText);
            }
        };

        xhr.send(formData);
    }
}


class MessagesHandler {

    constructor(dataHandler) {

        this.text_area            = document.getElementById('textarea');
        this.messages_list        = document.getElementById('messages_list');
        this.send_message_button  = document.getElementById('send_message');
        this.del_messages_button  = document.getElementById('delete_messages');
        this.reset_selected_mess  = document.getElementById('reset_delete_messages');


        this.monitored_messages = {outgoing_messages: [], incoming_messages: []};
        this.selected_messages  = {};
        this.messages_for_send  = [];

        this.eventListeners = {};

        this.fileHandler = new FileHandler();
        this.dataHandler = dataHandler;

        this.addEventListeners();
        this.processMessagesListForHideUserInfo();
    }


    addEventListeners() {

        this.eventListeners['add_message_to_send'] = this.addMessageToSend.bind(this);
        this.eventListeners['body_scroll'] = (e) => {
            if (e.target.body.scrollTop < 1) {
                this.loadOldMessages();
            }
        };
        this.eventListeners['select_message'] = (e) => {
            this.handleMessageClick(e);
        };
        this.eventListeners['delete_messages'] = (e) => {
            this.deleteMessages();
        };
        this.eventListeners['reset_selected_messages'] = (e) => {
            this.resetSelectedMessages();
        };

        this.del_messages_button.addEventListener('click', this.eventListeners['delete_messages']);
        this.send_message_button.addEventListener('click', this.eventListeners['add_message_to_send']);
        this.reset_selected_mess.addEventListener('click', this.eventListeners['reset_selected_messages']);
        this.messages_list.addEventListener('click', this.eventListeners['select_message']);
        document.addEventListener('scroll', this.eventListeners['body_scroll']);


        this.interval = setInterval(() => {
            this.sendMessages();
            this.searchMessagesForSeen();
            this.checkNewIncomingMessages();
            this.handleSeenMessages();
        }, 1100);


        let target = this.messages_list;
        let config = { attributes: true, childList: true, characterData: true };
        let observer = new MutationObserver((mutations) => {
            this.processMessagesListForHideUserInfo();
        });

        observer.observe(target, config);
    }


    handleMessageClick(e) {
        function showModalImage(image) {
            if (image) {
                let full_image_link = image.getAttribute('data-url');

                if (!full_image_link)
                    return;

                let img = document.createElement('img');
                let download_link = document.createElement('a');
                download_link.href = full_image_link;

                download_link.innerHTML = 'download image';
                download_link.setAttribute('download', '');
                img.src = full_image_link;

                let modalWindowBody = $("#media_modal .modal-body");
                modalWindowBody.html('');
                modalWindowBody.append(img);
                modalWindowBody.append(document.createElement('hr'));
                modalWindowBody.append(download_link);
                $("#media_modal").modal();

                e.preventDefault();

            }
        }

        switch (e.target.tagName) {
            case "IMG":
                showModalImage(e.target);
                break;

            case "A":
                break;

            default:
                this.selectMessage.call(this, e.target.closest('LI'));
        }
    }


    sendMessages() {
        function callback_sendMessages_success(result) {


            if (!result.messages_for_send)
                return;

            this.isSendingMessages = false;

            for (var i = 0; i < result.messages_for_send.length; i++) {
                let is_sending_message = this.messages_list.querySelectorAll("li[data-id='" + result.messages_for_send[i].pseudo_id + "']")[0];

                if (result.messages_for_send[i].success) {
                    this.messages_list.removeChild(is_sending_message);

                    this.messages_list.innerHTML += result.messages_for_send[i].message;
                }

                this.text_area.value = '';
                DialogHandler.goToTheDialogBottom();

                this.messages_for_send = [];
            }
        }

        function callback_sendMessages_error(result) {

            let messages = this.messages_for_send;

            console.log("Send Messages Error");

            for (var i in messages) {
                let is_sending_message = this.messages_list.querySelectorAll("li[data-id='" + messages[i].pseudo_id + "']")[0];
                console.log(is_sending_message);
                is_sending_message.innerHTML = "Error. Please try later";
            }

            this.messages_for_send = [];

            this.isSendingMessages = false;
        }

        if (this.isSendingMessages)
            return;

        this.dataHandler.addData(this.messages_for_send, 'messages_for_send');
        this.dataHandler.addCallback(callback_sendMessages_success.bind(this), false);
        this.dataHandler.addErrorCallback(callback_sendMessages_error.bind(this));

        this.isSendingMessages = true;
    }


    addMessageToSend() {

        if (this.fileHandler.isLoadingFiles()) {
            alert('Please wait while all files will be loaded');
            return;
        }

        let fileIds = this.fileHandler.getFileIds();

        let text = this.text_area.value;
        if ((text == "") && fileIds.length < 1)
            return;


        var message = this.createMessage('<i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i><span class="sr-only">Loading...</span>Sending...', 1);
        var pseudo_id = "@" + Math.round(Math.random() * 10000);
        message.setAttribute('data-id', pseudo_id);

        this.messages_list.appendChild(message);
        DialogHandler.goToTheDialogBottom();

        this.messages_for_send.push({
            text: text,
            pseudo_id: pseudo_id,
            files: fileIds,
        });
    }


    loadOldMessages() {
        function callback_load_old(result) {

            if (!result.load_old_messages)
                return;

            this.isLoadingOldMessages = false;

            this.messages_list.removeChild(isLoadingMessage);

            if (result.load_old_messages.length == 0) {
                document.removeEventListener('scroll', this.eventListeners['body_scroll']);
                this.messages_list.innerHTML = "<h5 class='text-warning text-center'><b>начало диалога</b></h5>" + this.messages_list.innerHTML;
            }

            let scrollBottom = document.body.scrollHeight - document.body.scrollTop;
            for (var i = result.load_old_messages.length - 1; i >= 0; i--) {
                this.messages_list.insertBefore(createElementsByHTML(result.load_old_messages[i])[0], this.messages_list.firstElementChild);
            }

            document.body.scrollTop = document.body.scrollHeight - scrollBottom;

            this.isLoadingOldMessages = false;
        }

        function getFirstMessage(messages_list) {

            for (var i = 0; i < messages_list.childNodes.length; i++) {

                if (messages_list.childNodes[i].tagName !== 'LI')
                    continue;

                if (messages_list.childNodes[i].classList.contains('message')) {
                    return messages_list.childNodes[i];
                }
            }

        }


        let firstMessage = getFirstMessage(this.messages_list);

        if (this.isLoadingOldMessages || !firstMessage)
            return;


        var isLoadingMessage = document.createElement("li");
        isLoadingMessage.innerHTML = `<div class="text-center">
                                            <i class="fa fa-refresh fa-spin fa-2x fa-fw"></i>
                                            <span class="sr-only">Loading...</span>
                                      </div>`;

        this.messages_list.insertBefore(isLoadingMessage, this.messages_list.firstElementChild);

        let data = {
            "first_message-id": firstMessage.getAttribute('data-id'),
        };

        this.dataHandler.addData(data, "load_old_messages");
        this.dataHandler.addCallback(callback_load_old.bind(this), false);

        this.isLoadingOldMessages = true;
    }


    handleSeenMessages() {
        function setMessagesSeen(messages) {
            if (messages.length == 0)
                return;

            let selector = "";
            for (var i = 0; i < messages.length; i++) {
                selector += 'li[data-id="' + messages[i] + '"]';
                if (i < messages.length - 1)
                    selector += ",";
            }

            let need_to_change = this.messages_list.querySelectorAll(selector);
            for (var i = 0; i < need_to_change.length; i++) {
                need_to_change[i].dataset.new = "0";
                need_to_change[i].classList.remove('message-new');
            }
        }

        function callback_sn(result) {
            this.isLoadingSeen = false;

            if (result.seen_messages != undefined)
                setMessagesSeen.call(this, result.seen_messages);

            if (result.check_is_seen != undefined)
                setMessagesSeen.call(this, result.check_is_seen);

            this.resetMonitoredMessages();
        }

        if (this.isLoadingSeen)
            return;

        this.dataHandler.addData(this.monitored_messages.incoming_messages, "seen_messages");
        this.dataHandler.addData(this.monitored_messages.outgoing_messages, "check_is_seen");
        this.dataHandler.addCallback(callback_sn.bind(this), false);

        this.isLoadingSeen = true;
    }


    deleteMessages() {
        function callback_dm(result) {
            this.isLoadingDeleteMessages = false;

            if (!result.deleted_messages)
                return;

            let selector = "";
            for (var i = 0; i < result.deleted_messages.length; i++) {
                selector += 'li[data-id="' + result.deleted_messages[i] + '"]';
                if (i < result.deleted_messages.length - 1)
                    selector += ",";
            }

            let messages = this.messages_list.querySelectorAll(selector);

            for (var i = 0; i < messages.length; i++) {
                this.messages_list.removeChild(messages[i]);
            }

            let div1 = document.getElementById('dialog_header_1');
            let div2 = document.getElementById('dialog_header_2');
            div1.classList.remove('hidden');
            div2.classList.add('hidden');

            this.selected_messages = {};
        }

        if (this.isLoadingDeleteMessages)
            return;

        let messageIds = [];
        if (Object.keys(this.selected_messages).length > 0) {
            for (var i in this.selected_messages) {
                messageIds.push(i);
            }
        } else {
            return;
        }

        this.dataHandler.addData(messageIds, "delete_messages");
        this.dataHandler.addCallback(callback_dm.bind(this), false);

        this.isLoadingDeleteMessages = true;
    }


    searchMessagesForSeen() {
        let messages_list = this.messages_list.getElementsByTagName('li');
        let messages_incoming = [];
        let  messages_outgoing = [];

        for (var i = 0; i < messages_list.length; i++) {
            if ((messages_list[i].dataset.new === "1")) {

                if (messages_list[i].classList.contains('message-outgoing'))
                     messages_outgoing.push(messages_list[i].dataset.id);
                else
                    messages_incoming.push(messages_list[i].dataset.id);
            }
        }

        this.monitored_messages = {
            outgoing_messages:  messages_outgoing,
            incoming_messages: messages_incoming
        };
    }


    checkNewIncomingMessages() {
        function getLastMessageId(messages_list) {
            let messages = messages_list.getElementsByClassName('message-incoming');
            let last_m_id = null;

            if (messages.length > 0) {
                last_m_id = messages[messages.length - 1].getAttribute('data-id');
            } else {
                messages = messages_list.getElementsByClassName('message-outgoing');
                if (messages.length > 0) {
                    last_m_id = messages[messages.length - 1].getAttribute('data-id');
                }
            }

            return last_m_id;
        }

        function callback_cn(result) {
            this.isLoadingNew = false;

            if (!result.load_new_messages
                || result.load_new_messages.length < 1)
                return;

            for (var i in result.load_new_messages) {
                this.messages_list.appendChild(createElementsByHTML(result.load_new_messages[i])[0]);
            }

            DialogHandler.goToTheDialogBottom();
        }

        if (this.is_loading_new)
            return;

        var first_message_id = getLastMessageId(this.messages_list);

        var data = {
                "first_message-id": first_message_id
        };

        this.dataHandler.addData(data, "load_new_messages");
        this.dataHandler.addCallback(callback_cn.bind(this), false);

        this.isLoadingNew = true;
    }


    resetMonitoredMessages() {
        this.monitored_messages = {incoming_messages: [], outgoing_messages: []};
    }


    createMessage(text, type = 0) {
        let list_node = document.createElement('li');
        list_node.classList.add('message');

        switch (type) {
            case 0 :
                list_node.classList.add('message-incoming');
                break;
            case 1 :
                list_node.classList.add('message-outgoing');
                break;
            case 2 :
                list_node.classList.add('message-error');
                break;
            default :
                list_node.classList.add('message-info');
        }

        list_node.innerHTML = text;

        return list_node;
    }


    selectMessage(li) {
        function showDialogHeader(number) {
            switch (number) {
                case 1:
                    dialog_header_1.classList.remove('hidden');
                    dialog_header_2.classList.add('hidden');
                    break;


                case 2:
                    dialog_header_1.classList.add('hidden');
                    dialog_header_2.classList.remove('hidden');
                    break;
            }
        }

        if (this.selected_messages == undefined)
            this.selected_messages = {};

        let id = li.getAttribute('data-id');
        if (!this.selected_messages[id]) {
            this.selected_messages[id] = true;
            li.classList.add('message-selected');
        } else if (this.selected_messages[id] == true) {
            delete this.selected_messages[id];
            li.classList.remove('message-selected');
        }


        let dialog_header_1 = document.getElementById('dialog_header_1');
        let dialog_header_2 = document.getElementById('dialog_header_2');
        let delete_button = document.getElementById('delete_messages');

        if (Object.keys(this.selected_messages).length > 0) {
            showDialogHeader(2);
            delete_button.innerHTML = "Delete " + Object.keys(this.selected_messages).length + " messages.";
        } else {
            delete_button.innerHTML = "Nothing to delete.";
            showDialogHeader(1);
        }

    }


    resetSelectedMessages() {
        var selector = "";
        let selected_messages = Object.keys(this.selected_messages);
        this.selected_messages = {};

        for (let i = 0; i < selected_messages.length; i++) {
            selector += "li[data-id='" + selected_messages[i] + "']";

            if (i < selected_messages.length - 1) {
                selector += ",";
            }
        }

        let messages = this.messages_list.querySelectorAll(selector);

        for (let i = 0; i < selected_messages.length; i++) {
            messages[i].classList.remove('message-selected');
        }


        let dialog_header_1 = document.getElementById('dialog_header_1');
        let dialog_header_2 = document.getElementById('dialog_header_2');

        dialog_header_1.style.display = 'block';
        dialog_header_2.style.display = 'none';
    }


    processMessagesListForHideUserInfo(){
        let messagesList = this.messages_list.getElementsByTagName('LI');
        let prevElement  = messagesList[0];

        for (let i = 1; i < messagesList.length; i++){
            if (messagesList[i].getAttribute('data-createdBy') === prevElement.getAttribute('data-createdBy')){
                messagesList[i].classList.add('message-hidden-info')
            }

            prevElement = messagesList[i];
        }
    }
}


class FileHandler {
    constructor() {
        this.createFileInputForm();

        this.files_list = document.getElementById('files-list');
        this.uploadFileButton = document.getElementById('upload-file');

        this.param = document.querySelectorAll('meta[name=csrf-param]')[0].getAttribute('content');
        this.token = document.querySelectorAll('meta[name=csrf-token]')[0].getAttribute('content');

        this.files = {};
        this.fileReader = new FileReader();

        this.isLoading = false;

        this.addEventListeners();
        this.defineFileIcons();
    }

    createFileInputForm() {
        this.fileInputForm = document.createElement('form');
        this.fileInput = document.createElement('input');
        this.fileInput.type = 'file';
        this.fileInputForm.appendChild(this.fileInput);
    }


    addEventListeners() {
        this.uploadFileButton.onclick = (e) => {
            e.preventDefault(e);
            this.fileInput.click();
        };

        this.fileInput.onchange = (e) => {
            console.log(e.target.files);
            if (e.target.files.length > 0) {
                this.handleFile(e.target.files[0]);
            }

        };

        this.files_list.onclick = (e) => {
            if (e.target.tagName == 'I') {
                let a = e.target.closest('a');
                if (!a)
                    return;

                let random_id = a.getAttribute('data-random_id');
                if (random_id) {
                    this.removeFile(random_id);
                }
            }
        };
    }


    handleFile(file) {
        function sendFile(file_obj) {
            let file = file_obj['file'];

            let xhr = new XMLHttpRequest();

            let formData = new FormData();
            formData.append(this.param, this.token);
            formData.append('file', file, file.name);

            xhr.open("POST", 'ajax/upload-file', true);

            xhr.upload.onloadstart = (e) => {
                file_obj.isLoading = true;
            };

            xhr.upload.onloadend = (e) => {
                file_obj.progress.setAttribute('value', e.total);
                file_obj.isLoading = false;
            };

            xhr.upload.onprogress = (e) => {
                file_obj.progress.setAttribute('value', e.loaded);
                file_obj.progress.setAttribute('max', e.total);
            };

            xhr.onload = xhr.onerror = (e) => {
                if (e.target.status === 200) {

                    let result = JSON.parse(e.target.responseText);
                    if (result.error) {
                        file_obj.div.classList.add('text-danger');
                        file_obj.progress.classList.add('error');
                        file_obj.div.innerHTML += " -error";
                        file_obj.error = true;

                    } else {
                        file_obj.id = result.file.id;
                        file_obj.div.classList.add('text-success');
                        file_obj.progress.classList.add('success');
                    }

                } else {
                    file_obj.div.classList.add('text-danger');
                    file_obj.div.innerHTML += " -error";
                    file_obj.progress.classList.add('error');
                    file_obj.error = true;
                }

            }

            xhr.send(formData);

            file_obj.xhr = xhr;
        }

        let file_obj = this.createFileElement(file);


        sendFile.call(this, file_obj);
    }


    isLoadingFiles() {
        for (let i in this.files) {
            if (this.files[i].isLoading) {
                return true;
            }
        }

        return false;
    }


    getFileIds() {
        let file_ids = [];
        for (let i in this.files) {
            file_ids.push(this.files[i].id);
        }

        //Removing files
        for (let i in this.files) {
            this.files_list.removeChild(this.files[i].li);
            delete this.files[i];
        }

        return file_ids;
    }


    defineFileIcons() {
        this.icons = {
            'image': 'fa-file-image-o',
            'audio': 'fa-file-audio-o',
            'text': 'fa-file-text-o',
            'file': 'fa-file-o',
            'video': 'fa-film'
        }
    }


    createFileElement(file) {
        let file_obj = {
            file: file,
            li: document.createElement('li'),
            div: document.createElement('div'),
            progress: document.createElement('progress'),

            error: false,
            isLoading: false
        };

        let rm_button = document.createElement('a');
        rm_button.setAttribute('data-toggle', 'tooltip');
        rm_button.setAttribute('title', 'remove');


        file_obj.li.appendChild(this.getFileIcon(file));
        file_obj.li.appendChild(file_obj.div);
        file_obj.li.appendChild(file_obj.progress);
        file_obj.li.appendChild(rm_button);
        this.files_list.appendChild(file_obj.li);


        file_obj.div.innerHTML = file.name;


        let random_id = Math.floor(Math.random() * 10000);


        rm_button.innerHTML = "<i class='fa fa-times'></i>";
        rm_button.setAttribute('data-random_id', random_id);


        this.files[random_id] = file_obj;

        return file_obj;
    }


    getFileIcon(file) {
        let fileIcon = document.createElement('i');
        fileIcon.classList.add('fa');


        switch (file.type.split('/')[0]) {
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


    removeFile(random_id) {
        console.log(random_id);
        this.files_list.removeChild(this.files[random_id].li);
        this.files[random_id].xhr.abort();
        this.fileInputForm.reset();
        delete this.files[random_id];
    }

}


class DialogHandler {

    constructor() {
        this.dialogBlock = document.getElementById('dialog_block');
        if (this.dialogBlock == undefined) {
            return;
        }

        // activeUser - variable from view.php
        this.isActiveUser = activeUser || 0;

        this.text_area = document.getElementById('textarea');
        this.dialogPropertiesLi = document.getElementById('dialog_properties');
        this.typingDiv = document.getElementById('typing');


        this.dialogId = document.getElementById('send_message').getAttribute('data-dialog_id');
        this.eventListeners = {};
        this.isTyping = false;

        this.dataHandler    = new JsonDataHandler('/chat/ajax/json', 1200, this.dialogId);
        this.messageHandler = new MessagesHandler(this.dataHandler);

        this.addEventListeners();
        DialogHandler.goToTheDialogBottom();

        var that = this;
        this.interval = setInterval(function () {
            that.handleIsTyping.apply(that);
        }, 1500);
    }

    addEventListeners() {
        let that = this;

        // Event listeners declaration
        this.eventListeners['dialogPropertiesLi'] = function (e) {
            that.showDialogProperties.apply(that);
        }
        this.eventListeners['textArea'] = function (e) {
            that.isTyping = true;
        }

        //Event listeners adding.
        this.text_area.addEventListener('keydown', this.eventListeners['textArea']);
        this.dialogPropertiesLi.addEventListener('click', this.eventListeners['dialogPropertiesLi']);

        this.interval = setInterval(function () {
            that.handleIsTyping.apply(that);
        }, 1000);
    }

    handleIsTyping() {
        function callback_is_t(result) {
            this.isLoadingIsTyping = false;

            if (!result.typing)
                return;

            if (result.typing.length === 0) {
                this.resetIsTyping();
                return;
            }

            let typingText = "";
            let separator = (result.typing.length > 1) ? ", " : "";

            for (var i in result.typing) {
                typingText += result.typing[i] + separator;
            }

            if (result.typing.length < 2)
                typingText += " is typing now...";
            else
                typingText += " are typing now...";

            if (this.typingDiv.innerHTML != typingText)
                this.typingDiv.innerHTML = typingText;
        }

        if (this.isLoadingIsTyping)
            return;

        let setIsTypingData = {
            "is_typing": this.isTyping
        }

        this.dataHandler.addData(true, "check_is_typing");
        this.dataHandler.addData(setIsTypingData, "set_is_typing");
        this.dataHandler.addCallback(callback_is_t.bind(this), false);

        this.isTyping = false;
        this.isLoadingIsTyping = true;
    }

    showDialogProperties() {
        function callback_dp(result) {
            if (!result.form)
                return;

            $("#chat_modal .modal-body").html(result.form);
            $("#chat_modal").modal();
        }

        this.dataHandler.addData(true, "dialog_properties");
        this.dataHandler.addCallback(callback_dp.bind(this), false);
    }

    resetIsTyping() {
        this.isTyping = false;
        this.typingDiv.innerHTML = '';
    }

    static goToTheDialogBottom() {
        let newScrollTop = document.body.scrollHeight - document.body.clientHeight;
        document.body.scrollTop = newScrollTop;
    }

}


var dialog_h = new DialogHandler();
