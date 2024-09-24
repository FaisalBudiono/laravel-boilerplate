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
    mv supervisord.conf/prod.conf /opt/docker/etc/supervisor.d/
    cat /opt/docker/etc/supervisor.d/prod.conf

    # supervisorctl -c /etc/supervisord.conf help
    # sleep 5s
    # supervisorctl -c /etc/supervisord.conf reread
    # supervisorctl -c /etc/supervisord.conf add queue-default
    # supervisorctl -c /etc/supervisord.conf start queue-default
    # supervisorctl -c /etc/supervisord.conf status all
    # supervisorctl status all

    # supervisorctl -c /etc/supervisord.conf status
    # supervisorctl -c /etc/supervisord.conf start queue-default

elif [ "$role" = "schedule" ]; then

    while [ true ]; do
        php /app/artisan schedule:run --verbose --no-interaction &
        sleep 60
    done

else

    echo "Could not match the container role \"$role\""
    exit 1

fi
