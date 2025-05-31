EXEC := "docker compose exec php"
CWD := `docker compose exec php pwd`
TOOLS_DIR := CWD + "/tools"
PHP := EXEC + " php"
COMPOSER := EXEC + " composer"
SYMFONY := EXEC + " symfony"

# *******************************
# Global aliases
# *******************************

# composer alias
composer +arguments:
	COMPOSER_ALLOW_SUPERUSER=1 {{COMPOSER}} {{arguments}}

# symfony console alias
console *arguments:
	{{SYMFONY}} console {{arguments}}

# *******************************
# Docker
# *******************************

up:
    docker compose up -d

down:
    docker compose down --remove-orphans

shell:
    docker compose exec -it php bash

# *******************************
# Application
# *******************************
component *name:
	{{SYMFONY}} console make:twig-component {{name}}
build:
	{{SYMFONY}} console tailwind:build

# Clear caches
cc env='dev':
	{{SYMFONY}} console cache:clear --env={{env}}

controller:
    {{SYMFONY}} console make:controller

entity:
    {{SYMFONY}} console make:entity

form:
	{{SYMFONY}} console make:form

voter:
	{{SYMFONY}} console make:voter

pretest:
    {{SYMFONY}} console --env=test doctrine:database:drop --force
    {{SYMFONY}} console --env=test doctrine:database:create
    {{SYMFONY}} console --env=test doctrine:schema:update --complete --force
    {{SYMFONY}} console --env=test doctrine:fixtures:load --no-interaction

test *path:
    {{PHP}} bin/phpunit {{path}}

# *******************************
# Database
# *******************************

create:
    {{SYMFONY}} console doctrine:database:create

migration:
    {{SYMFONY}} console make:migration

migrate:
    {{SYMFONY}} console doctrine:migrations:migrate

diff:
    {{SYMFONY}} console doctrine:migrations:diff

drop:
    {{SYMFONY}} console doctrine:database:drop --force

recreate: drop create migrate

# Load fixtures (add `--group=<name>` to launch specific fixtures)
seed *arguments:
	{{SYMFONY}} console doctrine:fixtures:load {{arguments}} --no-interaction

# *******************************
# Tools related
# *******************************

# Install php dependencies
install-php:
	{{COMPOSER}} install
	{{COMPOSER}} install --working-dir={{TOOLS_DIR}}/phpmd
	{{COMPOSER}} install --working-dir={{TOOLS_DIR}}/phpcs
	{{COMPOSER}} install --working-dir={{TOOLS_DIR}}/phpcsfixer
	{{COMPOSER}} install --working-dir={{TOOLS_DIR}}/phpstan
	{{COMPOSER}} install --working-dir={{TOOLS_DIR}}/phpcpd
	{{COMPOSER}} install --working-dir={{TOOLS_DIR}}/composer-require-checker
	{{COMPOSER}} install --working-dir={{TOOLS_DIR}}/rector

# Launch PHP CS Fixer (see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer)
fixer *paths='src':
	{{PHP}} {{TOOLS_DIR}}/phpcsfixer/vendor/bin/php-cs-fixer fix {{paths}}

# Launch PHPStan (see https://phpstan.org/)
stan *paths='src':
	{{PHP}} {{TOOLS_DIR}}/phpstan/vendor/bin/phpstan analyse -c phpstan.neon {{paths}}

# Launch PHP Mess Detector (see https://phpmd.org/)
phpmd *paths='src/':
	{{PHP}} {{TOOLS_DIR}}/phpmd/vendor/bin/phpmd {{paths}} text .phpmd.xml

# Launch PHP_CodeSniffer (see https://github.com/squizlabs/PHP_CodeSniffer)
phpcs:
	{{PHP}} {{TOOLS_DIR}}/phpcs/vendor/bin/phpcs -s --standard=phpcs.xml.dist

# Launch PHP_CodeBeautifier (see https://github.com/squizlabs/PHP_CodeSniffer)
phpcbf *paths='./src ./tests':
	{{PHP}} {{TOOLS_DIR}}/phpcs/vendor/bin/phpcbf --standard=phpcs.xml.dist {{paths}}

# Launch PHP Copy/Paste Detector (see https://github.com/sebastianbergmann/phpcpd)
phpcpd *paths='src/':
	{{PHP}} {{TOOLS_DIR}}/phpcpd/vendor/bin/phpcpd {{paths}}

# Launch Composer Require Checker (see https://github.com/maglnet/ComposerRequireChecker/)
check-deps:
	{{PHP}} {{TOOLS_DIR}}/composer-require-checker/vendor/bin/composer-require-checker check composer.json

rector:
	{{PHP}} {{TOOLS_DIR}}/rector/vendor/bin/rector process

# Launch all linting tools for backend code
lint-php: phpmd phpcpd phpcs stan fixer phpcbf check-deps

# *******************************
# Environment related
# *******************************

new-symfony:
	{{SYMFONY}} new temporary_dir --webapp
	{{EXEC}} rm -rf temporary_dir/.git
	{{EXEC}} cp -R temporary_dir/. .
	{{EXEC}} rm -rf temporary_dir
	{{COMPOSER}} install
	{{COMPOSER}} require symfony/apache-pack

# Deploy to production server.
# Append the ssh destination at the end, eg. my_ssh_server:/my/directory
deploy destination:
	rsync -avz --exclude-from=".rsyncignore.txt" --delete ./ {{destination}}
