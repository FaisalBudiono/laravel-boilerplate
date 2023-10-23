#!/usr/bin/env bash

set -e

role=${CONTAINER_ROLE:-app}
env=${APP_ENV:-production}

echo "Running container role \"$role\" in \"$env\" environment. "

php /app/artisan config:cache

if [ "$role" = "app" ]; then

    if [ "$env" = "production" ]; then
        php /app/artisan migrate --force
    else
        php /app/artisan migrate
    fi

elif [ "$role" = "queue" ]; then

    echo "Starting the queues..."
    mv queue.conf /opt/docker/etc/supervisor.d/
    supervisorctl start queue-default queue-admin
    supervisorctl status all

elif [ "$role" = "schedule" ]; then

    while [ true ]; do
        php /app/artisan schedule:run --verbose --no-interaction &
        sleep 60
    done

else

    echo "Could not match the container role \"$role\""
    exit 1

fi
