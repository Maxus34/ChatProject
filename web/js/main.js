$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});

;(function(){
    let link = document.getElementById('login-link');
    if (!link)
        return;

    link.onclick = function (e) {
        $('#small-modal').modal('show')
            .find('.modal-body')
            .load(link.getAttribute('href'));
        e.preventDefault();
    }
})();