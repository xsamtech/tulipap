/**
 * Custom scripts
 * 
 * Copyright (c) 2023 Xsam Technologies and/or its affiliates. All rights reserved.
 * 
 * @author Xanders Samoth
 * @see https://www.linkedin.com/in/xanders-samoth-b2770737/
 */

$(document).ready(function () {
    /* MULTILINE TEXT TRUNCATION */
    $('.paragraph-ellipsis').each(function () {
        $(this).find('.paragraph2').ellipsis({
            lines: 2,             // force ellipsis after a certain number of lines. Default is 'auto'
            ellipClass: 'ellip',  // class used for ellipsis wrapper and to namespace ellip line
            responsive: true      // set to true if you want ellipsis to update on window resize. Default is false
        });

        var _this2 = $(this).find('.paragraph2').get(0);

        $(this).find('.roll-block a').on('click', function () {
            $(_this2).ellipsis({ellipClass: '_ellip'});
            $(this).html('');

            return false;
        });

        $(this).find('.paragraph3').ellipsis({
            lines: 3,             // force ellipsis after a certain number of lines. Default is 'auto'
            ellipClass: 'ellip',  // class used for ellipsis wrapper and to namespace ellip line
            responsive: true      // set to true if you want ellipsis to update on window resize. Default is false
        });

        var _this3 = $(this).find('.paragraph3').get(0);

        $(this).find('.roll-block a').on('click', function () {
            $(_this3).ellipsis({ellipClass: '_ellip'});
            $(this).html('');

            return false;
        });

        $(this).find('.paragraph4').ellipsis({
            lines: 4,             // force ellipsis after a certain number of lines. Default is 'auto'
            ellipClass: 'ellip',  // class used for ellipsis wrapper and to namespace ellip line
            responsive: true      // set to true if you want ellipsis to update on window resize. Default is false
        });

        var _this4 = $(this).find('.paragraph4').get(0);

        $(this).find('.roll-block a').on('click', function () {
            $(_this4).ellipsis({ellipClass: '_ellip'});
            $(this).html('');

            return false;
        });

        $(this).find('.paragraph5').ellipsis({
            lines: 5,             // force ellipsis after a certain number of lines. Default is 'auto'
            ellipClass: 'ellip',  // class used for ellipsis wrapper and to namespace ellip line
            responsive: true      // set to true if you want ellipsis to update on window resize. Default is false
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

    // Auto-resize textarea
    autosize($('textarea'));
});
