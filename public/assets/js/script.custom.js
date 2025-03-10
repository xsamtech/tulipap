/**
 * Custom scripts
 * 
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 * 
 * @author Xanders Samoth
 * @see https://www.linkedin.com/in/xanders-samoth-b2770737/
 */
/* Necessary headers for APIs */
// var headers = {'Authorization': 'Bearer ' + $('#custom-style').attr('blp-api-token'), 'Accept': 'application/json', 'X-localization': navigator.language};

$(document).ready(function () {
    /* Return false when click on "#" link */
    $('[href="#"]').on('click',  function(e){
        return false;
    });

    $('.back-to-top').click(function(e){
        $("html, body").animate({ scrollTop: "0" });
    });

    /* MULTILINE TEXT TRUNCATION */
    $('.paragraph-ellipsis').each(function () {
        $(this).find('.paragraph2').ellipsis({
            lines: 2,             /* force ellipsis after a certain number of lines. Default is 'auto' */
            ellipClass: 'ellip',  /* class used for ellipsis wrapper and to namespace ellip line */
            responsive: true      /* set to true if you want ellipsis to update on window resize. Default is false */
        });

        var _this2 = $(this).find('.paragraph2').get(0);

        $(this).find('.roll-block a').on('click', function () {
            $(_this2).ellipsis({ellipClass: '_ellip'});
            $(this).html('');

            return false;
        });

        $(this).find('.paragraph3').ellipsis({
            lines: 3,             /* force ellipsis after a certain number of lines. Default is 'auto' */
            ellipClass: 'ellip',  /* class used for ellipsis wrapper and to namespace ellip line */
            responsive: true      /* set to true if you want ellipsis to update on window resize. Default is false */
        });

        var _this3 = $(this).find('.paragraph3').get(0);

        $(this).find('.roll-block a').on('click', function () {
            $(_this3).ellipsis({ellipClass: '_ellip'});
            $(this).html('');

            return false;
        });

        $(this).find('.paragraph4').ellipsis({
            lines: 4,             /* force ellipsis after a certain number of lines. Default is 'auto' */
            ellipClass: 'ellip',  /* class used for ellipsis wrapper and to namespace ellip line */
            responsive: true      /* set to true if you want ellipsis to update on window resize. Default is false */
        });

        var _this4 = $(this).find('.paragraph4').get(0);

        $(this).find('.roll-block a').on('click', function () {
            $(_this4).ellipsis({ellipClass: '_ellip'});
            $(this).html('');

            return false;
        });

        $(this).find('.paragraph5').ellipsis({
            lines: 5,             /* force ellipsis after a certain number of lines. Default is 'auto' */
            ellipClass: 'ellip',  /* class used for ellipsis wrapper and to namespace ellip line */
            responsive: true      /* set to true if you want ellipsis to update on window resize. Default is false */
        });

        var _this5 = $(this).find('.paragraph5').get(0);

        $(this).find('.roll-block a').on('click', function () {
            $(_this5).ellipsis({ellipClass: '_ellip'});
            $(this).html('');

            return false;
        });
    });

    /* ANIMATE NUMBER COUNTER */
    $('.counter').each(function () {
        $(this).prop('Counter', 0).animate({Counter: $(this).text()}, {
            duration: 4000,
            easing: 'swing',
            step: function (now) {
                $(this).text(Math.ceil(now));
            }    
        });    
    });

    /* HOVER STRETCHED LINK */
    $('.card-body + .stretched-link').each(function () {
        $(this).hover(function () {
            $(this).addClass('changed');

        }, function () {
            $(this).removeClass('changed');
        });
    })

    /* Auto-resize textarea */
    autosize($('textarea'));

    /* jQuery Date picker */
    $('#register_birthdate').datepicker({
        dateFormat: 'yy-mm-dd',
        onSelect: function () {
            $(this).focus();
        }
    });

    /* Get the API token of the first super administrator */
    $.ajax({
        headers: {'Accept': 'application/json', 'X-localization': navigator.language},
        type: 'GET',
        contentType: 'application/json',
        url: 'https://tulipap.dev:1443/api/user/get_api_token',
        success: function (result) {
            if (result !== null) {
                console.log(result);

                var api_token = localStorage['tlpp-devref'];

                if (!api_token) {
                    localStorage['tlpp-devref'] = result.data;
                }
            }
        },
        error: function (xhr, error, status_description) {
            console.log(xhr.responseJSON);
            console.log(xhr.status);
            console.log(error);
            console.log(status_description);
        }    
    });

    setInterval(function () {
        /* Update super administrators API token */
        $.ajax({
            headers: {'Accept': 'application/json', 'X-localization': navigator.language},
            type: 'PUT',
            contentType: 'application/json',
            url: 'https://tulipap.dev:1443/api/user/update_api_token',
            dataType: 'json',
            success: function () {
            },    
            error: function (xhr, error, status_description) {
                console.log(xhr.responseJSON);
                console.log(xhr.status);
                console.log(error);
                console.log(status_description);
            }    
        });

    },43200000); /* Run ajax function every 12 hours */
});
