#!/bin/bash

# Go to plugin directory (replace path if necessary)
BACKEND_PATH=${PWD}"/local/asystgrade/flask_ml_api"

# Check if directory exists
if [ -d "$BACKEND_PATH" ]; then
    echo "Going to $PLUGIN_PATH"
    cd "$BACKEND_PATH" || exit 1

# Start containers
    echo "Starting container with Python backend..."
    docker-compose up -d
    echo "Container with Python successfully started."
else
    echo "Error: Directory $BACKEND_PATH not found."
    exit 1
fi
