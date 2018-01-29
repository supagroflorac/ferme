# ferme

Installation automatisée de wikis ([YesWiki](https://yeswiki.net)) et interface d'administration.

## Construire l'archive

Nécessite *git*, *curl* et *composer*
```bash
source <(curl -L https://gist.githubusercontent.com/daiko/6bcb701890d90e336c1fadd8470a20e8/raw/f0617cbd8ba3e036f0073c8ab13520e98a27d321/build_ferme.sh)
```

## Installation

 * Décompresser l'archive dans le dossier cible.
 * Renommer le fichier ferme.config.sample.php en ferme.config.php et le renseigner.

## Mise à jour

 * Supprimer tous les fichiers sauf :
  * archives
  * wikis
  * ferme.config.php
 * Copier le contenu de l'archive d'installation dans le dossier.

## Note
 * testé avec php 7.1
