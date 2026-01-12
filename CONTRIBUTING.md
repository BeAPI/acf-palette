# Local Dev

## Installation

```shell
# Composer installation
composer install -o
./vendor/bin/psalm-plugin enable humanmade/psalm-plugin-wordpress

# Add playwright locally
npx playwright install --with-deps

# Install dependencies
npm install

# Env start, this will launch the environment with the PHP version specified into `.wp-env.json` and latest version of WordPress.
npm run env:start

# Install and activate ACF pro, for example :
npm run env:cli -- wp plugin install --activate https://composer.beapi.fr/dist/wpengine/advanced-custom-fields-pro/wpengine-advanced-custom-fields-pro-6.2.7.zip
```
