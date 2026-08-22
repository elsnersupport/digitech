# Basecom_DisableCustomerAddressFileUpload Module

<div align="center">

[![Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
![Supported Magento Versions][ico-compatibility]

</div>

This module disables the file upload functionality for customer address attributes in Magento 2. This file upload is by
default open to every user and can open up your system to security vulnerabilities.

The SessionReaper attacks exploit this endpoint to upload malicious files to your server and then execute them.
While the remote code execution vulnerability has been patched, the upload endpoint was kept open and remains a security
risk.

Install this module to disable the upload endpoint and secure your Magento installation.

## Installation

1. Install the module via composer

    ```console
    composer require basecom/magento2-disable-customer-address-file-upload
    ```

2. Enable the module

    ```console
    bin/magento module:enable Basecom_DisableCustomerAddressFileUpload
    bin/magento setup:upgrade
    ```

## Security

If you discover any security related issues, please email <magento@basecom.de> instead of using the issue tracker.

## License

Licensed under the [MIT](LICENSE) license.

## Copyright

basecom GmbH & Co. KG

[ico-version]: https://img.shields.io/packagist/v/basecom/magento2-disable-customer-address-file-upload.svg?style=flat-square

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[ico-compatibility]: https://img.shields.io/badge/magento-2.4-brightgreen.svg?logo=magento&longCache=true&style=flat-square

[link-packagist]: https://packagist.org/packages/basecom/magento2-disable-customer-address-file-upload
