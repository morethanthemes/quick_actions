dev:
	cd .devsite && composer install && \
	ln -s ../../../ web/modules/custom/quick_actions || true && \
	ddev start && \
	ddev drush si -y && \
	ddev drush en quick_actions -y

reset:
	cd .devsite && \
	ddev drush si -y && \
	ddev drush en quick_actions -y

stop:
	cd .devsite && ddev stop
