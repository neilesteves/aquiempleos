README
======

This directory should be used to place project specfic documentation including
but not limited to project notes, generated API/phpdoc documentation, or
manual files generated or hand written.  Ideally, this directory would remain
in your development environment only and should not be deployed with your
application to it's final production location.


Setting Up Your VHOST
=====================

The following is a sample VHOST you might want to consider for your project.

<VirtualHost *:80>
    DocumentRoot "aptitus\src\public"
    ServerName devel.aptitus.info
    ErrorLog "logs/aptitus-error.log"
    CustomLog "logs/aptitus-access.log" common
    <Directory "aptitus\src\public">
        Options Indexes FollowSymLinks
        AllowOverride all
        Order Deny,Allow
        Deny from none
        Allow from all
    </Directory>
</VirtualHost>
<VirtualHost *:80>
    DocumentRoot "aptitus\src\public\elements"
    ServerName e.devel.aptitus.info
    ErrorLog "logs/e-aptitus-error.log"
    CustomLog "logs/e-aptitus-access.log" common
    <Directory "aptitus\src\public\elements">
        Options Indexes FollowSymLinks
        AllowOverride all
        Order Deny,Allow
        Deny from none
        Allow from all
    </Directory>
</VirtualHost>
<VirtualHost *:80>
    DocumentRoot "aptitus\src\public\static"
    ServerName s.devel.aptitus.info
    ErrorLog "logs/s-aptitus-error.log"
    CustomLog "logs/s-aptitus-access.log" common
    <Directory "aptitus\src\public\static">
        Options Indexes FollowSymLinks
        AllowOverride all
        Order Deny,Allow
        Deny from none
        Allow from all
    </Directory>
</VirtualHost>
