;(
    function UserForm() {
        let fileInput     = document.getElementById('main_image-input');
        let selectedImage = document.getElementById('usr_select_form-image_selected');
        let fileReader    = new FileReader();

        fileReader.onloadend = function (e) {
            selectedImage.src = e.target.result;
        }

        fileInput.onchange = function (e) {
            fileReader.readAsDataURL(e.target.files[0]);
        }
    }
)();

