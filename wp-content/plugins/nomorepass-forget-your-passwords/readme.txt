=== NoMorePass Login ===
Contributors: biblioeteca
Donate link: https://www.biblioeteca.com/biblioeteca.web/dona
Tags: password, login, nomorepass, wordpress login, wp login form, wp-login, two-factor, password manager, safe login, qr login, mobile
Requires at least: 3.5
Tested up to: 5.4
Stable tag: 1.9.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Use your mobile phone to login into wordpress. Allow users instant registration. Fully protection against force brute attacks

== Description ==
<strong>NoMorePass</strong> is a secure and easy to use technology to provide you two factor autentication on every situation. This plugin allows you to login to wordpress using just your mobile phone, with no other requirement. No registering, no API keys, install, activate and that's all.
Your passwords will be <strong>only on your mobile phone</strong>, no copies over your computers, not even on nomorepass servers, fully anonymous. Login just scanning a one-time qr-code.

* <strong>Safe</strong> Your passwords are encrypted all the time, all transfers are on https and the credentials are encrypted with single-use keys.
* <strong>Personal</strong> Your passwords always go with you. No servers, no insecure transfers, no browser dependencies. Fully anonymous.
* <strong>Easy</strong> Magic! You can send passwords to your favorite websites without any effort. Just scan a qr-code with the app.

<strong>IMPORTANT</strong>: Install the app in your phone:

* [Google Play](https://play.google.com/store/apps/details?id=com.biblioeteca.apps.NoMorePass) Android.
* [Apple Appstore](https://itunes.apple.com/us/app/no-more-pass/id1199780162) iOS 

<strong>Instant user registration</strong>
You have a new configuration option named "Auto-login after registration" that allows your users scan the provided qr-code only providing username and email, then the password is sent to the mobile phone and the user is registered immediately.
Register your users in just one step, safely.

<stong>Avoid force brute attacks</strong>
You can force users to use NoMorePass app to login to your site, making impossible force-brute attacs. Even if the attackers guess the correct user an password they will be unable to enter if not using the mobile app.

<strong>NoMorePass support</strong>,
if you find a bug please open a ticket in the support request or go to [NoMorePass.com](https://www.nomorepass.com).
Every issue will be fixed asap!</strong>

<strong> NoMorePass</strong> plugin is also compatible with any plugin that hooks in the login form, including

1. BuddyPress,
1. bbPress,
1. Limit Login Attempts,
1. Captcha plugins.
1. etc.

<strong>Perfect replacement for old two-factor authentication plugin Clef</strong>.

== Installation ==

[youtube https://youtu.be/kCLf-AKD4NE ]

Install NoMorePass plugin via wordpress dashboard :
	
1. Go to the Plugins Menu in WordPress.
1. Search for plugin "NoMorePass".
1. Click "Install".
1. After Installation click activate to start using the NoMorePass plugin on your website.

* Go to NoMorePass plugin  from Dashboard menu.
* Enable NoMorePass plugin feature... And that's all
 
Install  NoMorePass plugin via FTP
    
1.  Download the NoMorePass plugin
1.  Unzip NoMorePass plugin
1.  Copy the NoMorePass plugin folder 
1.  Open the ftp \\wp-content\\plugins\\
1.  Paste the folder inside plug-ins folder 
1.  Go to admin panel => open item "Plugins" => activate NoMorePass plugin

<strong> To use the plugin you need to install the mobile app NoMorePass </strong>

* [Google Play](https://play.google.com/store/apps/details?id=com.biblioeteca.apps.NoMorePass).
* [Apple Appstore](https://itunes.apple.com/us/app/no-more-pass/id1199780162) iOS 

More info on [NoMorePass.com](https://www.nomorepass.com).

== Configuration ==

In the admin page you have 5 different options:

1. Show login form : shows or hide the user and password fields. If hidden only using QR is possible login.
1. Show password reset : shows or hide the password field when resetting password. If hidden the password is sent to the mobile phone using QR directly.
1. Auto-launch QR : makes the NoMorePass QR be launched when login page loads.
1. Auto-login : if you select this option the user will login directly after the registration without email verification. Use with caution, this option will reduce the registration dramatically (just 1 step) but the emails are not verified (but you know people registered have used nomorepass, so they are humans).
1. Only Nomorepass : allows logins only using nomorepass app
1. Custom Logo: allows to upload a new icon for login
1. Custom Message: allows to change the login message

== Frequently Asked Questions ==

= Do I need the mobile app? =

Yes. But you can login using username and password too depending on the configuration you choose.

= It is the app free? =

Yes. See [NoMorePass.com] (https://www.nomorepass.com/wp/tarifas/?lang=en)

= May I remove the plugin? =

Yes, you can and your user credentials remain untouched.

= What if I loose my phone? =

You can make a backup for your mobile phone credentials and restore in a new install, see app instructions.

== Screenshots ==

1. Regular login with No More Pass button
2. Decorated Login
3. No More Pass working
4. Configuration page
5. Login without username / password
6. Auto-registration enabled

== How it works ==

[youtube https://youtu.be/OVL7cuiS77g ]

== Changelog ==

= 1.9.3 =
* Adapted to Worpress 5.3
* Fixed error that prevents to hide login fields

= 1.9.1 =
* Fixed error is no other plugin is using jquery in admin page

= 1.9.0 =
* Allows custom modifications of text
* Allows customised icon
* Enhanced configuration page
* Tested WordPress 4.9.7

= 1.8.0 =
* New configuration option to allow only NoMorePass logins

= 1.7.0 =
* New configuration screen
* New modes (show only QR - auto-launch qr)
* Auto-login after registration
* Automatic registration using NoMorePass
* Tested Wordpress 4.9.1

= 1.6.0 =
* Reset password allows to receive the password on NoMorePass scanning a QRcode

= 1.5.0 =
* Support for UTF-8 encoded passwords

= 1.4.0 =
* New graphics elements

= 1.0.1 =
* Tested wordpress 4.8
* Italian traslation thanks Night train (nighttrain@aruba.it)

= 1.0 =
* First version

