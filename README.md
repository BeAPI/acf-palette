# Be API - ACF Color Palette

Plugin WordPress pour ajouter des champs personnalisés à Advanced Custom Fields (ACF).

## Fonctionnalités

### Champ Theme Color

Un nouveau type de champ ACF qui récupère automatiquement les couleurs définies dans le fichier `theme.json` du thème actuel et les propose à la sélection.

#### Utilisation

1. **Créer un nouveau champ ACF**
   - Type de champ : "Theme Color"
   - Le champ affichera automatiquement toutes les couleurs définies dans `wp-content/themes/[theme]/theme.json`

2. **Options du champ**
   - **Color Source** : Choisir la source des couleurs
     - `Settings Palette` : Uniquement `settings.color.palette`
     - `Custom Colors` : Uniquement `custom.color`
     - `Both` : Combine les deux sources (settings + custom)
   - **Allow Null** : Permet de ne pas sélectionner de couleur
   - **Default Value** : Valeur par défaut
   - **Color Filter Method** : Choisir entre exclure ou inclure des couleurs
   - **Exclude Colors** : Permet d'exclure certaines couleurs de la sélection
   - **Include Colors** : Permet de n'inclure que certaines couleurs spécifiques
   - **Return Format** : Format de retour (Slug, Hex Color, ou Both)

3. **Formats de retour**
   - **Slug** : Retourne le slug de la couleur (ex: `primary-orange`)
   - **Hex Color** : Retourne la valeur hexadécimale (ex: `#FF6745`)
   - **Both (Array)** : Retourne un tableau avec `name`, `slug` et `color`

#### Exemple d'utilisation en PHP

```php
// Récupérer le slug (si configuré en "Slug")
$color_slug = get_field('my_color_field');
// 'primary-orange'

// Récupérer la valeur hexadécimale (si configuré en "Hex Color")
$color_hex = get_field('my_color_field');
// '#FF6745'
echo "background-color: {$color_hex};";

// Récupérer toutes les informations (si configuré en "Both (Array)")
$color_data = get_field('my_color_field');
// $color_data = [
//     'name'  => 'Primaire orange',
//     'slug'  => 'primary-orange',
//     'color' => '#FF6745'
// ];

// Utilisation avec le format Array
echo "background-color: {$color_data['color']};";
echo "Title: {$color_data['name']}";
```

#### Méthodes de filtrage des couleurs

Le champ offre deux méthodes pour filtrer les couleurs disponibles :

1. **Exclude Colors** (par défaut) : Exclure certaines couleurs de la sélection
2. **Include Colors** : N'inclure que certaines couleurs spécifiques

Ces options sont mutuellement exclusives et s'affichent conditionnellement selon la méthode choisie.

#### Sources de couleurs disponibles

Le plugin peut récupérer les couleurs depuis trois sources différentes dans le `theme.json` :

##### 1. Settings Palette (par défaut)

```json
{
  "settings": {
    "color": {
      "palette": [
        {
          "name": "Nom de la couleur",
          "slug": "slug-de-la-couleur",
          "color": "#FF6745"
        }
      ]
    }
  }
}
```

##### 2. Custom Colors

```json
{
  "$schema": "https://schemas.wp.org/trunk/theme.json",
  "version": 3,
  "custom": {
    "color": {
      "environnement-400": "#c9dcba",
      "environnement-900": "#395f0f",
      "entreprises-400": "#c1bcff",
      "entreprises-900": "#001cb7"
    }
  },
  "settings": {
    // ... autres paramètres
  }
}
```

Les couleurs custom utilisent une structure simplifiée où le slug est la clé et la couleur hex est la valeur. Le nom lisible est généré automatiquement à partir du slug :

- `environnement-400` → "Environnement 400"
- `services-publics-900` → "Services Publics 900"

##### 3. Both (Settings + Custom)

Combine les couleurs des deux sources. Si un slug existe dans les deux sources, la version de `custom.color` sera prioritaire.

```json
{
  "settings": {
    "color": {
      "palette": [
        {
          "name": "Primaire",
          "slug": "primary",
          "color": "#FF6745"
        }
      ]
    }
  },
  "custom": {
    "color": {
      "environnement-400": "#c9dcba",
      "entreprises-400": "#c1bcff"
    }
  }
}
```

**Résultat avec "Both" :** Les 3 couleurs seront disponibles (`primary`, `environnement-400`, `entreprises-400`)

**Note :** Pour les couleurs custom, le nom est automatiquement généré à partir du slug. Par exemple :

- `environnement-400` devient "Environnement 400"
- `services-publics-900` devient "Services Publics 900"

## Installation

1. Copier le plugin dans `wp-content/plugins/beapi-acf-palette/`
2. Activer le plugin dans l'administration WordPress
3. Le champ "Theme Color" sera disponible dans ACF

## Dépendances

- WordPress 5.0+
- Advanced Custom Fields Pro 5.0+
- Un thème avec un fichier `theme.json` contenant une palette de couleurs

## Support

Pour toute question ou problème, contactez l'équipe technique Be API.
