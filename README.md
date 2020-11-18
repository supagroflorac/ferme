# Ferme

Installation automatisée de wikis ([YesWiki](https://yeswiki.net)) et interface d'administration.

## Installation

 * Décompresser l'archive dans le dossier cible.
 * Renommer le fichier ferme.config.sample.php en ferme.config.php et le renseigner.

## Mise à jour

 * Supprimer tous les fichiers sauf :
  * archives
  * wikis
  * ferme.config.php
 * Copier le contenu de l'archive d'installation dans le dossier.

## Construire une archive d'installation

Nécessite *git*, *make* et *composer*

Cloner le dépôt.
```bash
make build-release
```
la release sera dans le répertoire `releases`.

## Note
 * testé avec php 7.4
