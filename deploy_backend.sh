#!/bin/bash

# Путь к папке с Flask-бекендом относительно корня Moodle
BACKEND_PATH=${PWD}"/local/asystgrade/flask_ml_api"
echo $BACKEND_PATH

# Переход в нужную папку
cd "$BACKEND_PATH" || { echo "Папка с Flask API не найдена."; exit 1; }

# Запуск Docker Compose для развертывания и запуска контейнера
docker-compose up -d --build

# Сообщение об успешном развертывании
echo "Flask-бекенд развернут и запущен."