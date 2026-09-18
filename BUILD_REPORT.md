# Build report

Target framework: Laravel 13.x / PHP 8.3+.

This runtime has PHP 8.4 but Composer is not installed and outbound DNS from the shell is unavailable, so framework dependencies cannot be downloaded here. The source is therefore verified with PHP syntax lint and project-structure/static checks. Run `composer install && php artisan test` in an internet-connected environment before production deployment.
