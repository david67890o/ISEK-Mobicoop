## This makefile is simply shortcurts for mobicoop docker 


pink:=$(shell tput setaf 200)
blue:=$(shell tput setaf 27)
green:=$(shell tput setaf 118)
reset:=$(shell tput sgr0) 

ifeq ($(shell uname),Darwin)
  os=darwin
else
  os=linux
endif

install:
	$(info $(pink)------------------------------------------------------)
	$(info $(pink)Creating build/cache folders)

	mkdir -p build/cache;\
	mkdir -p build/cache2;\

	$(info $(pink)Make ($(os)): Installing monorepo root deps...)

	# Using docker-sync for macos only
	@if [ $(os) = "darwin" ]; then\
		docker-sync start; \
    fi

	docker-compose -f docker-compose-builder-$(os).yml run --rm install
	@make -s install-deps
	@make -s build-admin

	# $(info $(pink)------------------------------------------------------)
	$(info $(pink)Make ($(os)): Installing monorepo DONE)
	$(info $(pink)------------------------------------------------------$(reset))

install-deps:
	$(info $(green)------------------------------------------------------)
	$(info $(green)Make ($(os)): Installing api-client-admin deps...)
	$(info $(green)------------------------------------------------------$(reset))
	docker-compose -f docker-compose-builder-$(os).yml run --rm install-all

build-admin:
	$(info $(blue)------------------------------------------------------)
	$(info $(blue)Make ($(os)): Building admin...)
	$(info $(blue)------------------------------------------------------$(reset))
	docker-compose -f docker-compose-builder-$(os).yml run --rm build-admin

fixtures:
	$(info $(pink)------------------------------------------------------)
	$(info $(pink)Make ($(os)): Generating fixtures...)
	$(info $(pink)------------------------------------------------------$(reset))
	docker-compose -f docker-compose-builder-$(os).yml run --rm fixtures

start:
	$(info Make ($(os)): Starting Mobicoop-plateform environment containers.)
	docker-compose -f docker-compose-$(os).yml up  -d --always-recreate-deps --force-recreate
 
stop:

	#  Using docker-sync for darwin macos only
	@if [ $(os) = "darwin" ]; then\
		docker-sync stop; \
    fi

	$(info Make ($(os)): Stopping Mobicoop-plateform environment containers.)
	docker-compose -f docker-compose-$(os).yml stop 
 
restart:
	$(info Make ($(os)): Restarting Mobicoop-plateform environment containers.)
	@make -s stop
	@make -s start

remove:
	$(info Make ($(os)): Stopping Mobicoop-plateform environment containers.)
	docker-compose -f docker-compose-$(os).yml down -v 
 
clean:

	#  Using docker-sync for darwin macos only
	@if [ $(os) = "darwin" ]; then\
		docker-sync clean; \
    fi

	@make -s stop
	@make -s remove
	# docker system prune --volumes --force
	rm -rf node_modules api/vendor client/vendor client/node_modules admin/node_modules

logs: 
	docker logs -f --tail=100 mobicoop_platform | sed -e 's/^/[-- containerA1 --]/' &
	docker logs -f --tail=100 mobicoop_db | sed -e 's/^/[-- containerM2 --]/' &

go-app:
	docker exec -it mobicoop_platform zsh

go-db:
	docker exec -it mobicoop_db bash