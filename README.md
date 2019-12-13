# [SMTP Email Configuration for Magento 2](https://www.aitoc.com/magento-2-smtp.html)


## Description

**Advanced Level of Your Email Marketing**

Daily efforts related to sending numerous emails do not guarantee that all these emails will exactly come to customers. Unfortunately, there is a high probability that some of the messages will be rejected or will marked as spam. The default Magento email settings cannot offer you a perfect solution, endowing your emails with a doubtful reputation, so they can be rated as untrustworthy. 

This solution ensures merchants that all their messages will be delivered automatically and directly to the target audience. Among the other powerful features, **SMTP Email Settings extension for Magento 2** provides the option to control customization and arrange tests on the server that will guarantee that emails will reach customers timely.

The extension provides friendly configurations with multiple SMTP servers such as **Gmail, Yahoo, Outlook, Mail.com, Hotmail, Office365, O2 Mail, Send In Blue, etc.**

**What are the key benefits SMTP Email Configuration for Magento 2 provides you with?**
- Your emails will definitely reach the recipients
- No rejection and hitting a spam box
- You cooperate only with trusted email providers
- You are able to run tests before emails sending
- The secured connection ensures improved deliverability rates
- Using Email Log with advanced control
- Using Plain text for increase Spam-score


## Useful Information
- [About Us](https://www.aitoc.com/about-us.html)
- [Privacy Policy](https://www.aitoc.com/privacy-policy.html)
- [Partnership Program](https://www.aitoc.com/partnership-program)
- [Affiliate Program](https://www.aitoc.com/affiliate-program)
- [Aitoc Customer Rewards](https://www.aitoc.com/reward-points)
- [SMTP User Guide](https://www.aitoc.com/docs/guides/smtp.html)
- [Get Support](https://www.aitoc.com/get-support.html)
- [License](https://www.mageplaza.com/LICENSE.txt)


## Installation Guide

**INSTALL VIA COMPOSER**

Here you can find the guide [**'Extensions installation via composer'**](https://www.aitoc.com/docs/guides/composer.html#extensions-installation-via-composer).

As your next steps, run these CLI commands:

```
composer require aitoc/smtp
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Extension features

| Feature | Specification |
| -------- | -------- |
| **Soft configuration** | Store owners may easily reset the information about any attributes with many options. Your mail will be flexibly configured and work much faster, depending on your purposes.   |
| **Supporting trusted SMTP providers** | It is good to have your own SMTP server, however, in order to save time in the configuration for your email business, you may easily customize from one of the famous email service vendors, including Gmail, Yahoo, Outlook, Mail.com, Hotmail, Office365, O2 Mail, Send In Blue, and many more.   |
| **Email logging** | You get a smart block that keeps all sent email logs. It allows checking back who sent an email and how it was sent. Besides, you can check the current status (pending, in process or failed) as well as the exact sending time. Moreover, it is possible to find mistakes when checking the detailed logs.     |
| **Debug mode** | This feature is designed to test emails, check SMTP connection, and run self-testing of the module (without sending emails). The SMTP Debug mode allows store owners to manage, preview or review the time the email created.    |
| **Testing emails before sending** | This useful feature is aimed to help you to define whether the current email setting is work properly or not. Running tests is about the visual examination that can be performed by you. It will provide general looking back at the email again.    |
| **Export Email to CSV Files (coming soon)** | The extension will also help to export an email to the CSV file. Save your time and export your email into CSV.      |


**OTHER FEATURES**
- Use Your Own SMTP Server
- Sending the test emails before sending officially
- Ensure all emails will be sent to desired customers
- Email logging with detailed view of every letter
- Delete email log manually
- Debug mode by SMTP email settings to test
- Supports flexible servers
- Check and preview sent emails

## User Guide

Avoid unprofessional email management and do not find shipment information, order confirmation, invoices, or other important messages in a spam box. **SMTP Email Configuration for Magento 2 by Aitoc** is what can really assist to get success.
Here are the details about the configuration in the extension’s backend.

## Emails Log

Get the access here: ***REPORTS → SMTP → EMAILS LOG***. From here, you are able to track all the sent email from the server to customers.

[![](https://fstorage.aitoc.com/documentation/smtp-m2/emails.png)](https://fstorage.aitoc.com/documentation/smtp-m2/emails.png)

By clicking [![](https://fstorage.aitoc.com/documentation/smtp-m2/select.png)](https://fstorage.aitoc.com/documentation/smtp-m2/select.png) in each mail, you can have a general looking at the display which how your email will reach customer’s eyes. Hit [![](https://fstorage.aitoc.com/documentation/smtp-m2/clear.png)](https://fstorage.aitoc.com/documentation/smtp-m2/clear.png) to clear all the archived emails after checking carefully.

## Configuration

### General Settings

You should be at Admin Panel in order to configure general settings: ***STORES → CONFIGURATION → AITOC EXTENSIONS → SMTP → GENERAL SETTINGS***.
Choose **'Yes'** to enable Aitoc SMTP on.

[![](https://fstorage.aitoc.com/documentation/smtp-m2/gen_set.png)](https://fstorage.aitoc.com/documentation/smtp-m2/gen_set.png)

### SMTP Settings

Still from the same structure with **'General Settings'**, scroll down to see **'SMTP Settings'**.

[![](https://fstorage.aitoc.com/documentation/smtp-m2/smtp.png)](https://fstorage.aitoc.com/documentation/smtp-m2/smtp.png)

- **'Use Pre-Defined SMTP Providers'**. To see the field **'Provider'**, select **'Yes'** in the field **'Use Pre-Defined SMTP Providers'**. Such fields as **'Host'**, **'Port'**, **'Connection Security'** and **'Authentication Method'**, which are compatible with the SMTP provider you had chosen, will be filled automatically.

[![](https://fstorage.aitoc.com/documentation/smtp-m2/yes.png)](https://fstorage.aitoc.com/documentation/smtp-m2/yes.png)

At the moment we support nearly 30 SMTP email service providers.

[![](https://fstorage.aitoc.com/documentation/smtp-m2/provider.png)](https://fstorage.aitoc.com/documentation/smtp-m2/provider.png)

- At the **'Host'** field, type your Host name or IP address of SMTP Server. You can also custom STMP Provider’s Host name at here. If you had chosen the provider at the above field, you can give this step a free pass.

- **'Port'** is a specific gate where emails will be sent through. You can also pass this step if you had chosen the provider from the first place. In general, there will be 3 kinds of *Default Port*:
1. *Port 25*: Emails sent by other Protocol which different SSL will be sent through this portal
2. *Port 465*: Emails sent by other Protocol SSL will be sent through this portal
3. *Port 578*: Emails sent by other Protocol TLS will be sent through this portal

- **'Authentication Method'**. If you hadn’t chosen the provider before, please note those basic methods:
1. *Login/Password*: Authentication by login to the account through Username and Password that will be filled in the next field. Most of provider will require this method.
2. *CRAM-MD5*.

- **'Username'**: where you enter the account name matching format of the SMTP Provider you had selected.

- **'Password'**: password of the Username. After saving, the password will be encrypted into [![](https://fstorage.aitoc.com/documentation/smtp-m2/pass.png)](https://fstorage.aitoc.com/documentation/smtp-m2/pass.png).

- **'Connection Security'**: pass this step if you had chosen the provider, or you can select one of the providing protocol below here:
1. *None*: when you select this protocol, you have to accept all the risk may occur in the process of sending.
2. *SSL* stands for Secure Socket Layer. This protocol ensures that all data exchanged between the web server and the browser is secure and stay safe.
3. *TLS* means Transport Layer Security. This protocol secures data or messages and validates the integrity of messages through message authentication codes.

- **'Send Test To E-mail'**. This is the field for you to test the operation of the extension. After filling all fields, click [![](https://fstorage.aitoc.com/documentation/smtp-m2/send.png)](https://fstorage.aitoc.com/documentation/smtp-m2/send.png). If the information entered is valid, a successful email notification will be sent from Username to Email Test. That email will have the following content:

[![](https://fstorage.aitoc.com/documentation/smtp-m2/frame.png)](https://fstorage.aitoc.com/documentation/smtp-m2/frame.png)

## Log Settings

Still from the same structure with **'SMTP Settings'**, scroll down to see **'Log Settings'**.

To see the field **'Log Clean Every (days)'**, select **'Yes'** in the field **'Enable Log Outgoing Emails'**.

[![](https://fstorage.aitoc.com/documentation/smtp-m2/log.png)](https://fstorage.aitoc.com/documentation/smtp-m2/log.png)

The **'Log Clean Every (days)'** field limits the storage time for the email you sent. After that limited number of days, Email will automatically delete. If you do not want to delete the emails, set **zero** in the field blank.

## Emails Settings

Still from the same structure with **'Log Settings'**, scroll down to see **'Emails Settings'**.

Magento sends only the HTML part of the message, so some spam filters give such messages a higher spam score. In order to avoid this problem here the **Plain Text Part** can be added manually for each e-mail template.

[![](https://fstorage.aitoc.com/documentation/smtp-m2/emails_set.png)](https://fstorage.aitoc.com/documentation/smtp-m2/emails_set.png)

You can also create **CC Emails** (copies) and **BCC Emails** (hidden copies).

## Debug

Still from the same structure with **'Emails Settings'**, scroll down to see **'Debug'**.

[![](https://fstorage.aitoc.com/documentation/smtp-m2/debug.png)](https://fstorage.aitoc.com/documentation/smtp-m2/debug.png)

The **Debug Mode** is helpful in testing all Magento 2 SMTP email settings and identifying wrong email settings.

[![](https://fstorage.aitoc.com/documentation/smtp-m2/debug2.png)](https://fstorage.aitoc.com/documentation/smtp-m2/debug2.png)

##Reasons to choose Aitoc:

Aitoc proposes a full range of Magento development services, supporting e-commerce businesses with strong expertise and serious experience.

- **100+** Magento extensions built
- **800+** development projects completed
- **3000+** positive reviews
- **20000+** happy clients in over 100 countries, and counting

More FREE Magento 2 Extensions by Aitoc on GitHub
---
- [Aitoc Core Extension](https://github.com/aitoc/magento-2-core)
- [Google Customer Reviews](https://github.com/aitoc/magento-2-google-customer-reviews)

Other Magento 2 Extensions by Aitoc
---
- [Custom Product Designer](https://www.aitoc.com/magento-2-custom-product-designer.html)
- [Orders Export and Import](https://www.aitoc.com/magento-2-orders-export-and-import.html)
- [Advanced Permissions](https://www.aitoc.com/magento-2-advanced-permissions.html)
- [Product Units and Quantities](https://www.aitoc.com/magento-2-units-and-quantities.html)
- [Dimensional Shipping](https://www.aitoc.com/magento-2-dimensional-shipping.html) 
- [Email Marketing Suite](https://www.aitoc.com/magento-2-email-marketing-suite.html) 
- [Google Page Speed Optimization](https://www.aitoc.com/magento-2-google-pagespeed-optimization-extension.html) 
- [Pre-Orders](https://www.aitoc.com/magento-2-pre-orders.html) 
- [Free Gift](https://www.aitoc.com/magento-2-free-gift.html)
- [Follow Up Emails](https://www.aitoc.com/magento-2-follow-up.html) 
- [Shipping Rules](https://www.aitoc.com/magento-2-shipping-rules.html) 
- [Shipping Restrictions](https://www.aitoc.com/magento-2-shipping-restrictions.html) 
- [Shipping Table Rates & Methods](https://www.aitoc.com/magento-2-shipping-table-rates.html) 

See more [**Magento 2 extensions**](https://www.aitoc.com/magento-2-extensions.html).

**THANKS FOR CHOOSING** [![](https://fstorage.aitoc.com/documentation/smtp-m2/ext.png)](https://fstorage.aitoc.com/documentation/smtp-m2/ext.png)
