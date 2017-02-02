class DialogProperties {
    constructor() {
        this.form               = document.getElementById('dialog-properties');
        this.user_selected_div  = document.getElementById('selected_users');
        this.users_select       = document.getElementById('users-select');
        this.users              = this.form.querySelectorAll("input[type='checkbox']");

        this.addEventListeners();
    }

    addEventListeners(){
        var that = this;

        this.users_select.onchange = function(e){
            let selectedOption = e.target.selectedOptions[0]    ;
            that.addUser.apply(that, [selectedOption]);
        }
    }

    findUser(id){
        return this.user_selected_div.querySelectorAll("input[value='"+id+"']");
    }

    addUser(option){
        if (this.findUser(option.value).length > 0){
            console.log(this.findUser(option.value));
            return;
        }

        let input = document.createElement('input');
        input.type  = "checkbox";
        input.name  = "DialogProp[users][]";
        input.id    = "checkbox-" + option.value;
        input.value = option.value;

        let label = document.createElement('label');
        label.classList.add('checkbox-inline');
        label.for = "checkbox-" + option.value;
        label.appendChild(input);
        label.innerHTML += option.innerHTML;

        this.user_selected_div.appendChild(label);
    }


}

let dp = new DialogProperties();