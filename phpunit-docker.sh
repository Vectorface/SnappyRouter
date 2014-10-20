#!/bin/sh
command -v docker >/dev/null 2>&1 || { echo >&2 "Docker is required to run the test suite against multiple versions of PHP. Please just use ./vendor/bin/phpunit."; exit 1; }

for phpVersion in 5.3 5.4 5.5 5.6
do
    echo ""
    docker run -v "$(pwd)":/opt/source -i -t -w /opt/source dbruce/debian7-php${phpVersion} php -v
	docker run -v "$(pwd)":/opt/source -i -t -w /opt/source dbruce/debian7-php${phpVersion} /opt/source/vendor/bin/phpunit
done
