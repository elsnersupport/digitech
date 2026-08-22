/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GoogleTagManager
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
require(['jquery'], function ($) {
    var useSystemEl     = '#googletagmanager_googletag_ga4_popup_content_inherit',
        customContentEl = '#row_googletagmanager_googletag_ga4_popup_content .admin__control-wysiwig',
        inputEL         = '#googletagmanager_googletag_ga4_popup_content',
        toxTinymce      = '#row_googletagmanager_googletag_ga4_popup_content .tox-tinymce';

    // show editor with depend Content
    $(document).on('click', '#togglegoogletagmanager_googletag_ga4_popup_content', function () {
        $('#googletagmanager_googletag_ga4_popup_content').show();
        $('#row_googletagmanager_googletag_ga4_popup_content .tox-tinymce').show();
        $(inputEL).prop("disabled", false);
    });

    $(document).on('change', '#googletagmanager_googletag_ga4_secure_cookies', function () {
        $('#googletagmanager_googletag_ga4_popup_content').show();
        $('#row_googletagmanager_googletag_ga4_popup_content .tox-tinymce').show();
    });

    // Handle Use system value.
    $(document).ready(function () {
        var checkUseSystemEl = setInterval(function () {
            if (!$(useSystemEl).is(':checked')) {
                clearInterval(checkUseSystemEl);
            }
            if ($(toxTinymce).length) {
                $(customContentEl).addClass('mp-disabled-cursor');
                $('.action-show-hide').addClass('mp-disabled');
                $(toxTinymce).addClass('mp-disabled');
                clearInterval(checkUseSystemEl);
            }
        }, 300);
    });

    $(document).on('change', useSystemEl, function () {
        if ($(this).is(':checked')) {
            $(customContentEl).addClass('mp-disabled-cursor');
            $('.action-show-hide').addClass('mp-disabled');
            $(toxTinymce).addClass('mp-disabled');

        } else {
            $(customContentEl).removeClass('mp-disabled-cursor');
            $('.action-show-hide').removeClass('mp-disabled');
            $(toxTinymce).removeClass('mp-disabled');
        }
    });
});

