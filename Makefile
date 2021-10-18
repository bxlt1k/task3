include .env

init:
	@cd web && composer install
	@docker-compose up -d mysqldb
	while [ ![@migrate -path=migrate/migrations -database "mysql://dev:dev@tcp(task3.loc:8989)/test" up] ]; do done

start:
	@docker-compose up -d

stop:
	@docker-compose down

restart:
	@docker-compose down
	@docker-compose up -d