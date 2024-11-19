#!/bin/bash

# Path to the folder with Flask backend relative to the Moodle root
BACKEND_PATH=${PWD}"/local/asystgrade/flask_ml_api"
echo $BACKEND_PATH

# Go to the desired folder
cd "$BACKEND_PATH" || {
  echo "Folder with Flask API not found.";
  exit 1;
}

# Run Docker Compose to deploy and run the container
docker-compose up -d --build

# Message about successful deployment
echo "Flask-бекенд развернут и запущен."