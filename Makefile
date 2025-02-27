.PHONY: up down build install migrate fresh test npm-install npm-build npm-dev logs ps key seed

# Start the application
up:
	./vendor/bin/sail up -d

# Stop the application
down:
	./vendor/bin/sail down

# Build containers
build:
	./vendor/bin/sail build

# Install composer dependencies
install:
	docker run --rm --interactive --tty \
		--volume $(PWD):/app \
		composer install

# Run migrations
migrate:
	./vendor/bin/sail artisan migrate

# Refresh
refresh:
	./vendor/bin/sail artisan migrate:fresh

# Clear
clear:
	./vendor/bin/sail artisan config:clear
	./vendor/bin/sail artisan cache:clear
	./vendor/bin/sail artisan view:clear
	./vendor/bin/sail artisan route:clear

# Install npm dependencies
npm-install:
	./vendor/bin/sail npm install

# Build assets
npm-build:
	./vendor/bin/sail npm run build

# Run npm dev
npm-dev:
	./vendor/bin/sail npm run dev

# View logs
logs:
	./vendor/bin/sail logs

# List containers
ps:
	./vendor/bin/sail ps

# Generate application key
key:
	./vendor/bin/sail artisan key:generate

# Seed database
seed:
	./vendor/bin/sail artisan db:seed
