SHELL := bash

ifeq ($(origin .RECIPEPREFIX), undefined)
  $(error This Make does not support .RECIPEPREFIX. Please use GNU Make 4.0 or later)
endif

.RECIPEPREFIX = >
.DEFAULT_GOAL := help
.PHONY: init build-release

init:
> @mkdir -p ./{releases,archives,wikis}
> @composer install

build-release: init
> @mkdir -p ./{releases,archives,wikis}
> @composer update --no-dev --optimize-autoloader
> @tar czf releases/ferme-`date +"%Y%m%d"`.tgz \
> --exclude='.[^/]*' \
> --exclude="releases" \
> --exclude="Makefile" \
> --exclude="ferme.config.php" \
> --exclude="ruleset.xml" \
> --exclude="wikis/*" \
> --exclude="archives/*" \
> --exclude="composer*" \
> --exclude=".git*" \
> --exclude=".vscode" \
> . \
> --transform 's/^./ferme/'
> @composer update

help:
> @printf "Usage make [init|build-release]\n"