class DialogProperties {
    constructor() {
        this.form = document.getElementById('dialog-properties');
        this.user_selected_div = document.getElementById('users-selected');
        this.users = this.form.querySelectorAll("input[type='checkbox']");
        this.users_select = document.getElementById('users-select');
        console.log(this.users);

        this.addEventListeners();
    }

    addEventListeners(){
        var that = this;

        this.users_select.onchange = function(e){
            let selectedOption = e.target.selectedOptions[0]    ;
            console.log(selectedOption);
            that.addUser.apply(that, [selectedOption]);
        }
    }

    addUser(option){
        function insertAfter(elem, refElem) {
            return refElem.parentNode.insertBefore(elem, refElem.nextSibling);
        }

        let input = document.createElement('input');
        input.type  = "checkbox";
        input.name  = "DialogProp[users][]";
        input.id    = "checkbox-" + option.value;
        input.value = option.value;

        let label = document.createElement('label');
        label.innerHTML = option.innerHTML;
        label.for = "checkbox-" + option.value;

        insertAfter(label, this.users[this.users.length-1]);
        insertAfter(input, label);
    }
}

let dp = new DialogProperties();