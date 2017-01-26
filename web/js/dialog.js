var Dialog = (function () {
    let dialogBlock = document.getElementById('dialog_block');
    let dialogList = document.getElementById('messages_list');
    console.log(dialogList);
    let textArea = $('#textarea');

    if (dialogBlock == undefined){
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



    function createMessage(message, from = 1){
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
    function goToTheDialogBottom(){
        dialogBlock.scrollTop = dialogBlock.scrollHeight;
    }

    function loadOldMessages(){
        if (loadOldMessages.isLoading == undefined){
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
                if (res === ""){
                    loadOldMessages.canLoadMore = false;
                    dialogList.innerHTML = "<h5 class='text-warning text-center'><b>начало диалога</b></h5>" + dialogList.innerHTML;
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

    function loadNewMessages(){
        let messagesFromUsersInDialog = dialogBlock.getElementsByClassName('message');
        let lastMessage = messagesFromUsersInDialog[messagesFromUsersInDialog.length-1];
        let date = lastMessage.getAttribute('data-creation-date');
        let dialog_id = $('#send_message').data('dialog_id');

        $.ajax({
            url: "/dialog/load-new-messages",
            data: {
                dialog_id: dialog_id,
                creation_date: date,
            },
            type: "POST",
            success: function (res) {
                if(res == 'empty'){ return false; }
                dialogBlock.innerHTML += res;
                goToTheDialogBottom();
            },
            error: function (err) {
                console.log("loadNewMessages ERROR");
            }
        });
    }

})();
