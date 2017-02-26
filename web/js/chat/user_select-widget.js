console.log(123);
class DialogProperties {
    constructor() {
        this.form               = document.getElementById('dialog-properties');
        this.users_selected_div = document.getElementById('selected_users');
        this.users_select       = document.getElementById('users-select');
		this.addUserButton      = document.getElementById('add-user');
        this.addEventListeners();
    }

    addEventListeners(){
        function UsersSelectedDivEventsListener(e) {
            let a_target = e.target.closest('a');
            if (a_target == undefined)
                return e.preventDefault();

            if (a_target.classList.contains('btn-delete')){
                let checkbox  = a_target.parentNode.querySelector("input[type='checkbox']");
                let a_restore = a_target.parentNode.querySelector("a.btn-restore");
                let label     = a_target.closest('label');

                checkbox.checked = false;
                label.classList.add('deleted');
                a_target.style.display  = 'none';
                a_restore.style.display = 'block';

            } else
            if (a_target.classList.contains('btn-restore')){
                let checkbox  = a_target.parentNode.querySelector("input[type='checkbox']");
                let a_delete  = a_target.parentNode.querySelector("a.btn-delete");
                let label     = a_target.closest('label');

                checkbox.checked = true;
                label.classList.remove('deleted');
                a_target.style.display = 'none';
                a_delete.style.display = 'block';
            }
        }


        var that = this;
		this.addUserButton.addEventListener('click', function(e){
			let selectedOption = that.users_select.selectedOptions[0];
			that.addUser.apply(that, [selectedOption]);
		});
		
		this.users_selected_div.addEventListener('click', UsersSelectedDivEventsListener);
    }

    findUser(id){
        return this.users_selected_div.querySelectorAll("input[value='"+id+"']");
    }

    addUser(option){
        if (this.findUser(option.value).length > 0){
            console.log(this.findUser(option.value));
            return;
        }

        let label = this.createUserElement(option.value, option.innerHTML);
        this.users_selected_div.appendChild(label);
    }
    createUserElement(id, username){
        let input = document.createElement('input');
        input.type  = "checkbox";
        //input.name  = "DialogProperties[users][]";
        // modelName - global Variable
        // attributeName - global variable
        input.name  = modelName + "["+ attributeName + "][]";
        input.value = id;
        input.id    = 'checkbox-' + id;
        input.setAttribute('checked', true);

        let a_delete = document.createElement('a');
        a_delete.classList.add('btn-delete');
        a_delete.innerHTML = `<i class="fa fa-times" aria-hidden="true"></i>`;

        let a_restore = document.createElement('a');
        a_restore.classList.add('btn-restore');
        a_restore.innerHTML = `<i class="fa fa-undo" aria-hidden="true">`;

        let span = document.createElement('span');
        span.innerHTML = username;

        let p = document.createElement('p');
        p.innerHTML = "Was added just now";

        let label = document.createElement('label');
        label.classList.add('user-checkbox');
        label.appendChild(input);
        label.appendChild(span);
        label.appendChild(p);
        label.appendChild(a_delete);
        label.appendChild(a_restore);

        return label;
    }
}

var dp = new DialogProperties();




function deleteButtonEventListener(event){
	var target = event.target;
	var label = target.closest('label');
	label.parentNode.removeChild(label);
	console.log(label);
}