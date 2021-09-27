class Validator {

    constructor()
    {

        this.firstname = document.getElementById("firstname");
        this.lastname = document.getElementById("lastname");
        this.email = document.getElementById("email");
        this.password = document.getElementById("password");
        this.confirmPassword = document.getElementById("passwordConfirm");
        this.content = document.getElementById("content");
        this.submitRegister = document.getElementById("submitRegister");
        this.submitContact = document.getElementById("submitContact");
        this.submitAccount = document.getElementById("submitAccount");
        this.error = document.getElementById("error");

        this.passCheck = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[-+!*$@%_])([-+!*$@%_\w]{8,100})$/;

        if (this.submitRegister) {
            this.submitRegister.addEventListener("click", (event) => {
                this.registerValidator(event);
            });
        }

        if (this.submitContact) {
            this.submitContact.addEventListener("click", (event) => {
                this.contactValidator(event);
            });
        }

        if (this.submitAccount) {
            this.submitAccount.addEventListener("click", (event) => {
                this.registerValidator(event);
            });
        }

    }

    // Les différentes méthodes pour le fonctionnement de la validation

    registerValidator(event)
    {

        if (this.firstname.value === "" || this.lastname.value === "" || this.email.value === "" || this.password.value === "" ||this.confirmPassword.value === "") {
            event.preventDefault();
            this.error.style.display = "flex";
            this.error.innerHTML = "Tous les champs doivent être remplis !";
        } else if (this.firstname.value.length < 2 || this.firstname.value.length > 30 || this.lastname.value.length < 2 || this.lastname.value.length > 30) {
            event.preventDefault();
            this.error.style.display = "flex";
            this.error.innerHTML = "Le prénom et le nom doivent contenir de 2 à 30 caractères !";
        } else if (this.confirmPassword.value !== this.password.value) {
            event.preventDefault();
            this.error.style.display = "flex";
            this.error.innerHTML = "Mots de passe non identiques !";
        } else if (!this.passCheck.test(this.confirmPassword.value) && !this.passCheck.test(this.confirmPassword.value)) {
            event.preventDefault();
            this.error.style.display = "flex";
            this.error.innerHTML = "Votre mot de passe doit contenir au moins 1 chiffre, une lettre minuscule, majuscule, un caractère spécial et 8 caractères minimum&nbsp;!";
        }

        return true;

    }

    contactValidator(event)
    {

        if (this.firstname.value === "" || this.lastname.value === "" || this.email.value === "" || this.content.value === "") {
            event.preventDefault();
            this.error.style.display = 'flex';
            this.error.innerHTML = 'Tous les champs doivent être remplis !';
        } else if (this.firstname.value.length < 2 || this.firstname.value.length > 30 || this.lastname.value.length < 2 || this.lastname.value.length > 30) {
            event.preventDefault();
            this.error.style.display = 'flex';
            this.error.innerHTML = 'Le prénom et le nom doivent contenir de 2 à 30 caractères !';
        }

        return true;

    }

}

new Validator();