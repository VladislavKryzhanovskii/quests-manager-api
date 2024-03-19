# quests-manager-api

### Настройка среды 

#### Требованием для запуска приложенияявляется наличие Docker

#### Запуск приложения:
```shell
git clone https://github.com/VladislavKryzhanovskii/quests-manager-api.git
cd quests-manager-api
make build 
make up 
make migrate
```
После чего приложение будет доступно по ссылке: http://127.0.0.1:888/


### Запуск unit-тестов:
```shell
make test
```
