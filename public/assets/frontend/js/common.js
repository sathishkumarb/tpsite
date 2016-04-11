/**
 * This function check html tags in field value
 */
function checkHTMLTags(value, element, params) {
    if (value.match(/([\<])([^\>]{1,})*([\>])/i) == null) {
        return true;
    } else {
        return false;
    }
}

/**
 * checkUniqueEmail
 * @param {string} value
 * @param {object} element
 * @param {mixed} params
 * @returns {Boolean}
 */
function checkUniqueEmail(value, element, params) {
    var result = false;
    $.ajax({
        cache: false,
        async: false,
        type: "POST",
        url: FULL_URL_PATH + 'index/checkemail',
        dataType: 'json',
        data: {'email': value},
        success: function (response) {
            if (response.status === 'success') {
                if (response.result == 1) {
                    result = false;
                } else if (response.result == 2) {
                    result = false; /* Email id not received on server */
                }else{
                    result = true;//response.result = 0;
                }
            } else {
                result = false;
            }
        }
//        success: function (response) {
//            if (data == 1)
//            {
//                result = false;
//            } else if (data == 2) {
//                result = false; /* Email id not received on server */
//            }
//            else
//            {
//                result = true;
//            }
//        }
    });
    return result;
}

$(document).ready(function () {
    /* On click of Sign up button */
    $('#btnSignUpPop').click(function () {
        $("#usersignup input[type=text]").val('');
        $('label.error').hide();
        $('#signupsucssmsg').hide();
    });
});
