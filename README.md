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
После чего приложение будет доступно по ссылке: http://127.0.0.1:888

### Запуск unit-тестов:
```shell
make test
```

### Методы API:
UserController
- POST /api/users - добавление пользователя
- GET /api/users?page=1&limit=10 - получение всех пользователей (Пагинация: page - номер страницы, limit - максимальное количество элементов на странице)
- GET /api/users/{id} - получение полной информации по пользователю
- PATCH|PUT /api/users/{id} - обновление информации о пользователе
- DELETE /api/users/{id} - удаление пользователя
- PATCH /api/users/{id}/quests/{questId} - добавление задания пользователю (событие выполнения задания)
QuestController
- POST /api/quests - добавление задания
- GET /api/quests?page=1&limit=10 - получение всех заданий (Пагинация: page - номер страницы, limit - максимальное количество элементов на странице)
- GET /api/quests/{id} - получение полной информации по задаче
- PATCH /api/quests/{id} - частичное обновление информации о задаче
- PUT /api/quests/{id} - полное обновление информации о задаче (если задача не найдена, будет создана новая)
- DELETE /api/quests/{id} - удаление задачи

