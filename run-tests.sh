#!/usr/bin/env bash

./vendor/bin/codecept run && \
./vendor/bin/codecept -c codeception.https.yml run
