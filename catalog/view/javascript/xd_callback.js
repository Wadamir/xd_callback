document.addEventListener("DOMContentLoaded", function () {
    console.log('xd_callback.js!');

    $('#xd_callback-form input').focus(function () {
        console.log('focus');
        $(this).parent().removeClass('has-error');
        if ($(this).attr('name') === 'captcha') {
            $(this).parent().parent().parent().removeClass('has-error');
        }
    });

    $('#xd_callback-form').submit(function (event) {
        event.preventDefault ? event.preventDefault() : (event.returnValue = false);
        if (!formValidation(event.target)) { return false; }
        var sendingForm = $(this);
        var submit_btn = $(this).find('button[type=submit]');
        var value_text = $(submit_btn).text();
        var waiting_text = 'SENDING';
        $.ajax({
            url: 'index.php?route=module/xd_callback/submit',
            type: 'post',
            data: $('#xd_callback-form input[type=\'hidden\'], #xd_callback-form input[type=\'text\'], #xd_callback-form input[type=\'tel\'], #xd_callback-form input[type=\'email\'], #xd_callback-form textarea'),
            dataType: 'json',
            beforeSend: function () {
                $(submit_btn).prop('disabled', true);
                $(submit_btn).addClass('waiting').text(waiting_text);
            },
            complete: function () {
                $(submit_btn).button('reset');
            },
            success: function (json) {
                console.log(json);
                if (json['error']) {
                    if (json['error']['input'] === 'spam_protection') {
                        $(sendingForm).trigger('reset');
                        $(submit_btn).removeClass('waiting');
                        $(submit_btn).text(value_text);
                        $(submit_btn).prop('disabled', false);
                        $('#xd_callback_success').find('.text-center').html(json['error']['message']);
                        $('#xd_callback_modal').on('hidden.bs.modal', function (e) {
                            $('#xd_callback_success').modal('show');
                            setTimeout(function () {
                                console.log('success sending!');
                                $('#xd_callback_success').modal('hide');
                            }, 3000);
                        });
                        $('#xd_callback_modal').modal('hide');
                    } else {
                        $(submit_btn).prop('disabled', false);
                        $(submit_btn).removeClass('waiting').text("ERROR");
                        setTimeout(function () {
                            $(submit_btn).delay(3000).text(value_text);
                        }, 3000);
                    }
                }

                if (json['success']) {
                    var success = true;
                    let successType = document.getElementById('xd_callback_success_type').value;
                    console.log('successType - ', successType);
                    if (successType == '0') {
                        $(sendingForm).trigger('reset');
                        $(submit_btn).removeClass('waiting');
                        $(submit_btn).text(value_text);
                        $(submit_btn).prop('disabled', false);
                        $('#xd_callback_modal').modal('hide');
                        $('#xd_callback_modal').on('hidden.bs.modal', function (e) {
                            if (success) {
                                $('#xd_callback_success').modal('show');
                                setTimeout(function () {
                                    console.log('success sending!');
                                    $('#xd_callback_success').modal('hide');
                                }, 4000);
                                success = false;
                            }
                        });
                    } else {
                        let successUtm = document.getElementById('xd_callback_success_utm').value;
                        console.log('successUtm - ', successUtm);
                        let successUrl = '/index.php?route=checkout/success&' + successUtm;
                        window.location.href = successUrl;
                    }
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                $(submit_btn).prop('disabled', false);
                $(submit_btn).removeClass('waiting').text("ERROR");
                setTimeout(function () {
                    $(submit_btn).delay(3000).text(value_text);
                }, 3000);
            }
        });
        event.preventDefault();
    });
});
function formValidation(formElem) {
    var elements = $(formElem).find('input.required');
    var telElements = $(formElem).find('input[type=tel]');
    var errorCounter = 0;

    $(elements).each(function (indx, elem) {
        if ($(elem).attr('type') === 'tel' || $(elem).attr('type') === 'hidden') {
            return;
        }
        var placeholder = $(elem).attr('placeholder');
        if ($.trim($(elem).val()) == '' || $(elem).val() == placeholder) {
            $(elem).parent().addClass('has-error');
            errorCounter++;
        } else {
            $(elem).parent().removeClass('has-error');
        }
    });

    $(telElements).each(function (indx, elem) {
        var pattern = new RegExp(/^(\(?\+?[0-9]*\)?)?[0-9_\- \(\)]*$/);
        if (!pattern.test($(elem).val()) || $.trim($(elem).val()) == '') {
            $(elem).parent().addClass('has-error');
            errorCounter++;
        } else {
            $(elem).parent().removeClass('has-error');
        }
    });

    console.log('validation error - ', errorCounter);

    if (errorCounter > 0) {
        return false;
    } else {
        return true;
    }
}