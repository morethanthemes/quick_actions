# Base config
MODULE_NAME=quick_actions
PROJECT_ROOT=$(CURDIR)
SRC_DIR=.
DEST_DIR=devsite/web/modules/custom/$(MODULE_NAME)
DDEV_DIR=devsite

# Start and install everything
dev:
	cd $(DDEV_DIR) && ddev start
	cd $(DDEV_DIR) && ddev composer install
	make link-module
	cd $(DDEV_DIR) && ddev drush si -y
	cd $(DDEV_DIR) && ddev drush en $(MODULE_NAME) -y

# Just (re)install the Drupal site
reset:
	cd $(DDEV_DIR) && ddev drush si -y
	cd $(DDEV_DIR) && ddev drush en $(MODULE_NAME) -y

# Quick login link
login:
	cd $(DDEV_DIR) && ddev drush uli

# Delete DDEV and stop it
ddev-clean:
	cd $(DDEV_DIR) && ddev delete --omit-snapshot
	cd $(DDEV_DIR) && ddev stop

# Start only DDEV
ddev-start:
	cd $(DDEV_DIR) && ddev start

# Stop DDEV
ddev-stop:
	cd $(DDEV_DIR) && ddev start


link-module:
	@echo "Creating symlinked module in $(SYMLINK_DIR)"
	mkdir -p $(SYMLINK_DIR)
	cd $(SYMLINK_DIR) && \
	ln -sf $(PROJECT_ROOT)/quick_actions.info.yml . && \
	ln -sf $(PROJECT_ROOT)/quick_actions.module . && \
	ln -sf $(PROJECT_ROOT)/quick_actions.routing.yml . && \
	ln -sf $(PROJECT_ROOT)/README.md . && \
	ln -sf $(PROJECT_ROOT)/LICENSE.txt . && \
	ln -sfn $(PROJECT_ROOT)/src src


# Symlink local files into the container project
link-module:
	@echo "🔗 Linking module to $(DEST_DIR)"
	mkdir -p $(DEST_DIR)
	cd $(DEST_DIR) && \
	ln -sf $(PROJECT_ROOT)/quick_actions.info.yml . && \
	ln -sf $(PROJECT_ROOT)/quick_actions.module . && \
	ln -sf $(PROJECT_ROOT)/quick_actions.routing.yml . && \
	ln -sf $(PROJECT_ROOT)/composer.json . && \
	ln -sf $(PROJECT_ROOT)/LICENSE.txt . && \
	ln -sf $(PROJECT_ROOT)/README.md . && \
	ln -sfn $(PROJECT_ROOT)/src src


# Rsync instead of symlinking
sync-module:
	@echo "🔁 Syncing module from $(SRC_DIR) to $(DEST_DIR)"
	mkdir -p $(DEST_DIR)
	rsync -av --delete \
		--exclude='devsite' \
		--exclude='.git' \
		--exclude='.ddev' \
		--exclude='.DS_Store' \
		--exclude='Makefile' \
		--exclude='README.md' \
		$(SRC_DIR)/ $(DEST_DIR)


# Set the default events view display (e.g. index or cards)
# make set-events-default path=index
# or
# make set-events-default path=cards
# set-events-default:
# 	@echo "⚙️  Setting default events view to page_$(path)"
# 	cd $(DDEV_DIR) && ddev drush cset quick_actions.settings events_default.view events --yes
# 	cd $(DDEV_DIR) && ddev drush cset quick_actions.settings events_default.display page_$(path) --yes

# Set a single default view path
# Usage: make set-default-path path=events/cards
# set-default-path:
# 	@echo "⚙️  Setting default path to /$(path)"
# 	cd $(DDEV_DIR) && ddev drush cset quick_actions.settings default_paths '[/$(path)]' --input-format=yaml --yes


# Set the full default_paths array
# Usage:
# make set-default-paths paths=/events/cards,/services/index
set-default-paths:
	@echo "⚙️  Setting default_paths to: [$(paths)]"
	cd $(DDEV_DIR) && ddev drush php-eval "\$$paths = explode(',', '$(paths)'); \Drupal::configFactory()->getEditable('quick_actions.settings')->set('default_paths', \$$paths)->save();"

get-default-paths:
	cd $(DDEV_DIR) && ddev drush cget quick_actions.settings default_paths

drush-cr:
	cd $(DDEV_DIR) && ddev drush cr


# Export just the events_index view from active config to module-local file
export-events-view:
	@echo "📤 Exporting 'events_index' view config..."
	cd $(DDEV_DIR) && ddev drush config:get views.view.events --format=yaml > ../config/dev/views.view.events.yml

# Import only the events_index view from file, no full config overwrite
import-events-view:
	@echo "📥 Importing 'events' view config (single import)..."
	cd $(DDEV_DIR) && ddev drush config:import --partial --source=/home/skounis/drupal/quick_actions/config/dev
	cd $(DDEV_DIR) && ddev drush cr

.PHONY: dev reset login stop ddev-clean ddev-start link-module sync-module
