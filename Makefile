.PHONY: test test-php83 test-php84

check-%:
	${if $($(*)), , ${error $(*) undefined}}


build: check-PHP_VERSION
	docker build \
		--network host \
		--ssh default \
		--secret id=github_token,env=GITHUB_TOKEN \
		--target vendor-export \
		--output ./ \
		-f "ci/test/php$(PHP_VERSION)/Dockerfile" .

build-php83: ## Build test container for PHP 8.3
	PHP_VERSION=8.3 make --no-print-directory build

build-php84: ## Build test container for PHP 8.4
	PHP_VERSION=8.4 make --no-print-directory build

test: build
	docker compose run --build --rm test \
		vendor/bin/phpunit \
		--testdox \
		--coverage-text=php://stdout --coverage-html=.tmp/reports/coverage

test-php83: ## Run tests with PHP 8.3
	PHP_VERSION=8.3 make --no-print-directory test

test-php84: ## Run tests with PHP 8.4
	PHP_VERSION=8.4 make --no-print-directory test
