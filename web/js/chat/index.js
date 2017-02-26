(
    function () {
        let a = document.getElementById('create_dialog');
        if (a == undefined)
            return;

        a.addEventListener('click', function (e) {
            function success(res) {
                $("#chat_modal .modal-body").html(res);
                $("#chat_modal").modal();
            }

            function error(res){
                console.log(res);
            }

            $.ajax({
                type : "POST",
                url  : "/chat/ajax/get-create-dialog-form",
                success : success,
                error   : error
            });
        });
    }
)();
