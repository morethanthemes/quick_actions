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

.PHONY: dev reset login stop ddev-clean ddev-start link-module sync-module
