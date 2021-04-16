SHELL := bash

.DEFAULT_GOAL := help
.PHONY: init build-release

PACKAGE_DIR := packages
YESWIKI_VERSION := cercopitheque
YESWIKI_RELEASE := 2021-02-18-2
YESWIKI_ZIPFILE := https://repository.yeswiki.net/${YESWIKI_VERSION}/yeswiki-${YESWIKI_VERSION}-${YESWIKI_RELEASE}.zip
TMP_FILE := /tmp/yeswiki-${YESWIKI_VERSION}-${YESWIKI_RELEASE}.zip
PACKAGE_FILES_TO_REMOVE := $(shell cat .package_cleaning)

init: build-package
	@mkdir -p ./{releases,archives,wikis,packages}
	@if [ ! -d "vendor" ]; then \
		composer install; \
	fi

gitpull:
	@printf "Get last version...\n"
	@git pull

update: clean gitpull init

build-package:
	@printf "Downloading yeswiki-${YESWIKI_VERSION}-${YESWIKI_RELEASE}.zip...\n"
	@wget -q -O ${TMP_FILE} $(YESWIKI_ZIPFILE)
	@printf "Extracting package...\n"
	@unzip -o -q -d ${PACKAGE_DIR}/ ${TMP_FILE}
	@printf "Cleaning package...\n"
	@rm ${TMP_FILE}
	@for file in ${PACKAGE_FILES_TO_REMOVE}; do \
		rm -rf ${PACKAGE_DIR}/${YESWIKI_VERSION}/$$file; \
	done
	@printf "patching YesWiki (Cookies)"
	patch -i patch/yeswiki_cookie.diff ${PACKAGE_DIR}/${YESWIKI_VERSION}/includes/YesWikiInit.php

clean:
	@printf "Deleting package...\n"
	@if [ -d ${PACKAGE_DIR}/${YESWIKI_VERSION} ]; then \
		rm -rf ${PACKAGE_DIR}/${YESWIKI_VERSION}; \
	fi
	@printf "Deleting vendor...\n"
	@if [ -d "vendor" ]; then \
		rm -rf "vendor"; \
	fi

build-release: init
	@mkdir -p ./{releases,archives,wikis}
	@composer update --no-dev --optimize-autoloader
	@tar czf releases/ferme-`date +"%Y%m%d"`.tgz \
	--exclude='.[^/]*' \
	--exclude="releases" \
	--exclude="Makefile" \
	--exclude="ferme.config.php" \
	--exclude="ruleset.xml" \
	--exclude="wikis/*" \
	--exclude="archives/*" \
	--exclude="composer.json" \
	--exclude="composer.lock" \
	--exclude=".git" \
	--exclude=".gitignore" \
	--exclude=".vscode" \
	--exclude="ferme.log" \
	--exclude="patch" \
	. \
	--transform 's/^./ferme/'
	@composer update

help:
	@printf "Usage make [init|update|build-release|clean]\n"