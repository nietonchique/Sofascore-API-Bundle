# Dev helpers. Pick a PHP version with e.g. `make PHP=8.4 qa` (default 8.5).
# These run inside Docker as your host user, so you do NOT need PHP on the host
# and no root-owned files are left behind.
PHP ?= 8.5
DC  := PHP=$(PHP) docker compose run --rm --user $(shell id -u):$(shell id -g) php

.PHONY: help install test qa stan cs cs-fix deptrac shell ci

help: ## Show this help
	@grep -hE '^[a-z-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN{FS=":.*?## "}{printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2}'

install: ## Install dependencies
	$(DC) composer install --no-progress

test: ## Run the test suite
	$(DC) composer test

qa: ## cs-check + phpstan + deptrac + phpunit
	$(DC) composer qa

stan: ## Static analysis
	$(DC) composer stan

cs: ## Check coding standards
	$(DC) composer cs-check

cs-fix: ## Fix coding standards
	$(DC) composer cs-fix

deptrac: ## Check architecture layers
	$(DC) composer deptrac

shell: ## Open a shell in the container
	$(DC) bash

ci: install qa ## Fresh install + full QA (what CI does)
