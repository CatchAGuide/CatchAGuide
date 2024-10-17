# Catch A Guide

[![pipeline status](https://gitlab.webschuppen.com/webschuppen/catch-a-guide/badges/staging/pipeline.svg)](https://gitlab.webschuppen.com/webschuppen/catch-a-guide/-/commits/staging)

## Installation
- Clone repository to your local machine
- Go to root folder of repository
- Execute ``ddev start``

## Deployment
- Deploy to staging (https://catchaguide.neueseite.eu) with merging develop into staging
- Deploy to production via SFTP

## Commands
- Console commands are available with ``ddev artisan ...``
- Artisan options can be listed with ``ddev artisan``
- Clear cache ``ddev artisan cache:clear``
- Open Mailhog: ``ddev launch --mailhog``
- Open PHPMyAdmin: ``ddev launch --phpmyadmin``