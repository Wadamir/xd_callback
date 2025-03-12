function callbackOpenInfo(e, link) {
    e.preventDefault();
    e.stopPropagation();
    window.open(link, '_blank');
    return false;
}

function callbackFormValidation(formElem) {
    let elements = formElem.querySelectorAll('input.required');
    let telElements = formElem.querySelectorAll('input[type=tel]');
    let errorCounter = 0;

    elements.forEach(elem => {
        if (elem.type === 'tel' || elem.type === 'hidden') return;
        let placeholder = elem.getAttribute('placeholder');
        if (elem.value.trim() === '' || elem.value === placeholder) {
            elem.parentElement.classList.add('has-error');
            errorCounter++;
        } else {
            elem.parentElement.classList.remove('has-error');
        }
    });

    telElements.forEach(elem => {
        let pattern = /^\(?\+?[0-9]*\)?[0-9_\- ()]*$/;
        if (!pattern.test(elem.value) || elem.value.trim() === '') {
            elem.parentElement.classList.add('has-error');
            errorCounter++;
        } else {
            elem.parentElement.classList.remove('has-error');
        }
    });

    return errorCounter === 0;
}

document.addEventListener("DOMContentLoaded", function () {
    console.log('xd_callback.js!');
    let errorElem = document.getElementById('xd_callback_error');
    document.querySelectorAll('#xd_callback-form input').forEach(input => {
        input.addEventListener('focus', function () {
            this.parentElement.classList.remove('has-error');
            if (this.name === 'captcha') {
                this.closest('.has-error')?.classList.remove('has-error');
            }
            errorElem.textContent = '';
            errorElem.classList.add('hidden');
        });
    });

    document.getElementById('xd_callback-form').addEventListener('submit', function (event) {
        event.preventDefault();
        // if (!callbackFormValidation(this)) return false;

        let submitBtn = this.querySelector('button[type=submit]');
        let errorElem = document.getElementById('xd_callback_error');

        submitBtn.disabled = true;
        submitBtn.classList.add('disabled');

        let formData = new FormData(this);

        fetch('index.php?route=module/xd_callback/submit', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(json => {
                console.log(json);
                if (json.error) {
                    if (json.error.input) {
                        let input = document.getElementById(json.error.input);
                        if (!input) {
                            return false;
                        }
                        if (json.error.input === 'xd_callback_captcha') {
                            input.classList.add('has-error');
                        } else {
                            input.parentElement.classList.add('has-error');
                        }

                        if (json.error.message) {
                            errorElem.textContent = json.error.message;
                            errorElem.classList.remove('hidden');
                            setTimeout(() => {
                                errorElem.textContent = '';
                                errorElem.classList.add('hidden');
                            }, 5000);
                        }
                    }
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('disabled');
                }
                if (json.success) {
                    errorElem.textContent = '';
                    errorElem.classList.add('hidden');
                    document.getElementById('xd_callback-form').reset();
                    submitBtn.disabled = false;
                    if (json.redirect) {
                        window.location.href = json.redirect;
                    } else {
                        submitBtn.classList.remove('disabled');
                        $('#xd_callback_modal').modal('hide');
                        $('#xd_callback_success').modal('show');
                        setTimeout(() => {
                            $('#xd_callback_success').modal('hide');
                        }, 5000);
                    }
                }
            })
            .catch(error => {
                console.error(error);
                submitBtn.disabled = false;
                submitBtn.classList.remove('disabled');
            });
    });
});