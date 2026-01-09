# Contribuer

## Créer une nouvelle fonctionnalité ou un correctif

À partir de la branche `develop` à jour, créez votre branche `feature/<topic>` si c'est une nouvelle fonctionnalité ou `fix/<topic>` si c'est un correctif.

## Merger votre fonctionnalité ou correctif

Créez une Pull Request de votre branche vers `main`.

## Créer une nouvelle branche de release

Une fois votre branche mergée sur `main`, créez une nouvelle branche `release/X.X.X` (X.X.X correspondant à la nouvelle version de votre release).

Modifiez les fichiers `.plugin-data` et `beapi-acf-palette.php` pour mettre à jour la version.

```plain
{
 "version": "1.0.6",
 "slug": "beapi-acf-palette"
}
```

```php
/*
Version: 1.0.6
*/

define( 'BEAPI_ACF_PALETTE_VERSION', '1.0.6' );
```

Committez et poussez votre branche.

## Déployer une nouvelle release

Créez une Pull Request de votre branche `release/X.X.X` vers `main` et une autre Pull Request vers `develop`.

Une fois validée et mergée, un nouveau tag sera créé. Il faudra ensuite lancer la commande Satis pour mettre à jour <https://composer.beapi.fr/>.
