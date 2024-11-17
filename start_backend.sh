#!/bin/bash

# Перейти в директорию плагина (замените путь, если необходимо)
BACKEND_PATH=${PWD}"/local/asystgrade/flask_ml_api"

# Проверка, существует ли директория
if [ -d "$BACKEND_PATH" ]; then
    echo "Перехожу в $PLUGIN_PATH"
    cd "$BACKEND_PATH" || exit 1

    # Запустить контейнеры
    echo "Запускаю контейнер с Python бэкендом..."
    docker-compose up -d
    echo "Контейнер с Python успешно запущен."
else
    echo "Ошибка: Директория $BACKEND_PATH не найдена."
    exit 1
fi
