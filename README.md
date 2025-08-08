# Be API - ACF Color Palette

Plugin WordPress pour ajouter des champs personnalisés à Advanced Custom Fields (ACF).

## Fonctionnalités

### Champ Theme Color

Un nouveau type de champ ACF qui récupère automatiquement les couleurs définies dans le fichier `theme.json` du thème actuel et les propose à la sélection.

#### Utilisation

1. **Créer un nouveau champ ACF**
   - Type de champ : "Theme Color"
   - Le champ affichera automatiquement toutes les couleurs définies dans `wp-content/themes/[theme]/src/theme-json/settings-color.json`

2. **Options du champ**
   - **Allow Null** : Permet de ne pas sélectionner de couleur
   - **Default Value** : Valeur par défaut
   - **Color Filter Method** : Choisir entre exclure ou inclure des couleurs
   - **Exclude Colors** : Permet d'exclure certaines couleurs de la sélection
   - **Include Colors** : Permet de n'inclure que certaines couleurs spécifiques
   - **Return Format** : Format de retour (Value, Label, ou Array)

3. **Formats de retour**
   - **Value (Slug)** : Retourne le slug de la couleur (ex: `primary-orange`)
   - **Hex Color** : Retourne la valeur hexadécimale (ex: `#FF6745`)
   - **Label** : Retourne le nom de la couleur (ex: `Primaire orange`)
   - **Array** : Retourne un tableau avec `value`, `label` et `color`

#### Exemple d'utilisation en PHP

```php
// Récupérer le slug (si configuré en "Value (Slug)")
$color_slug = get_field('my_color_field');

// Récupérer la valeur hexadécimale (si configuré en "Hex Color")
$color_hex = get_field('my_color_field');
echo "background-color: {$color_hex};";

// Récupérer le nom (si configuré en "Label")
$color_name = get_field('my_color_field');

// Récupérer toutes les informations (si configuré en "Array")
$color_data = get_field('my_color_field');
// $color_data = [
//     'value' => 'primary-orange',
//     'label' => 'Primaire orange',
//     'color' => '#FF6745'
// ];

// Utilisation directe en CSS avec le format Array
$color_hex = $color_data['color'];
echo "background-color: {$color_hex};";
```

#### Méthodes de filtrage des couleurs

Le champ offre deux méthodes pour filtrer les couleurs disponibles :

1. **Exclude Colors** (par défaut) : Exclure certaines couleurs de la sélection
2. **Include Colors** : N'inclure que certaines couleurs spécifiques

Ces options sont mutuellement exclusives et s'affichent conditionnellement selon la méthode choisie.

#### Structure attendue du theme.json

Le plugin s'attend à trouver les couleurs dans :

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
